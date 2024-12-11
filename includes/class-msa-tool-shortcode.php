<?php

class MSA_Tool_Shortcode
{
    public static function init()
    {
        add_shortcode('msa_tool_table', [self::class, 'render_table_shortcode']);
//        add_shortcode('msa_tool_debug', [self::class, 'debug_shortcode']);

        add_action('wp_enqueue_scripts', [self::class, 'register_scripts']);
    }



//    public static function debug_shortcode()
//    {
//        // Получаем данные
//        $grouped_data = MSA_Tool_Database::get_grouped_data();
//
//        // Отображаем на странице
//        return '<pre>' . esc_html(print_r($grouped_data, true)) . '</pre>';
//    }



    public static function register_scripts()
    {
        $plugin_url = plugin_dir_url(__FILE__);

        wp_register_script(
            'arcgis-main',
            'https://js.arcgis.com/4.23/init.js',
            ['jquery'],
            '4.23',
            true
        );
        wp_register_style(
            'arcgis-styles',
            'https://js.arcgis.com/4.23/esri/themes/light/main.css',
            [],
            '4.23'
        );


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
        $disable_arcgis = get_option('msa_tool_disable_arcgis', 0);

        if (!$disable_arcgis) {
            wp_enqueue_script('arcgis-main');
            wp_enqueue_style('arcgis-styles');
        }

        wp_enqueue_script('msa-tool-frontend');
        wp_enqueue_script('msa-tool-frontend-map');
        wp_enqueue_style('msa-tool-frontend-styles');


        // Получаем данные через Handler
        $data = MSA_Tool_Shortcode_Handler::get_data($atts);

        // Передача данных в JavaScript
        wp_localize_script('msa-tool-frontend', 'msaToolData', $data);
        wp_localize_script('msa-tool-frontend-map', 'msaToolSettings', [
            'portalItemId' => '5c0c0595be9c422bb95ace1bc48f610e',
            'featureLayerUrl' => 'https://services2.arcgis.com/3KQnhNHIDCtyRpO4/arcgis/rest/services/Neighborhood_Boundaries/FeatureServer/0',
        ]);

        ob_start();

        // Подключаем шаблон
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-shortcode-template.php';

        return ob_get_clean();
    }






}
