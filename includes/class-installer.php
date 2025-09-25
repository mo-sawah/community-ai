<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Installer {
    private AI_Community_Database $database;

    public function __construct(AI_Community_Database $database) {
        $this->database = $database;
    }

    public function activate(): void {
        $this->database->create_tables();
        $this->create_default_communities();
        $this->schedule_events();
        
        if (get_option('community_ai_settings') === false) {
             update_option('community_ai_settings', (new AI_Community_Settings())->get_defaults());
        }
        flush_rewrite_rules();
    }

    public function deactivate(): void {
        wp_clear_scheduled_hook('community_ai_generate_content');
        flush_rewrite_rules();
    }

    private function schedule_events(): void {
        if (!wp_next_scheduled('community_ai_generate_content')) {
            wp_schedule_event(time(), 'hourly', 'community_ai_generate_content');
        }
    }
    
    private function create_default_communities(): void {
        $default_communities = [
            ['name' => 'General Discussion', 'slug' => 'general', 'description' => 'A place for general community chat.', 'color' => '#3b82f6', 'created_by' => 1],
            ['name' => 'AI & Technology', 'slug' => 'ai-tech', 'description' => 'Discuss the latest in AI and tech.', 'color' => '#8b5cf6', 'created_by' => 1]
        ];
        foreach ($default_communities as $community) {
            if (!$this->database->get_community_id_by_slug($community['slug'])) {
                $this->database->create_community($community);
            }
        }
    }
}
