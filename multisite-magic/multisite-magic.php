<?php
/**
 * Plugin Name: Multisite Magic
 * Plugin URI:  https://ciao2chao.com
 * Description: High-velocity, decoupled infrastructure for enterprise network orchestration.
 * Version:     1.0.0
 * Author:      MXM Core Labs
 * Text Domain: mxm
 * Domain Path: /languages
 * * =========================================================================
 * _____                                     _   _           _         
 * / ____|                                   (_) | |         | |        
 * | (___   __ _ _ __ ___  _   _ _ __  __ _ _  | |  _ __  _| |__  ___ 
 * \___ \ / _` | '_ ` _ \| | | | '__/ _` | | | | / _` | '_ \/ __|
 * ____) | (_| | | | | | | |_| | | | (_| | | | |___| (_| | |_) \__ \
 * |_____/ \__,_|_| |_| |_|\__,_|_|  \__,_|_| |_|______\__,_|_.__/|___/
 * * tpsamurai.com/labs
 * =========================================================================
 */

// Exit if accessed directly to enforce Zero-Trust baseline boundaries
if (!defined('ABSPATH')) {
    exit;
}

// Establish absolute structural path constants for internal file routing
define('MXM_VERSION', '1.0.0');
define('MXM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MXM_PLUGIN_URL', plugin_dir_url(__FILE__));

class Mxm_Wp_Multisite_Magic {

    private static $instance = null;

    /**
     * Initializes the core framework loading matrix.
     */
    public static function launch() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Core Constructor Pipeline
     */
    private function __construct() {
        // 1. HIGH-VELOCITY BOT FILTER: Catch malicious form payloads instantly before loading core DB
        if (!empty($_POST)) {
            if (file_exists(MXM_PLUGIN_DIR . 'inc/bot-gatekeeper.php')) {
                require_once MXM_PLUGIN_DIR . 'inc/bot-gatekeeper.php';
                if (class_exists('Mxm_Xami_Gatekeeper')) {
                    Mxm_Xami_Gatekeeper::evaluate_request_security();
                }
            }
        }

        // 2. ENVIRONMENT VERIFICATION: Delay core components to verify multi-site runtime conditions
        add_action('plugins_loaded', array($this, 'verify_environment_and_load'), 1);

        // 3. REGISTRATION HOOKS: Bind internal activation/deactivation life-cycles
        register_activation_hook(__FILE__, array($this, 'activate_framework'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_framework'));
    }

    /**
     * Framework Activation Gateway (Executes structural database schema setup)
     */
    public function activate_framework() {
        if (!is_multisite()) {
            return;
        }
        
        if (file_exists(MXM_PLUGIN_DIR . 'inc/db-materializer.php')) {
            require_once MXM_PLUGIN_DIR . 'inc/db-materializer.php';
            if (class_exists('Mxm_Db_Materializer')) {
                Mxm_Db_Materializer::synchronize()->materialize_schema();
            }
        }
        
        flush_rewrite_rules();
    }

    /**
     * Framework Deactivation Gateway (Graceful Standby Mode)
     */
    public function deactivate_framework() {
        flush_rewrite_rules();
    }

    /**
     * Evaluates network architecture constraints before waking up module dependencies.
     */
    public function verify_environment_and_load() {
        if (!is_multisite()) {
            return;
        }

        // Load underlying infrastructure scripts
        $this->load_core_components();

        // Calculate and materialize active modules dynamically via Chart of Accounts rules
        $this->evaluate_and_materialize_modules();
    }

    /**
     * Sequentially loads structural system bricks into runtime execution memory.
     */
    private function load_core_components() {
        $components = array(
            'inc/common-core.php',       // Stateless Operational Validation Matrix
            'inc/db-materializer.php',   // High-Performance Materialized Flat Table Indexer
            'inc/api-cors-bridge.php',   // Multi-Channel REST API Ingestion Switchboard
            'inc/sso-handler.php',       // Single Sign-On Access Interceptor
            'agent/multi-claw.php'       // Multi-Claw Transient RAM Harvesting Module
        );

        foreach ($components as $component) {
            $path = MXM_PLUGIN_DIR . $component;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    /**
     * Dynamically calculates your rule-based matrix ID from any 3-letter handle.
     * Enforces strict 4-digit alignment mimicking management chart of accounts.
     * Rule: 'mns' -> "131419" -> "1314"
     */
    private function derive_handle_matrix_id($handle) {
        $chars = str_split(strtolower($handle));
        $raw_positions = '';

        foreach ($chars as $char) {
            $position = ord($char) - 96;
            if ($position >= 1 && $position <= 26) {
                $raw_positions .= $position;
            }
        }

        // Clamp tightly to the first 4 numerical characters (1111 - 9999 window)
        $fixed_4_digit_id = substr($raw_positions, 0, 4);

        return str_pad($fixed_4_digit_id, 4, '0');
    }

    /**
     * Evaluates handshake parameters and mounts permitted functional directories.
     */
    private function evaluate_and_materialize_modules() {
        // Human-readable, collision-protected operational directory layout
        $module_registry = array(
            'mns' => 'multi-network-sync',  // Calculated ID: 1314
            'gnv' => 'global-navigation',   // Calculated ID: 7142
            'ism' => 'isolation-members'    // Calculated ID: 9191
        );

        // Simulated validation array returned via the primary central telemetry layer.
        // Determines exactly which modules are unlocked for execution on this cluster footprint.
        $cleared_matrix_ids = array('1314', '7142', '9191');

        foreach ($module_registry as $handle => $folder_name) {
            // Run the Management Accounting algorithmic check
            $computed_id = $this->derive_handle_matrix_id($handle);

            // Match dynamic ID to permission boundaries before triggering require_once
            if (in_array($computed_id, $cleared_matrix_ids, true)) {
                $init_path = trailingslashit(MXM_PLUGIN_DIR) . "modules/{$folder_name}/init.php";

                if (file_exists($init_path)) {
                    require_once $init_path;
                }
            }
        }
    }
}

// Launch the synchronized framework ledger
Mxm_Wp_Multisite_Magic::launch();