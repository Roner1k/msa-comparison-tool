<?php
class MSA_Tool_Shortcode {
    public static function init() {
        add_shortcode('msa_tool_table', [self::class, 'render_table_shortcode']);
    }

    public static function render_table_shortcode($atts) {
        // Начинаем буферизацию вывода
        ob_start();

        // Проверяем, включён ли мультисайт
        if (is_multisite()) {
            // Проверяем, включён ли глобальный режим
            $global_blog_id = get_site_option('msa_tool_global_data', null);

            if ($global_blog_id) {
                // Если глобальный режим включён, переключаемся на глобальный сайт
                switch_to_blog($global_blog_id);
                $data = MSA_Tool_Database::get_all_data(); // Получаем данные с глобального сайта
                restore_current_blog(); // Возвращаемся к текущему сайту
            } else {
                // Если глобальный режим не включён, берём данные с текущего сайта
                $data = MSA_Tool_Database::get_all_data();
            }
        } else {
            // Если это не мультисайт, работаем как обычно
            $data = MSA_Tool_Database::get_all_data();
        }

        // Выводим данные
        if (!empty($data)) {
            echo '<table>';
            echo '<thead><tr><th>Category</th><th>Indicator</th><th>Region</th><th>Value</th></tr></thead>';
            echo '<tbody>';
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row['category']) . '</td>';
                echo '<td>' . esc_html($row['indicator']) . '</td>';
                echo '<td>' . esc_html($row['region']) . '</td>';
                echo '<td>' . esc_html($row['value']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No data available.</p>';
        }

        // Возвращаем результат
        return ob_get_clean();
    }
}