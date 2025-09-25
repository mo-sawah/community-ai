<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Settings {
    const OPTION_NAME = 'community_ai_settings';
    private array $defaults;
    private ?array $settings = null;

    public function __construct() {
        $this->defaults = [
            'layout_type' => 'sidebar',
            'primary_color' => '#3b82f6',
            'posts_per_page' => 15,
            'enable_voting' => true,
            'enable_comments' => true,
            'ai_generation_enabled' => false,
            'openrouter_api_key' => '',
            'ai_model' => 'openai/gpt-3.5-turbo',
            'source_websites' => get_site_url(),
            'post_topics' => 'tech, wordpress, ai',
            'posts_per_day' => 5,
            'ai_generation_schedule' => 'hourly',
            'debug_mode' => false,
        ];
    }

    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting(
            'community_ai_options',
            self::OPTION_NAME,
            [
                'sanitize_callback' => [$this, 'sanitize'],
                'default' => $this->defaults
            ]
        );
    }

    public function get($key, $default = null) {
        if ($this->settings === null) {
            $this->settings = get_option(self::OPTION_NAME, $this->defaults);
        }
        return $this->settings[$key] ?? $default ?? $this->defaults[$key] ?? null;
    }

    public function sanitize(array $input): array {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (!isset($this->defaults[$key])) continue;
            
            switch ($key) {
                case 'posts_per_page':
                case 'posts_per_day':
                    $sanitized[$key] = absint($value);
                    break;
                case 'primary_color':
                    $sanitized[$key] = sanitize_hex_color($value);
                    break;
                case 'enable_voting':
                case 'enable_comments':
                case 'ai_generation_enabled':
                case 'debug_mode':
                    $sanitized[$key] = (bool)$value;
                    break;
                case 'source_websites':
                    $sanitized[$key] = implode("\n", array_map('esc_url_raw', explode("\n", $value)));
                    break;
                default:
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
            }
        }
        return $sanitized;
    }
}