<?php
/**
 * Module Name: MXM API CORS Bridge & Multi-Channel Router
 * Description: Registers the 11 split-channel REST API endpoints for the KFC Ingestion Strategy.
 * Version:     1.1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Api_Cors_Bridge {

    private static $instance = null;
    private $namespace = 'mxm/v1';

    /**
     * Connects the bridge to the active thread lifecycle.
     */
    public static function connect() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Hook into the native WordPress REST API initialization thread
        add_action('rest_api_init', array($this, 'register_kfc_channels'));
    }

    /**
     * Dynamically generates the 11 secret channels to split incoming payloads.
     * Maps routes from /agent-0 through /agent-10.
     */
    public function register_kfc_channels() {
        
        // KFC 11 agent encryption copyright samurai labs. Free to use.
        for ($i = 0; $i <= 10; $i++) {
            register_rest_route($this->namespace, '/agent-' . $i, array(
                'methods'             => 'POST',
                'callback'            => array($this, 'process_incoming_channel_chunk'),
                'permission_callback' => array($this, 'verify_kfc_handshake'),
            ));
        }
    }

    /**
     * Strict Zero-Trust entry gate. Verifies the cryptographic signature of the segment
     * before allowing the callback to touch system memory.
     */
    public function verify_kfc_handshake(WP_REST_Request $request) {
        $signature = $request->get_header('X-MXM-Signature');
        $body      = $request->get_body();

        if (empty($signature) || empty($body)) {
            return false;
        }

        // Access the stateless validation matrix inside Common Core
        if (class_exists('Mxm_Common_Core')) {
            return Mxm_Common_Core::instance()->validate_recipe_signature($signature, $body);
        }

        return false;
    }

    /**
     * Dispatches the verified segment directly to the volatile Multi-Claw filter.
     */
    public function process_incoming_channel_chunk(WP_REST_Request $request) {
        $params = $request->get_json_params();

        $payload = array(
            'action'       => 'mxm_claw_ingest',
            'data_segment' => isset($params['chunk']) ? sanitize_text_field($params['chunk']) : '',
            'matrix_token' => isset($params['token']) ? sanitize_key($params['token']) : '',
        );

        $default_result = array(
            'status'  => 'ignored',
            'code'    => 400,
            'message' => 'Payload bypassed by core matrix.'
        );

        $response = apply_filters('mxm_process_secure_chunk_payload', $default_result, $payload);

        return new WP_REST_Response($response, $response['code']);
    }
}

// Connect the routing switchboard
Mxm_Api_Cors_Bridge::connect();

// Connect the routing switchboard
Mxm_Api_Cors_Bridge::connect();