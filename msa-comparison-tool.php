<?php
/**
 * Plugin Name: MSA Comparison Tool
 * Description: A tool for comparing MSA data with import, export, and visualization capabilities.
 * Version: 1.0
 * Author: NL
 */

defined('ABSPATH') || exit;


require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-database.php';

require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-import.php';

require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-msa-tool-shortcode-handler.php';


register_activation_hook(__FILE__, ['MSA_Tool_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['MSA_Tool_Deactivator', 'deactivate']);

if (is_admin()) {
    MSA_Tool_Admin::init();
} else {
    MSA_Tool_Shortcode::init();

}
add_action('wp_enqueue_scripts', 'msa_tool_enqueue_arcgis_scripts');

function msa_tool_enqueue_arcgis_scripts() {
    $disable_arcgis = get_option('msa_tool_disable_arcgis', 0);

    if ($disable_arcgis) {
        return;
    }

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

    wp_enqueue_script('arcgis-init');
    wp_enqueue_style('arcgis-styles');
}
