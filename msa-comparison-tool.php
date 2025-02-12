<?php
/**
 * Plugin Name: MSA Comparison Tool
 * Description: A tool for comparing MSA data with import, export, and visualization capabilities.
 * Version: 1.0
 * Author: NL
 */

// Prevent direct access to the file
defined('ABSPATH') || exit;

// Include core plugin classes
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-database.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-import.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-shortcode-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-ajax.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['MSA_Tool_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['MSA_Tool_Deactivator', 'deactivate']);

// Initialize plugin based on context
if (defined('DOING_AJAX') && DOING_AJAX) {
    // AJAX requests
    MSA_Tool_Ajax::init_hooks();
} elseif (is_admin()) {
    // Admin panel
    MSA_Tool_Admin::init();
} else {
    // Frontend
    MSA_Tool_Shortcode::init();
}

// Enqueue ArcGIS scripts and styles
add_action('wp_enqueue_scripts', 'msa_tool_enqueue_arcgis_scripts');

function msa_tool_enqueue_arcgis_scripts() {
    $disable_arcgis = get_option('msa_tool_disable_arcgis', 0);

    // Do not load scripts if disabled
    if ($disable_arcgis) {
        return;
    }

    // Register ArcGIS scripts and styles
    wp_register_script(
        'arcgis-init',
        'https://js.arcgis.com/4.23/init.js',
        [],
        null,
        true
    );

    wp_register_style(
        'arcgis-styles',
        'https://js.arcgis.com/4.23/esri/themes/light/main.css',
        [],
        null
    );

    // Enqueue the scripts and styles
    wp_enqueue_script('arcgis-init');
    wp_enqueue_style('arcgis-styles');
}
