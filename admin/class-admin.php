<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Admin {
    private AI_Community_Settings $settings;
    private AI_Community_Database $database;
    private AI_Community_AI_Generator $ai_generator;

    public function __construct(
        AI_Community_Settings $settings,
        AI_Community_Database $database,
        AI_Community_AI_Generator $ai_generator
    ) {
        $this->settings = $settings;
        $this->database = $database;
        $this->ai_generator = $ai_generator;
    }

    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_community_ai_save_settings', [$this, 'ajax_save_settings']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Community AI', 'community-ai'),
            'Community AI',
            'manage_options',
            'community-ai',
            [$this, 'render_dashboard_page'],
            'dashicons-groups',
            30
        );

        add_submenu_page(
            'community-ai',
            __('Dashboard', 'community-ai'),
            __('Dashboard', 'community-ai'),
            'manage_options',
            'community-ai',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'community-ai',
            __('Settings', 'community-ai'),
            __('Settings', 'community-ai'),
            'manage_options',
            'community-ai-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function render_dashboard_page() {
        require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/pages/dashboard.php';
    }

    public function render_settings_page() {
        require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/pages/settings.php';
    }

    public function ajax_save_settings() {
        check_ajax_referer('community_ai_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.'], 403);
        }

        $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : [];
        $sanitized_settings = $this->settings->sanitize($settings);
        
        update_option(AI_Community_Settings::OPTION_NAME, $sanitized_settings);

        wp_send_json_success(['message' => 'Settings saved successfully.']);
    }
}