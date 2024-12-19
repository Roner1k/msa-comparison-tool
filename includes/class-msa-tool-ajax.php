<?php

class MSA_Tool_Ajax {

    public static function init_hooks() {
        // Регистрируем обработчики только для фронтенда
        add_action('wp_ajax_export_pdf', [__CLASS__, 'export_pdf']); // Для авторизованных пользователей
        add_action('wp_ajax_nopriv_export_pdf', [__CLASS__, 'export_pdf']); // Для неавторизованных пользователей
    }

    public static function export_pdf() {
        // Получаем данные таблицы из POST-запроса
        // Подключаем autoload, если он ещё не подключен
        if (!class_exists('TCPDF')) {
            require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
        }

//        $table_data = json_decode(stripslashes($_POST['table_data']), true);
        $table_data = [
            'headers' => ['Indicator', 'Region 1', 'Region 2', 'Region 3'],
            'rows' => [
                ['GDP', '1000', '2000', '3000'],
                ['Population', '500', '600', '700'],
                ['Area (sq km)', '300', '450', '600'],
            ]
        ];


        if (empty($table_data) || !isset($table_data['headers']) || !isset($table_data['rows'])) {
            wp_send_json_error(['message' => 'Invalid table data'], 400);
        }

        // Генерируем PDF
        MSA_Tool_PDF_Export::generate_pdf($table_data);

        // Выход после генерации
        wp_die();
    }

}
