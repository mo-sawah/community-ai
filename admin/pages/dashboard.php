<div class="wrap community-ai-admin-page">
    <h1><?php esc_html_e('Community AI Dashboard', 'community-ai'); ?></h1>
    <p><?php esc_html_e('Welcome to your AI-powered community. Here is a summary of the activity.', 'community-ai'); ?></p>

    <div class="community-ai-dashboard-widgets">
        <div class="widget">
            <div class="widget-header">
                <span class="dashicons dashicons-admin-post"></span>
                <h3><?php esc_html_e('Content Overview', 'community-ai'); ?></h3>
            </div>
            <div class="widget-content">
                <p><strong>Total Posts:</strong> 1,234</p>
                <p><strong>Total Comments:</strong> 5,678</p>
                <p><strong>AI-Generated Posts:</strong> 234</p>
            </div>
        </div>
        
        <div class="widget">
            <div class="widget-header">
                <span class="dashicons dashicons-groups"></span>
                <h3><?php esc_html_e('User Activity', 'community-ai'); ?></h3>
            </div>
            <div class="widget-content">
                <p><strong>Active Users (24h):</strong> 150</p>
                <p><strong>New Registrations (7d):</strong> 45</p>
                <p><strong>Top Contributor:</strong> Admin</p>
            </div>
        </div>

        <div class="widget">
            <div class="widget-header">
                <span class="dashicons dashicons-robot"></span>
                <h3><?php esc_html_e('AI Generation', 'community-ai'); ?></h3>
            </div>
            <div class="widget-content">
                 <?php
                    $next_run = wp_next_scheduled('community_ai_generate_content');
                    if ($next_run) {
                        echo '<p><strong>Next Run:</strong> ' . esc_html(human_time_diff($next_run)) . ' from now</p>';
                    } else {
                        echo '<p><strong>Next Run:</strong> Not scheduled.</p>';
                    }
                ?>
                <button class="button button-primary">Generate Content Now</button>
            </div>
        </div>
    </div>
</div>