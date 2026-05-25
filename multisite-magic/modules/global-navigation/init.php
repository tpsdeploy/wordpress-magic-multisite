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
 * Module Identity Matrix: Module 492
 * Namespace:              xAMI (where x = 492)
 * Functional Role:        Zero-Query Navigation Injection Engine
 * =========================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

class Mxm_Xami_Navigation_Engine {

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Intercept WordPress navigation markup compilation entirely
        add_filter('pre_wp_nav_menu', array($this, 'intercept_and_inject_global_menu'), 10, 2);
    }

    /**
     * Catches native menu render loops and short-circuits them using the flat-file asset.
     * Bypasses core database generation completely.
     */
    public function intercept_and_inject_global_menu($output, $args) {
        // PUSHBACK/GUARD: Only hijack the menu if it explicitly targets the designated primary slot
        if (!isset($args->theme_location) || $args->theme_location !== 'primary') {
            return $output; // Return untouched HTML to let local theme menus pass through
        }

        // Tap directly into the Module 711 core instance flat-file asset registry
        if (!class_exists('Mxm_Xami_Node_Orchestrator')) {
            return $output;
        }

        $orchestrator = Mxm_Xami_Node_Orchestrator::instance();
        $menu_data = $orchestrator->read_compiled_matrix_asset('492_global');

        // Fallback: If the flat-file doesn't exist yet, let the system render local defaults gracefully
        if (empty($menu_data) || !is_array($menu_data)) {
            return $output;
        }

        // Reconstruct raw HTML markup directly from the static RAM object array
        $menu_id = 'xami-global-nav';
        $html = '<ul id="' . esc_attr($menu_id) . '" class="' . esc_attr($args->menu_class) . '">';

        foreach ($menu_data as $item) {
            // Filter out child sub-items for the baseline wrapper (handling flat rendering)
            if (!empty($item['parent'])) {
                continue; 
            }

            $classes = implode(' ', array_map('sanitize_html_class', $item['classes']));
            $html .= '<li class="menu-item ' . esc_attr($classes) . '">';
            $html .= '<a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}

// Ignition
Mxm_Xami_Navigation_Engine::instance();