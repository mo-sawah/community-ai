<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Admin {
    private AI_Community_Settings $settings;
    private AI_Community_Database $database;
    private AI_Community_AI_Generator $ai_generator;

    public function __construct(AI_Community_Settings $settings, AI_Community_Database $database, AI_Community_AI_Generator $ai_generator) {
        $this->settings = $settings;
        $this->database = $database;
        $this->ai_generator = $ai_generator;
    }

    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_community_ai_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_community_ai_generate_now', [$this, 'ajax_generate_now']);
    }

    public function add_admin_menu() {
        add_menu_page('Community AI', 'Community AI', 'manage_options', 'community-ai', [$this, 'render_dashboard_page'], 'dashicons-groups', 30);
        add_submenu_page('community-ai', 'Dashboard', 'Dashboard', 'manage_options', 'community-ai', [$this, 'render_dashboard_page']);
        add_submenu_page('community-ai', 'Posts', 'Posts', 'manage_options', 'community-ai-posts', [$this, 'render_posts_page']);
        add_submenu_page('community-ai', 'Communities', 'Communities', 'manage_options', 'community-ai-communities', [$this, 'render_communities_page']);
        add_submenu_page('community-ai', 'Settings', 'Settings', 'manage_options', 'community-ai-settings', [$this, 'render_settings_page']);
    }
    
    public function render_dashboard_page() { require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/views/dashboard-page.php'; }
    public function render_posts_page() { require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/views/posts-page.php'; }
    public function render_communities_page() { require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/views/communities-page.php'; }
    public function render_settings_page() { require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/views/settings-page.php'; }

    public function ajax_save_settings() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'community_ai_admin_nonce')) die('Security check failed.');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Permission denied.'], 403);
        $settings_input = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : [];
        $sanitized_settings = $this->settings->sanitize($settings_input);
        update_option(AI_Community_Settings::OPTION_NAME, $sanitized_settings);
        wp_clear_scheduled_hook('community_ai_generate_content');
        wp_schedule_event(time(), $sanitized_settings['ai_generation_schedule'], 'community_ai_generate_content');
        wp_send_json_success(['message' => 'Settings saved successfully.']);
    }

    public function ajax_generate_now() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'community_ai_admin_nonce')) die('Security check failed.');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Permission denied.'], 403);
        try {
            $result = $this->ai_generator->generate_content_batch();
            wp_send_json_success(['message' => "Successfully generated {$result['posts_created']} posts."]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
