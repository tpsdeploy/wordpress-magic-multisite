<?php
/**
 * Multisite Magic - Clean Teardown Engine
 * Triggers strictly when the plugin is explicitly uninstalled/deleted from the network dashboard.
 * Completely purges the MXM tablespace to leave zero database footprint.
 */

// If uninstall not called from WordPress, exit immediately to prevent malicious direct execution
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

class Mxm_Clean_Teardown {

    /**
     * Executes a pristine database purge across the multisite network installation.
     */
    public static function purge_ecosystem() {
        global $wpdb;

        // Target our specific, isolated master flat table
        $table_name = $wpdb->base_prefix . 'mxm_master_users';

        // Drop the custom MXM tablespace entirely
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

        // Clear any high-velocity RAM transients cached by the Multi-Claw
        // This ensures no volatile memory leaks remain in the object cache
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_mxm_claw_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_mxm_claw_%'");
    }
}

// Execute the clinical purge
Mxm_Clean_Teardown::purge_ecosystem();