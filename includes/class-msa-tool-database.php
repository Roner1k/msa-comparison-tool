<?php

class MSA_Tool_Database
{


    public static function get_grouped_data()
    {
        global $wpdb;

        // Определяем имена таблиц
        $data_table = $wpdb->get_blog_prefix() . 'msa_tool_data';
        $map_table = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

        // Проверяем глобальный режим
        $global_blog_id = get_site_option('msa_tool_global_data', 0);
        if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
            $data_table = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_data';
            $map_table = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_map_keys';
        }

        // Проверяем существование таблиц
        if ($wpdb->get_var("SHOW TABLES LIKE '$data_table'") != $data_table || $wpdb->get_var("SHOW TABLES LIKE '$map_table'") != $map_table) {
            error_log("One or both tables ($data_table, $map_table) do not exist.");
            return [];
        }

        // SQL-запрос для объединения данных из двух таблиц
        $query = "
        SELECT 
            data.category,
            data.indicator,
            data.region,
            data.slug,
            data.value,
            map.map_id
        FROM 
            $data_table AS data
        LEFT JOIN 
            $map_table AS map
        ON 
            data.slug = map.region_slug
    ";

        $all_data = $wpdb->get_results($query, ARRAY_A);

        if (!$all_data) {
            error_log("No data found in joined tables.");
            return [];
        }

        // Формируем группированный массив
        $grouped_data = [
            'categories' => [],
            'regions' => [],
        ];

        foreach ($all_data as $row) {
            $category = $row['category'] ?? 'Unknown Category';
            $indicator = $row['indicator'] ?? 'Unknown Indicator';
            $region = $row['region'] ?? 'Unknown Region';
            $slug = $row['slug'] ?? sanitize_title($region); // Используем slug из базы или генерируем из названия региона
            $value = $row['value'] ?? 0;
            $map_id = $row['map_id'] ?? null;

            // Группируем по категориям
            if (!isset($grouped_data['categories'][$category])) {
                $grouped_data['categories'][$category] = [];
            }
            if (!in_array($indicator, $grouped_data['categories'][$category])) {
                $grouped_data['categories'][$category][] = $indicator;
            }

            // Группируем по регионам
            // Группируем по регионам
            if (!isset($grouped_data['regions'][$region])) {
                $grouped_data['regions'][$region] = [
                    'slug' => $slug,
                    'map_id' => $map_id,
                    'categories' => [],
                ];
            }

            if (!isset($grouped_data['regions'][$region]['categories'][$category])) {
                $grouped_data['regions'][$region]['categories'][$category] = [];
            }

// Проверка на Rank
            if (strpos($indicator, 'Rank') !== false) {
                $clean_indicator = str_replace(' Rank', '', $indicator);
                $grouped_data['regions'][$region]['categories'][$category][$clean_indicator]['rank'] = $value;
            } else {
                // Добавляем только строки, которые не являются Rank
                if (!isset($grouped_data['regions'][$region]['categories'][$category][$indicator])) {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator] = [
                        'value' => $value
                    ];
                }
            }


        }

//        error_log("Grouped data with map_id: " . print_r($grouped_data, true));

        return $grouped_data;
    }

    public static function get_map_data()
    {
        global $wpdb;

        // Определяем имя таблицы
        $map_table = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

        // Проверяем глобальный режим
        if (is_multisite()) {
            $global_blog_id = get_site_option('msa_tool_global_data', 0);

            if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
                // Если глобальный режим включен, меняем таблицу на глобальную
                $map_table = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_map_keys';

                // Проверяем существование таблицы
                if ($wpdb->get_var("SHOW TABLES LIKE '$map_table'") != $map_table) {
                    error_log("Global table $map_table does not exist.");
                    return [];
                }
            }
        }

        // Проверяем существование таблицы
        if ($wpdb->get_var("SHOW TABLES LIKE '$map_table'") != $map_table) {
            error_log("Table $map_table does not exist.");
            return [];
        }

        // Получаем данные из таблицы
        $results = $wpdb->get_results("SELECT region_slug, map_id FROM $map_table", ARRAY_A);

        if (!$results) {
            error_log("No data found in $map_table.");
        }

        return $results;
    }


}
