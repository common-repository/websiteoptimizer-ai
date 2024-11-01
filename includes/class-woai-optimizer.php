<?php
class WOAI_Optimizer {
    public function woai_initialize() {
        add_action('wp_enqueue_scripts', array($this, 'woai_enqueue_script'));
        add_filter('script_loader_tag', array($this, 'woai_add_data_site_attribute'), 10, 3);
        add_filter('wp_headers', array($this, 'woai_modify_csp_headers'));
    }

    public function woai_enqueue_script() {
        $options = get_option('woai_settings');
        $site_id = isset($options['site_id']) ? esc_attr($options['site_id']) : '';
        $is_enabled = isset($options['enabled']) && $options['enabled'] == 1;

        if ($is_enabled && !empty($site_id)) {
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is not needed for this script
            wp_enqueue_script('woai-script', 'https://www.websiteoptimizer.dev/o.js', array(), null, false);
            wp_script_add_data('woai-script', 'data-site', $site_id);
        }
    }

    public function woai_add_data_site_attribute($tag, $handle, $src) {
        if ('woai-script' === $handle) {
            $options = get_option('woai_settings');
            $site_id = esc_attr($options['site_id']);
            return str_replace(' src', ' data-site="' . $site_id . '" src', $tag);
        }
        return $tag;
    }

    // Ensure the CSP headers are modified if needed to allow the WebsiteOptimizer.AI script
    public function woai_modify_csp_headers($headers) {
        $options = get_option('woai_settings');
        $is_enabled = isset($options['enabled']) && $options['enabled'] == 1;
        $site_id = isset($options['site_id']) ? esc_attr($options['site_id']) : '';
    
        if (!$is_enabled || empty($site_id)) {
            return $headers;
        }
    
        $csp_headers = array('Content-Security-Policy', 'Content-Security-Policy-Report-Only', 'X-Content-Security-Policy', 'X-WebKit-CSP');
        $woai_directives = array(
            'script-src' => 'https://*.websiteoptimizer.dev',
            'connect-src' => 'https://*.websiteoptimizer.dev'
        );
    
        foreach ($csp_headers as $header) {
            if (isset($headers[$header])) {
                $csp = $headers[$header];
                $modified = false;
                foreach ($woai_directives as $directive => $value) {
                    $new_csp = $this->woai_add_to_csp_directive($csp, $directive, $value);
                    if ($new_csp !== $csp) {
                        $csp = $new_csp;
                        $modified = true;
                    }
                }

                if ($modified) {
                    $headers[$header] = $csp;
                }
            }
        }
    
        return $headers;
    }
    
    private function woai_add_to_csp_directive($csp, $directive, $value) {
        $directives = array_filter(array_map('trim', explode(';', $csp)));
        $directive_found = false;
    
        foreach ($directives as &$existing_directive) {
            if (strpos($existing_directive, $directive) === 0) {
                $parts = preg_split('/\s+/', $existing_directive);
                if (!in_array($value, $parts)) {
                    $existing_directive .= " $value";
                    $directive_found = true;
                }
                break;
            }
        }
    
        return $directive_found ? implode('; ', $directives) : $csp;
    }
}