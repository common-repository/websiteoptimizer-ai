<?php
/**
 * Plugin Name: WebsiteOptimizer.AI
 * Plugin URI: https://www.websiteoptimizer.ai
 * Description: Easily integrate WebsiteOptimizer.AI with your WordPress site to A/B test AI generated site content.
 * Version: 1.0.0
 * Author: WebsiteOptimizer.AI
 * Author URI: https://WebsiteOptimizer.AI
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('WOAI_VERSION', '1.0.0');
define('WOAI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOAI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WOAI_PLUGIN_DIR . 'includes/class-woai-optimizer.php';
require_once WOAI_PLUGIN_DIR . 'admin/class-woai-admin.php';

function woai_run_website_optimizer() {
    $plugin = new WOAI_Optimizer();
    $plugin->woai_initialize();

    $plugin_admin = new WOAI_Admin();
    $plugin_admin->woai_init();
}

add_action('plugins_loaded', 'woai_run_website_optimizer');

register_activation_hook(__FILE__, 'woai_activate');

function woai_activate() {
    add_option('woai_do_activation_redirect', true);
}

function woai_redirect() {
    if (get_option('woai_do_activation_redirect', false)) {
        delete_option('woai_do_activation_redirect');
        wp_safe_redirect(admin_url('options-general.php?page=website-optimizer-ai'));
        exit;
    }
}
add_action('admin_init', 'woai_redirect');

// Add settings link on plugin page
function woai_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=website-optimizer-ai') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'woai_settings_link');