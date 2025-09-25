<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Assets {
    public function init() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_frontend_assets() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'community_ai')) {
            wp_enqueue_style('community-ai-frontend', COMMUNITY_AI_PLUGIN_URL . 'assets/css/frontend.css', [], COMMUNITY_AI_VERSION);
            wp_enqueue_script('community-ai-frontend', COMMUNITY_AI_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], COMMUNITY_AI_VERSION, true);
        }
    }

    public function enqueue_admin_assets(string $hook) {
        if (strpos($hook, 'community-ai') === false) return;
        wp_enqueue_style('community-ai-admin', COMMUNITY_AI_PLUGIN_URL . 'assets/css/admin.css', [], COMMUNITY_AI_VERSION);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('community-ai-admin', COMMUNITY_AI_PLUGIN_URL . 'assets/js/admin.js', ['jquery', 'wp-color-picker', 'jquery-ui-tabs'], COMMUNITY_AI_VERSION, true);
        wp_localize_script('community-ai-admin', 'communityAiAdmin', ['nonce' => wp_create_nonce('community_ai_admin_nonce'), 'ajax_url' => admin_url('admin-ajax.php')]);
    }
}
