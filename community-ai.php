<?php
/**
 * Plugin Name:       Community AI
 * Description:       An advanced AI-powered community platform with intelligent content generation, moderation, and engagement features.
 * Version:           1.0.1
 * Author:            Mohamed Sawah
 * Author URI:        https://sawahsolutions.com
 * License:           GPL v2 or later
 * Text Domain:       community-ai
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define constants
define('COMMUNITY_AI_VERSION', '1.0.1');
define('COMMUNITY_AI_PLUGIN_FILE', __FILE__);
define('COMMUNITY_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COMMUNITY_AI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COMMUNITY_AI_PLUGIN_BASENAME', plugin_basename(__FILE__));

final class Community_AI_Plugin {

    private static ?Community_AI_Plugin $instance = null;
    public ?AI_Community_Container $container = null;

    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if (!function_exists('add_action')) {
            return;
        }
        $this->load_dependencies();
        $this->init_container();
        $this->init_hooks();
    }

    private function load_dependencies(): void {
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/traits/trait-singleton.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-container.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-installer.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-database.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-settings.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-openrouter-api.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-ai-generator.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-assets.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once COMMUNITY_AI_PLUGIN_DIR . 'admin/class-admin.php';
    }

    private function init_container(): void {
        $this->container = new AI_Community_Container();
        
        $this->container->register('database', AI_Community_Database::class);
        $this->container->register('settings', AI_Community_Settings::class);
        $this->container->register('openrouter', AI_Community_OpenRouter_API::class, ['settings']);
        $this->container->register('ai_generator', AI_Community_AI_Generator::class, ['settings', 'database', 'openrouter']);
        $this->container->register('rest_api', AI_Community_REST_API::class, ['database', 'settings']);
        $this->container->register('assets', AI_Community_Assets::class);
        $this->container->register('frontend', AI_Community_Frontend::class);
        $this->container->register('admin', AI_Community_Admin::class, ['settings', 'database', 'ai_generator']);
        $this->container->register('installer', AI_Community_Installer::class, ['database']);
    }

    private function init_hooks(): void {
        // Activation & Deactivation
        $installer = $this->container->get('installer');
        register_activation_hook(COMMUNITY_AI_PLUGIN_FILE, [$installer, 'activate']);
        register_deactivation_hook(COMMUNITY_AI_PLUGIN_FILE, [$installer, 'deactivate']);

        // Load text domain for translations
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Initialize components
        $this->container->get('settings')->init();
        $this->container->get('rest_api')->init();
        $this->container->get('assets')->init();
        $this->container->get('frontend')->init();
        $this->container->get('ai_generator')->init();
        
        if (is_admin()) {
            $this->container->get('admin')->init();
        }
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'community-ai',
            false,
            dirname(plugin_basename(COMMUNITY_AI_PLUGIN_FILE)) . '/languages'
        );
    }
    
    public function get_component(string $name) {
        return $this->container->get($name);
    }
    
    private function __clone() {}
    public function __wakeup() {}
}

function Community_AI(): Community_AI_Plugin {
    return Community_AI_Plugin::get_instance();
}

// Initialize the plugin
Community_AI();