<?php class MSA_Tool_Shortcode_Handler
{

    public static function get_data($atts)
    {
        // Логика получения данных
        if (is_multisite()) {
            $global_blog_id = get_site_option('msa_tool_global_data', null);

            if ($global_blog_id) {
                switch_to_blog($global_blog_id);
                $data = MSA_Tool_Database::get_grouped_data(); // Используем get_grouped_data
                restore_current_blog();
            } else {
                $data = MSA_Tool_Database::get_grouped_data(); // Используем get_grouped_data
            }
        } else {
            $data = MSA_Tool_Database::get_grouped_data(); // Используем get_grouped_data
        }

        return $data;
    }

//    public static function prepare_data_for_display($region_main = 'Orlando') {
//        global $wpdb;
//
//        // Получаем данные из базы
//        $data = MSA_Tool_Database::get_grouped_data();
//        error_log(print_r($data, true));
//
//
//        // Группируем данные по категориям и индикаторам
//        $grouped_data = [];
//        foreach ($data as $row) {
//            $category = $row['category'];
//            $indicator = $row['indicator'];
//            $region = $row['region'];
//            $value = $row['value'];
//
//            if (!isset($grouped_data[$category])) {
//                $grouped_data[$category] = [];
//            }
//            if (!isset($grouped_data[$category][$indicator])) {
//                $grouped_data[$category][$indicator] = [];
//            }
//
//            $grouped_data[$category][$indicator][$region] = $value;
//        }
//        error_log('$grouped_data');
//        error_log($grouped_data);
//
//        return $grouped_data;
//    }

//    public static function render_table_shortcode($atts) {
//        ob_start();
//
//        // Получаем обработанные данные
//        $data = self::prepare_data_for_display();
//
//        // Генерируем базовую структуру контейнера
//        echo '<div id="msa-tool-container">';
//        echo '<div id="msa-tool-filters"></div>';
//        echo '<div id="msa-tool-table"></div>';
//        echo '</div>';
//
//        // Передаем данные в JS
//        wp_localize_script(
//            'msa-tool-frontend-js',
//            'msaToolData',
//            [
//                'categories' => $data,
//            ]
//        );
//
//        return ob_get_clean();
//    }
}
