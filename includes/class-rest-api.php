<?php
if (!defined('ABSPATH')) exit;

class AI_Community_REST_API {
    const NAMESPACE = 'community-ai/v1';
    private AI_Community_Database $database;
    private AI_Community_Settings $settings;

    public function __construct(AI_Community_Database $database, AI_Community_Settings $settings) {
        $this->database = $database;
        $this->settings = $settings;
    }

    public function init() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route(self::NAMESPACE, '/posts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_posts'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => ['sanitize_callback' => 'absint'],
                'per_page' => ['sanitize_callback' => 'absint'],
                'sort' => ['sanitize_callback' => 'sanitize_text_field'],
                'community' => ['sanitize_callback' => 'sanitize_text_field'],
                'search' => ['sanitize_callback' => 'sanitize_text_field'],
            ]
        ]);
    }

    public function get_posts(WP_REST_Request $request): WP_REST_Response {
        $args = [
            'page' => $request->get_param('page') ?: 1,
            'per_page' => $request->get_param('per_page') ?: $this->settings->get('posts_per_page'),
            'sort' => $request->get_param('sort') ?: 'hot',
            'community' => $request->get_param('community') ?: '',
            'search' => $request->get_param('search') ?: '',
        ];
        $posts = $this->database->get_posts($args);
        $total_posts = $this->database->get_posts_count($args);
        $response = new WP_REST_Response($posts);
        $response->header('X-WP-Total', $total_posts);
        $response->header('X-WP-TotalPages', ceil($total_posts / $args['per_page']));
        return $response;
    }
}
