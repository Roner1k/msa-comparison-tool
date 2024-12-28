<?php

class MSA_Tool_Shortcode
{
    public static function init()
    {
        add_shortcode('msa_tool_table', [self::class, 'render_table_shortcode']);
        add_action('wp_enqueue_scripts', [self::class, 'register_scripts']);
    }

    public static function register_scripts()
    {
        $plugin_url = plugin_dir_url(__FILE__);

        // Register frontend scripts
        wp_register_script(
            'msa-tool-frontend',
            $plugin_url . '../assets/js/msa-tool-frontend.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_register_script(
            'msa-tool-frontend-map',
            $plugin_url . '../assets/js/msa-tool-frontend-map.js',
            [],
            '1.0',
            true
        );

        // Register frontend styles
        wp_register_style(
            'msa-tool-frontend-styles',
            $plugin_url . '../assets/css/msa-tool-frontend.css',
            [],
            '1.0'
        );
    }

    public static function render_table_shortcode($atts)
    {
        // Enqueue necessary scripts and styles
        wp_enqueue_script('msa-tool-frontend');
        wp_enqueue_script('msa-tool-frontend-map');
        wp_enqueue_style('msa-tool-frontend-styles');

        // Get data for rendering the table and map
        $data = MSA_Tool_Shortcode_Handler::get_data($atts);
        $map_data = MSA_Tool_Shortcode_Handler::get_map_data();

        // Localize data for frontend scripts
        wp_localize_script('msa-tool-frontend-map', 'msaMapData', [
            'regions' => $map_data,
            'portalItemId' => '5c0c0595be9c422bb95ace1bc48f610e',
            'featureLayerUrl' => 'https://services2.arcgis.com/3KQnhNHIDCtyRpO4/arcgis/rest/services/tl_2023_us_cbsa_view/FeatureServer',
            'activeRegions' => ['orlando-fl']
        ]);

        wp_localize_script('msa-tool-frontend', 'msaToolData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        ob_start();

        // Include template for shortcode rendering
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-shortcode-template.php';

        return ob_get_clean();
    }
}
