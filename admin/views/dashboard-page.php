<?php
$db = Community_AI()->get_component('database');
$stats = $db->get_dashboard_stats();
?>
<div class="wrap community-ai-admin-page">
    <div class="cai-header">
        <h1><?php esc_html_e('Community AI Dashboard', 'community-ai'); ?></h1>
        <p><?php esc_html_e('Welcome to your AI-powered community. Here is a summary of the activity.', 'community-ai'); ?></p>
    </div>

    <div class="cai-dashboard-widgets">
        <div class="cai-widget">
            <div class="widget-header"><span class="dashicons dashicons-admin-post"></span><h3><?php esc_html_e('Content Overview', 'community-ai'); ?></h3></div>
            <div class="widget-content">
                <p><strong>Total Posts:</strong> <?php echo esc_html(number_format($stats['total_posts'])); ?></p>
                <p><strong>Total Comments:</strong> <?php echo esc_html(number_format($stats['total_comments'])); ?></p>
                <p><strong>AI-Generated Posts:</strong> <?php echo esc_html(number_format($stats['ai_posts'])); ?></p>
            </div>
        </div>
        
        <div class="cai-widget">
            <div class="widget-header"><span class="dashicons dashicons-groups"></span><h3><?php esc_html_e('User Activity', 'community-ai'); ?></h3></div>
            <div class="widget-content"><p>User metrics coming soon!</p></div>
        </div>

        <div class="cai-widget">
            <div class="widget-header"><span class="dashicons dashicons-robot"></span><h3><?php esc_html_e('AI Generation', 'community-ai'); ?></h3></div>
            <div class="widget-content">
                 <?php $next_run = wp_next_scheduled('community_ai_generate_content');
                       echo '<p><strong>Next Run:</strong> ' . ($next_run ? esc_html(human_time_diff($next_run)) . ' from now' : 'Not scheduled.') . '</p>'; ?>
                <button id="cai-generate-now" class="button button-primary"><?php esc_html_e('Generate Content Now', 'community-ai'); ?></button>
                <p id="cai-generate-feedback" class="feedback"></p>
            </div>
        </div>
    </div>
</div>
