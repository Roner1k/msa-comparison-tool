<?php

class MSA_Tool_Database
{
    public static function get_all_data()
    {
        global $wpdb;

        // Определяем имя таблицы
        $table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';

        // Проверяем существование таблицы
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log("Table $table_name does not exist.");
            return [];
        }

        // Проверяем глобальный режим
        $global_blog_id = get_site_option('msa_tool_global_data', 0);
        if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
            // Если глобальный режим включен, меняем таблицу на глобальную
            $table_name = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_data';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                error_log("Global table $table_name does not exist.");
                return [];
            }
        }

        // Загружаем данные
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        // Логируем результаты
        error_log("Data fetched from $table_name: " . print_r($results, true));

        return $results ? $results : [];
    }


    public static function get_grouped_data()
    {
        $all_data = self::get_all_data();

        $grouped_data = [
            'categories' => [],
            'regions' => [],
        ];

        foreach ($all_data as $row) {
            $category = $row['category'] ?? 'Unknown Category';
            $indicator = $row['indicator'] ?? 'Unknown Indicator';
            $region = $row['region'] ?? 'Unknown Region';
            $value = $row['value'] ?? 0;

            // Группируем по категориям
            if (!isset($grouped_data['categories'][$category])) {
                $grouped_data['categories'][$category] = [];
            }
            if (!in_array($indicator, $grouped_data['categories'][$category])) {
                $grouped_data['categories'][$category][] = $indicator;
            }

            // Группируем по регионам
            if (!isset($grouped_data['regions'][$region])) {
                $grouped_data['regions'][$region] = [];
            }
            if (!isset($grouped_data['regions'][$region][$category])) {
                $grouped_data['regions'][$region][$category] = [];
            }
            $grouped_data['regions'][$region][$category][$indicator] = $value;
        }

        error_log("Grouped data: " . print_r($grouped_data, true));

        return $grouped_data;
    }
}
