<?php
if (!defined('ABSPATH')) exit;

class AI_Community_AI_Generator {

    private AI_Community_Settings $settings;
    private AI_Community_Database $database;
    private AI_Community_OpenRouter_API $openrouter;

    public function __construct(
        AI_Community_Settings $settings,
        AI_Community_Database $database,
        AI_Community_OpenRouter_API $openrouter
    ) {
        $this->settings = $settings;
        $this->database = $database;
        $this->openrouter = $openrouter;
    }
    
    public function init() {
        add_action('community_ai_generate_content', [$this, 'run_scheduled_generation']);
    }
    
    public function run_scheduled_generation() {
        if (!$this->settings->get('ai_generation_enabled')) {
            return;
        }

        try {
            $this->generate_content_batch();
        } catch (Exception $e) {
            error_log('Community AI Generation Error: ' . $e->getMessage());
        }
    }

    public function generate_content_batch() {
        $posts_per_day = $this->settings->get('posts_per_day');
        $posts_today = $this->get_ai_posts_count_today();
        $posts_to_create = max(0, $posts_per_day - $posts_today);
        
        // Let's create a fraction of the daily goal per run
        $posts_to_create = min($posts_to_create, ceil($posts_per_day / 4));
        if ($posts_to_create <= 0) {
            return ['posts_created' => 0];
        }

        $source_content = $this->fetch_source_content();
        if (empty($source_content)) {
            throw new Exception('No source content found to generate from.');
        }

        $generated_posts = $this->openrouter->generate_posts_from_content($source_content, $posts_to_create);
        if (is_wp_error($generated_posts)) {
            throw new Exception($generated_posts->get_error_message());
        }

        $posts_created_count = 0;
        foreach ($generated_posts as $post_data) {
            if ($this->create_ai_post($post_data)) {
                $posts_created_count++;
            }
        }
        
        return ['posts_created' => $posts_created_count];
    }
    
    private function fetch_source_content(): array {
        $urls = explode("\n", $this->settings->get('source_websites', ''));
        $content = [];
        
        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) continue;
            
            $feed_url = rtrim($url, '/') . '/feed/';
            $feed = fetch_feed($feed_url);

            if (!is_wp_error($feed)) {
                $items = $feed->get_items(0, 5); // Get 5 items per feed
                foreach ($items as $item) {
                    $content[] = [
                        'title' => $item->get_title(),
                        'content' => wp_strip_all_tags($item->get_content()),
                        'url' => $item->get_link(),
                    ];
                }
            }
        }
        return $content;
    }
    
    private function create_ai_post(array $post_data): bool {
        $ai_user_id = $this->get_or_create_ai_user();

        $post_id = $this->database->create_post([
            'title' => $post_data['title'],
            'content' => $post_data['content'],
            'author_id' => $ai_user_id,
            'community_slug' => $post_data['community'],
            'tags' => is_array($post_data['tags']) ? implode(', ', $post_data['tags']) : '',
            'is_ai_generated' => 1,
            'ai_model' => $this->settings->get('ai_model'),
        ]);

        return (bool) $post_id;
    }
    
    private function get_or_create_ai_user(): int {
        $username = 'community_ai_bot';
        $user = get_user_by('login', $username);
        if ($user) {
            return $user->ID;
        }

        $user_id = wp_create_user($username, wp_generate_password(), 'bot@' . parse_url(home_url(), PHP_URL_HOST));
        if (is_wp_error($user_id)) {
            return 1; // Fallback to admin
        }
        
        wp_update_user([
            'ID' => $user_id,
            'display_name' => 'Community AI Bot',
            'role' => 'subscriber'
        ]);
        return $user_id;
    }
    
    private function get_ai_posts_count_today(): int {
        global $wpdb;
        $tables = $this->database->get_table_names();
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$tables['posts']} WHERE is_ai_generated = 1 AND DATE(created_at) = CURDATE()"
        );
    }
}