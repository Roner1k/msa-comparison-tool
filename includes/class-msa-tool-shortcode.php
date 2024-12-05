<?php

class MSA_Tool_Shortcode {
    public static function init() {
        add_shortcode('msa_tool_table', [self::class, 'render_table_shortcode']);
        add_action('wp_enqueue_scripts', [self::class, 'register_scripts']);
    }

    public static function render_table_shortcode($atts) {
        // Подключаем скрипты только на странице со шорткодом
        self::enqueue_scripts($atts);

        ob_start();

        // Получаем данные через Handler
        $data = MSA_Tool_Shortcode_Handler::get_data($atts);

        // Подключаем шаблон
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-shortcode-template.php';

        return ob_get_clean();
    }

    public static function register_scripts() {
        $plugin_url = plugin_dir_url(__FILE__);

        // Регистрируем основной JS-файл
        wp_register_script(
            'msa-tool-frontend',
            $plugin_url . '../assets/js/msa-tool-frontend.js',
            ['jquery'], // Зависимости (если нужны)
            '1.0',
            true // Подключаем в футере
        );

        // Регистрируем JS-файл для карты
        wp_register_script(
            'msa-tool-frontend-map',
            $plugin_url . '../assets/js/msa-tool-frontend-map.js',
            [],
            '1.0',
            true // Подключаем в футере
        );
    }

    private static function enqueue_scripts($atts) {
        // Данные для JavaScript
        $data = MSA_Tool_Shortcode_Handler::get_data($atts);

        // Подключаем скрипты
        wp_enqueue_script('msa-tool-frontend');
        wp_enqueue_script('msa-tool-frontend-map');

        // Передаем данные в JS
        wp_localize_script(
            'msa-tool-frontend',
            'msaToolData',
            [
                'categories' => $data['categories'], // Пример данных
                'regions' => $data['regions'],       // Пример данных
            ]
        );
    }
}
