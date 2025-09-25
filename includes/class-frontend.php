<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Frontend {
    private AI_Community_Settings $settings;
    public function __construct(AI_Community_Settings $settings) {
        $this->settings = $settings;
    }

    public function init() {
        add_shortcode('community_ai', [$this, 'render_shortcode']);
        add_action('wp_head', [$this, 'add_custom_styles']);
    }

    public function render_shortcode($atts): string {
        $atts = shortcode_atts(['layout' => 'default'], $atts, 'community_ai');
        ob_start();
        ?>
        <div id="community-ai-app" class="community-ai-container" data-layout="<?php echo esc_attr($atts['layout']); ?>">
            <div class="cai-loading-spinner">
                <div></div><div></div><div></div><div></div>
            </div>
            <p>Loading Community...</p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function add_custom_styles() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'community_ai')) {
            $primary_color = esc_attr($this->settings->get('primary_color'));
            echo "<style>:root { --cai-primary-color: {$primary_color}; }</style>";
        }
    }
}
