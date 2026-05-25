<?php
/**
 * Module Name: MXM Common Core Engine
 * Description: The stateless runtime execution manager and cryptographic validation matrix.
 * Version:     1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Common_Core {

    private static $instance = null;

    /**
     * Instantiates the Common Core across the active thread lifecycle.
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize basic system execution states in volatile memory
        add_action('plugins_loaded', array($this, 'initialize_stateless_matrix'));
    }

    /**
     * Prepares core system hooks without committing persistent rows or tracking data to disk.
     */
    public function initialize_stateless_matrix() {
        // This structural anchor allows other Lego brick modules to register themselves
        do_action('mxm_core_matrix_initialized');
    }

    /**
     * Verifies the authenticity of an incoming split-channel segment body.
     * Implements the strict, time-attack resistant verification layer.
     * * @param string $incoming_hash  The SHA-256 signature passed via the X-MXM-Signature header.
     * @param string $payload_data   The raw body payload from the REST request.
     * @return bool                  True if valid, False if rejected.
     */
    public function validate_recipe_signature($incoming_hash, $payload_data) {
        if (empty($incoming_hash) || empty($payload_data)) {
            return false;
        }

        /**
         * Fetch the secure server anchor token. 
         * Falls back to standard WordPress keys if a bespoke MXM constant isn't defined in wp-config.
         */
        $server_secret = defined('MXM_ENCRYPTION_KEY') ? MXM_ENCRYPTION_KEY : SECURE_AUTH_KEY;

        /**
         * Generate the strict, single-use cryptographic signature matching the data segment.
         * This ensures the payload hasn't been altered or injected mid-transit.
         */
        $computed_signature = hash_hmac('sha256', $payload_data, $server_secret);

        /**
         * Perform a time-attack resistant strict string comparison.
         * Compares characters at a constant rate so hackers cannot guess signature lengths based on server latency.
         */
        return hash_equals($computed_signature, $incoming_hash);
    }
}

// Engage the Core Engine
Mxm_Common_Core::instance();