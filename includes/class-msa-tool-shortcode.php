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
        wp_register_style(
            'msa-tool-frontend-styles',
            $plugin_url . '../assets/css/msa-tool-frontend.css',
            [],
            '1.0'
        );
    }


    public static function render_table_shortcode($atts)
    {

        wp_enqueue_script('msa-tool-frontend');
        wp_enqueue_script('msa-tool-frontend-map');
        wp_enqueue_style('msa-tool-frontend-styles');


        $data = MSA_Tool_Shortcode_Handler::get_data($atts);

        $map_data = MSA_Tool_Shortcode_Handler::get_map_data();

        wp_localize_script('msa-tool-frontend', 'msaToolData', $data);
        wp_localize_script('msa-tool-frontend-map', 'msaMapData', [
            'regions' => $map_data,
            'portalItemId' => '5c0c0595be9c422bb95ace1bc48f610e',
            'featureLayerUrl' => 'https://services2.arcgis.com/3KQnhNHIDCtyRpO4/arcgis/rest/services/tl_2023_us_cbsa_s/FeatureServer/0',
            'activeRegions' => [] // Для синхронизации активных регионов

        ]);

        ob_start();

        // Подключаем шаблон
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-shortcode-template.php';

        return ob_get_clean();
    }


}
