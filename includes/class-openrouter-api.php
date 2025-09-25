<?php
if (!defined('ABSPATH')) exit;

class AI_Community_OpenRouter_API {
    const API_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';
    private AI_Community_Settings $settings;

    public function __construct(AI_Community_Settings $settings) {
        $this->settings = $settings;
    }

    public function generate_posts_from_content(array $source_content, int $count): array|WP_Error {
        $api_key = $this->settings->get('openrouter_api_key');
        if (empty($api_key)) return new WP_Error('no_api_key', 'OpenRouter API key is missing.');

        $prompt = "Based on the following article titles, generate {$count} engaging community discussion posts. For each post, provide a title, content (100-200 words), a relevant community slug (like 'general' or 'ai-tech'), and an array of 3 relevant tags.\n\nSource Titles:\n";
        foreach (array_slice($source_content, 0, 10) as $content) { $prompt .= "- " . $content['title'] . "\n"; }
        $prompt .= "\nFormat the output as a valid JSON object like this: {\"posts\": [{\"title\": \"...\", \"content\": \"...\", \"community\": \"...\", \"tags\": [\"tag1\"]}]}";

        $response = $this->make_request($prompt);
        if (is_wp_error($response)) return $response;

        return $this->parse_generated_posts($response);
    }

    private function make_request(string $prompt, int $max_tokens = 2000) {
        $api_key = $this->settings->get('openrouter_api_key');
        $model = $this->settings->get('ai_model');
        $response = wp_remote_post(self::API_ENDPOINT, [
            'timeout' => 90, 'headers' => ['Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'],
            'body' => json_encode(['model' => $model, 'messages' => [['role' => 'user', 'content' => $prompt]], 'max_tokens' => $max_tokens])
        ]);
        if (is_wp_error($response)) return $response;
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (wp_remote_retrieve_response_code($response) !== 200) return new WP_Error('api_error', $data['error']['message'] ?? 'Unknown API error');
        return $data['choices'][0]['message']['content'] ?? '';
    }

    private function parse_generated_posts(string $content): array {
        preg_match('/\{.*?\}/s', $content, $matches);
        if (empty($matches[0])) return [];
        $data = json_decode($matches[0], true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($data['posts'])) { return $data['posts']; }
        return [];
    }
}
