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


register_activation_hook(__FILE__, ['MSA_Tool_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['MSA_Tool_Deactivator', 'deactivate']);

if (is_admin()) {
    MSA_Tool_Admin::init();
} else {
    MSA_Tool_Shortcode::init();
}