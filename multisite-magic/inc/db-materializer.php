<?php
/**
 * Module Name: MXM Database Materializer
 * Description: High-performance flat table indexer driven by Action Scheduler batch processing.
 * Version:     1.1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Db_Materializer {

    private static $instance = null;
    private $table_name;
    private $hook_name = 'mxm_materialize_user_batch';

    /**
     * Instantiates the database materializer layer.
     */
    public static function synchronize() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->base_prefix . 'mxm_master_users';

        // Bind our materialization processor directly to Action Scheduler's execution pool
        add_action($this->hook_name, array($this, 'execute_batch_materialization'), 10, 1);
    }

    /**
     * Carves out the high-velocity, single-row indexed matrix inside the database.
     */
    public function materialize_schema() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            mxm_user_id BIGINT(20) UNSIGNED NOT NULL,
            mxm_global_token VARCHAR(255) NOT NULL,
            mxm_network_clearance VARCHAR(50) DEFAULT 'standard' NOT NULL,
            last_sync_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (mxm_user_id),
            KEY mxm_global_token_idx (mxm_global_token(20))
        ) $charset_collate;";

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta($sql);
    }

    /**
     * Schedules an asynchronous, isolated user update task in the background queue.
     * Bypasses the active page load immediately to keep user experience blistering fast.
     */
    public function queue_user_sync($user_id, $global_token, $clearance = 'standard') {
        // Ensure Action Scheduler is available before pushing to the queue
        if (!function_exists('as_enqueue_async_action')) {
            // Fallback to direct execution if Action Scheduler isn't active on the host ecosystem
            $this->execute_row_replace($user_id, $global_token, $clearance);
            return;
        }

        // Arguments passed safely as an isolated data package
        $args = array(
            'user_id'      => intval($user_id),
            'global_token' => sanitize_text_field($global_token),
            'clearance'    => sanitize_key($clearance)
        );

        // Hand the job off to the background runner instantly
        as_enqueue_async_action($this->hook_name, array($args), 'mxm-data-sync');
    }

    /**
     * The worker function executed by Action Scheduler's background runner thread.
     */
    public function execute_batch_materialization($args) {
        if (empty($args['user_id']) || empty($args['global_token'])) {
            return;
        }

        // Run the resource-intensive database write completely out of the user's view
        $this->execute_row_replace($args['user_id'], $args['global_token'], $args['clearance']);
    }

    /**
     * Internal atomic database execution.
     */
    private function execute_row_replace($user_id, $global_token, $clearance) {
        global $wpdb;

        $wpdb->replace(
            $this->table_name,
            array(
                'mxm_user_id'          => $user_id,
                'mxm_global_token'     => $global_token,
                'mxm_network_clearance' => $clearance,
                'last_sync_timestamp'   => current_time('mysql', 1)
            ),
            array('%d', '%s', '%s', '%s')
        );
    }
}

// Instantiate the database engine
Mxm_Db_Materializer::synchronize();