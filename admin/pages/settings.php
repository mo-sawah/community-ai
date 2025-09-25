<?php
// Get settings instance
$settings_manager = Community_AI()->get_component('settings');
?>
<div class="wrap community-ai-admin-page">
    <h1><?php esc_html_e('Community AI Settings', 'community-ai'); ?></h1>

    <form id="community-ai-settings-form">
        <?php wp_nonce_field('community_ai_admin_nonce', 'nonce'); ?>

        <div id="tabs">
            <ul>
                <li><a href="#tab-general"><?php esc_html_e('General', 'community-ai'); ?></a></li>
                <li><a href="#tab-ai"><?php esc_html_e('AI Generation', 'community-ai'); ?></a></li>
            </ul>

            <div id="tab-general">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="posts_per_page"><?php esc_html_e('Posts Per Page', 'community-ai'); ?></label></th>
                        <td>
                            <input type="number" id="posts_per_page" name="posts_per_page"
                                   value="<?php echo esc_attr($settings_manager->get('posts_per_page')); ?>" class="small-text">
                            <p class="description"><?php esc_html_e('Number of posts to show on community pages.', 'community-ai'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="primary_color"><?php esc_html_e('Primary Color', 'community-ai'); ?></label></th>
                        <td>
                            <input type="text" id="primary_color" name="primary_color"
                                   value="<?php echo esc_attr($settings_manager->get('primary_color')); ?>" class="color-picker">
                            <p class="description"><?php esc_html_e('The main theme color for the community frontend.', 'community-ai'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="tab-ai">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ai_generation_enabled"><?php esc_html_e('Enable AI Generation', 'community-ai'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="ai_generation_enabled" name="ai_generation_enabled" value="1"
                                    <?php checked($settings_manager->get('ai_generation_enabled')); ?>>
                                <?php esc_html_e('Enable automatic content generation.', 'community-ai'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="openrouter_api_key"><?php esc_html_e('OpenRouter API Key', 'community-ai'); ?></label></th>
                        <td>
                            <input type="password" id="openrouter_api_key" name="openrouter_api_key"
                                   value="<?php echo esc_attr($settings_manager->get('openrouter_api_key')); ?>" class="regular-text">
                            <p class="description"><?php printf(
                                esc_html__('Enter your API key from %s. Required for AI features.', 'community-ai'),
                                '<a href="https://openrouter.ai/keys" target="_blank">OpenRouter</a>'
                            ); ?></p>
                        </td>
                    </tr>
                     <tr>
                        <th scope="row"><label for="source_websites"><?php esc_html_e('Source Websites (RSS Feeds)', 'community-ai'); ?></label></th>
                        <td>
                            <textarea id="source_websites" name="source_websites" rows="5" class="large-text code"><?php echo esc_textarea($settings_manager->get('source_websites')); ?></textarea>
                            <p class="description"><?php esc_html_e('Enter one RSS feed URL per line. The AI will use these as inspiration.', 'community-ai'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        <?php submit_button(__('Save Settings', 'community-ai')); ?>
    </form>
    <div id="settings-save-feedback"></div>
</div>