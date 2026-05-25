<?php
/**
 * =========================================================================
 * _____                                     _   _           _         
 * / ____|                                   (_) | |         | |        
 * | (___   __ _ _ __ ___  _   _ _ __  __ _ _  | |  _ __  _| |__  ___ 
 * \___ \ / _` | '_ ` _ \| | | | '__/ _` | | | | / _` | '_ \/ __|
 * ____) | (_| | | | | | | |_| | | | (_| | | | |___| (_| | |_) \__ \
 * |_____/ \__,_|_| |_| |_|\__,_|_|  \__,_|_| |_|______\__,_|_.__/|___/
 * * tpsamurai.com/labs
 * =========================================================================
 * Module Identity Matrix: Module 91913
 * Namespace:              dpism (Isolation Members)
 * Functional Role:        Zero-Query Membership Gate & Access Isolation
 * =========================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Xami_Isolation_Members {

    private static $instance = null;
    private $gate_prefix = 'dpism_';

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Intercept user authentication states before content rendering pipelines execute
        add_action('template_redirect', array($this, 'enforce_node_access_isolation'));
    }

    /**
     * Absolute Boundary Gatekeeper.
     * Evaluates whether the active logged-in user is cleared to view this specific node footprint.
     */
    public function enforce_node_access_isolation() {
        // PUSHBACK/GUARD: Always let Super Admins pass completely unhindered
        if (is_super_admin()) {
            return;
        }

        global $wpdb;
        $current_node_id = intval($wpdb->blogid);
        $current_user_id = get_current_user_id();

        // 1. Public Access Fallback
        // If a user isn't logged in, standard WordPress access control handles public states
        if (0 === $current_user_id) {
            return;
        }

        // 2. High-Velocity Memory Flat-File Intercept
        // Pull the pre-compiled authorization envelope via the MNS core engine
        if (!class_exists('Mxm_Xami_Node_Orchestrator')) {
            return;
        }

        $orchestrator = Mxm_Xami_Node_Orchestrator::instance();
        
        // Fetch the user's specific access clearance map file
        // Naming syntax follows our rules: e.g., matrix_map_ism_user_{ID}.json
        $clearance_asset_key = 'ism_user_' . $current_user_id;
        $access_envelope = $orchestrator->read_compiled_matrix_asset($clearance_asset_key);

        // PUSHBACK: If no flat-file envelope exists for this user, they have no cross-network clearance
        if (empty($access_envelope) || !is_array($access_envelope)) {
            $this->trigger_isolation_bounce();
        }

        // 3. Coordinate Match Validation
        // Verify if the active blog ID exists inside the user's allowed envelope array
        $allowed_nodes = isset($access_envelope['allowed_nodes']) ? $access_envelope['allowed_nodes'] : array();

        if (!in_array($current_node_id, $allowed_nodes, true)) {
            // The user is authenticated on the network but locked out of this specific site node
            $this->trigger_isolation_bounce();
        }
    }

    /**
     * Executes an immediate, defensive redirection bounce to the user's primary portal hub
     */
    private function trigger_isolation_bounce() {
        // Send a clean, standardized unauthorized code before shifting locations
        status_header(401);
        
        // Redirect them directly to their assigned home node dashboard or network root
        $fallback_url = get_site_url(get_current_site()->blog_id) . '/wp-admin/';
        
        wp_redirect($fallback_url);
        exit;
    }
}

// Ignition
Mxm_Xami_Isolation_Members::instance();