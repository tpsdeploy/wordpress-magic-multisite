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
 * Security Core:  Zero-Friction Bot Catch Matrix
 * Protocol:       Chameleon Honeypot & Velocity Gate
 * =========================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Xami_Gatekeeper {

    private static $trap_field = '_xami_sys_meta_nonce';
    private static $signature_payload = 'gx_ami_clearance_verified';

    /**
     * Injects the invisible honeypot mechanics cleanly into any active form stream
     */
    public static function inject_honeypot_vector() {
        $timestamp = current_time('timestamp', 1);
        
        // Output a completely hidden configuration wrapper
        echo '<div style="position:absolute; left:-9999px; top:-9999px; height:0; width:0; overflow:hidden;" aria-hidden="true">';
        
        // The Trap Field: Pre-seeded with our validation signature
        echo '<input type="text" name="' . esc_attr(self::$trap_field) . '" value="' . esc_attr(self::$signature_payload) . '" autocomplete="off" tabindex="-1" />';
        
        // The Velocity Anchor: Tracks when the form was delivered to the browser
        echo '<input type="hidden" name="_xami_render_epoch" value="' . esc_attr($timestamp) . '" />';
        
        echo '</div>';
    }

    /**
     * Evaluates incoming request parameters for bot behavior.
     * Enforces velocity limits and honeypot integrity checks.
     */
    public static function evaluate_request_security() {
        // 1. HONEYPOT INTEGRITY CHECK
        // If the field isn't present, it's an un-mapped custom script. If the value changed, a bot touched it.
        if (!isset($_POST[self::$trap_field]) || $_POST[self::$trap_field] !== self::$signature_payload) {
            self::trigger_defensive_shutdown('Honeypot Vector Tripped. Malicious payload mutation detected.');
        }

        // 2. VELOCITY CHECK (The Human Minimum Limit)
        if (isset($_POST['_xami_render_epoch'])) {
            $render_time = intval($_POST['_xami_render_epoch']);
            $submit_time = current_time('timestamp', 1);
            $elapsed_seconds = $submit_time - $render_time;

            // If a form is submitted or a node is initialized in under 2 seconds, it's mechanically impossible for a human
            if ($elapsed_seconds < 2) {
                self::trigger_defensive_shutdown('Velocity Guard Tripped. Execution speed matches automated script profiles.');
            }
        }
    }

    /**
     * Executes an uncompromising system drop without loading resource-heavy WordPress error screens
     */
    private static function trigger_defensive_shutdown($reason) {
        // Send a clean, non-descript server failure header to throw off the scanning bot
        status_header(403);
        header('Content-Type: text/plain; charset=utf-8');
        
        // Give the script scanner an explicit, direct statement of refusal
        exit("xAMI Security Matrix Exception: Access Denied. Go to hell.");
    }
}