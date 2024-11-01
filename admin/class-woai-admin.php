<?php
class WOAI_Admin {

    public function woai_init() {
        add_action('admin_menu', array($this, 'woai_add_plugin_page'));
        add_action('admin_init', array($this, 'woai_page_init'));
        add_action('admin_enqueue_scripts', array($this, 'woai_enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'woai_show_setup_notice'));
    }


    public function woai_show_setup_notice() {
        // Get current screen
        $screen = get_current_screen();
        
        // Only show notice on plugins page
        if ($screen->id !== 'plugins') {
            return;
        }

        $options = get_option('woai_settings');
        $site_id = isset($options['site_id']) ? $options['site_id'] : '';
        $is_enabled = isset($options['enabled']) && $options['enabled'] == 1;

        if (empty($site_id) || !$is_enabled) {
            $setup_url = admin_url('options-general.php?page=website-optimizer-ai');
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><a href="' . esc_url($setup_url) . '" class="button button-primary">Activate WebsiteOptimizer.AI</a>&nbsp;&nbsp;to start optimizing your site</p>';
            echo '</div>';
        }
    }

    public function woai_enqueue_admin_scripts($hook) {
        if ('settings_page_website-optimizer-ai' !== $hook) {
            return;
        }

        wp_enqueue_style('woai-admin-css', WOAI_PLUGIN_URL . 'assets/css/website-optimizer-ai-admin.css', array(), WOAI_VERSION);
        wp_enqueue_script('woai-admin-js', WOAI_PLUGIN_URL . 'assets/js/website-optimizer-ai-admin.js', array('jquery'), WOAI_VERSION, true);
    }

    public function woai_add_plugin_page() {
        add_options_page(
            'WebsiteOptimizer.AI Settings',
            'WebsiteOptimizer.AI',
            'manage_options',
            'website-optimizer-ai',
            array($this, 'woai_create_admin_page')
        );
    }

    public function woai_create_admin_page() {
        $this->options = get_option('woai_settings');
        ?>
        <div class="wrap woai-admin-wrap">

            <h1>
                <div class="woai-admin-header">
                    <img width=64 height=64 src="<?php echo esc_url(WOAI_PLUGIN_URL . 'assets/images/icon-256x256.png'); ?>" style="margin-right: 24px" /> 
                    <div style="width: 100%">
                            <div style="flex: 1">WebsiteOptimizer.AI</div>
                            <div style="font-size: 80%; color: #555; margin-top: 0px; padding-top: 0px;">
                                Optimize your site's content with AI
                            </div>
                    </div>
                </div>
            </h1>

            <div class="woai-admin-instructions">
                <h3>Instructions</h3>
                <p>To set up WebsiteOptimizer.AI:</p>
                <ol>
                    <li>If you haven't already, <a href="https://websiteoptimizer.ai/signup" target="_blank">create a WebsiteOptimizer.AI account</a>.</li>
                    <li>In your WebsiteOptimizer.AI dashboard, copy the implementation code snippet. It will look something like this:

                        <pre>&lt;script src="https://www.websiteoptimizer.dev/o.js" data-site="YOUR_UNIQUE_SITE_ID">&lt;/script></pre>
                    </li>
                    <li>Paste the Site ID below and click "Save Changes".</li>
                    <li>Configure your optimizations in your <a href="http://websiteoptimizer.AI">WebsiteOptimizer.AI dashboard</a>. If you run into any issues, email us at <a href="mailto:support@websiteoptimizer.ai">support@websiteoptimizer.ai</a></li>
                </ol>
            </div>
            <h3>Optimization Settings</h3>
            <p>Optimizations are configured at the WebsiteOptimizer.AI site.</p>
            <div class="woai-dashboard-button" >
                            <a href="https://websiteoptimizer.ai" target="_blank" class="button button-primary">Configure Optimizations</a>
                        </div>

            <form method="post" action="options.php" id="woai-settings-form">
            <?php
                settings_fields('woai_option_group');
                do_settings_sections('website-optimizer-ai-admin');
                submit_button('Save Changes', 'woai-submit-button');
            ?>
            </form>
        </div>
        <?php
    }

    public function woai_page_init() {
        register_setting(
            'woai_option_group',
            'woai_settings',
            array($this, 'woai_sanitize')
        );
    
        add_settings_section(
            'woai_setting_section',
            '<br/>WordPress Settings',
            array($this, 'woai_section_info'),
            'website-optimizer-ai-admin'
        );
    
        add_settings_field(
            'enabled',
            'Enable WebsiteOptimizer.AI',
            array($this, 'woai_enabled_callback'),
            'website-optimizer-ai-admin',
            'woai_setting_section'
        );
    
        add_settings_field(
            'site_id',
            'WebsiteOptimizer.AI Site ID',
            array($this, 'woai_site_id_callback'),
            'website-optimizer-ai-admin',
            'woai_setting_section'
        );
    }

    public function woai_site_id_callback() {
        $site_id = isset($this->options['site_id']) ? esc_attr($this->options['site_id']) : '';
        $enabled = isset($this->options['enabled']) ? $this->options['enabled'] : 1;
        $dashboard_url = 'https://websiteoptimizer.ai/';
        
        echo '<div class="woai-form-group woai-site-id-group">';
        printf(
            '<input type="text" id="site_id" name="woai_settings[site_id]" value="%s" %s />',
            esc_attr($site_id),
            $enabled ? '' : 'disabled'
        );
        echo '<a href="' . esc_url($dashboard_url) . '#siteid" target="_blank" style="text-decoration: none; margin-left: 4px;">(Get Your WebsiteOptimizer.AI Site ID)</a>';
        echo '</div>';
    }


    public function woai_sanitize($input) {
        $sanitary_values = array();
        $sanitary_values['enabled'] = isset($input['enabled']) ? 1 : 0;
        
        if ($sanitary_values['enabled']) {
            if (isset($input['site_id'])) {
                $sanitary_values['site_id'] = sanitize_text_field($input['site_id']);
            }
        } else {
            // If not enabled, keep the existing site_id
            $existing_options = get_option('woai_settings');
            $sanitary_values['site_id'] = isset($existing_options['site_id']) ? $existing_options['site_id'] : '';
        }
        
        return $sanitary_values;
    }
    
    public function woai_enabled_callback() {
        $checked = isset($this->options['enabled']) ? $this->options['enabled'] : 1; // Default to enabled
        printf(
            '<div class="woai-form-group"><input type="checkbox" id="enabled" name="woai_settings[enabled]" value="1" %s /></div>',
            checked(1, $checked, false)
        );

    }

    public function woai_section_info() {
        echo 'Enable WebsiteOptimizer.AI on your WordPress site and enter your WebsiteOptimizer.AI Site ID below.';
    }

}