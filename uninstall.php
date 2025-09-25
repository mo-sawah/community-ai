<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

$options_to_delete = [
    'community_ai_settings',
    'community_ai_db_version',
];

foreach ($options_to_delete as $option) {
    delete_option($option);
}

// For site options in multisite
foreach ($options_to_delete as $option) {
    delete_site_option($option);
}

$tables = [
    $wpdb->prefix . 'community_ai_posts',
    $wpdb->prefix . 'community_ai_comments',
    $wpdb->prefix . 'community_ai_votes',
    $wpdb->prefix . 'community_ai_communities',
    $wpdb->prefix . 'community_ai_user_meta',
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}
