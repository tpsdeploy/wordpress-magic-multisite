<?php
/**
 * Module Name: MXM Single Sign-On (SSO) Handler
 * Description: Intercepts secure network requests and logs users in via global tokens.
 * Version:     1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Sso_Handler {

    private static $instance = null;

    /**
     * Connects the SSO handler to the active execution loop.
     */
    public static function engage() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Listen for early initialization to intercept the authentication handshake
        add_action('init', array($this, 'authenticate_via_token'));
    }

    /**
     * Intercepts the inbound request stream to execute a zero-friction login.
     */
    public function authenticate_via_token() {
        // Only run if an explicit global token login request is present
        if (empty($_GET['mxm_sso_token'])) {
            return;
        }

        $global_token = sanitize_text_field($_GET['mxm_sso_token']);
        
        // Locate the matching user identity using our optimized materialized view table
        $user_id = $this->lookup_user_by_token($global_token);

        if (!$user_id) {
            wp_die('Invalid or expired secure SSO handshake.', 'SSO Failure', array('response' => 403));
        }

        // Authenticate the user cleanly into the environment thread
        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        // Instantly scrub the token parameter from the URL string and redirect safely to dashboard
        $redirect_url = remove_query_arg('mxm_sso_token');
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Scans the materialized flat table index for a lightning-fast match.
     */
    private function lookup_user_by_token($token) {
        global $wpdb;
        $table_name = $wpdb->base_prefix . 'mxm_master_users';

        // Direct lookup using our indexed global token column
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT mxm_user_id FROM {$table_name} WHERE mxm_global_token = %s LIMIT 1",
            $token
        ));

        return $user_id ? intval($user_id) : false;
    }
}

// Engage the SSO Bridge
Mxm_Sso_Handler::engage();