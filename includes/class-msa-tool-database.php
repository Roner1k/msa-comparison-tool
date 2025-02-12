<?php

class MSA_Tool_Database
{
    public static function get_grouped_data()
    {
        global $wpdb;

        // Define table names
        $data_table = $wpdb->get_blog_prefix() . 'msa_tool_data';
        $map_table = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

        // Check global mode
        $global_blog_id = get_site_option('msa_tool_global_data', 0);
        if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
            $data_table = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_data';
            $map_table = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_map_keys';
        }

        // Verify table existence
        if (
            $wpdb->get_var("SHOW TABLES LIKE '$data_table'") != $data_table ||
            $wpdb->get_var("SHOW TABLES LIKE '$map_table'") != $map_table
        ) {
            error_log("One or both tables ($data_table, $map_table) do not exist.");
            return [];
        }

        // SQL query to join data from two tables
        $query = "
        SELECT 
            data.category,
            data.subcategory,
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

        // Initialize grouped data structure
        $grouped_data = [
            'categories' => [], // List of categories
            'regions' => [], // List of regions with their data
        ];

        foreach ($all_data as $row) {
            $category = isset($row['category']) ? trim($row['category']) : 'Unknown Category';
            $subcategory = isset($row['subcategory']) ? trim($row['subcategory']) : '';
            $indicator = isset($row['indicator']) ? trim($row['indicator']) : 'Unknown Indicator';
            $region = isset($row['region']) ? trim($row['region']) : 'Unknown Region';
            $slug = isset($row['slug']) ? trim($row['slug']) : sanitize_title($region);
            $value = isset($row['value']) ? $row['value'] : 0;
            $map_id = $row['map_id'] ?? null;

            // Group by categories (top level)
            if (!isset($grouped_data['categories'][$category])) {
                $grouped_data['categories'][$category] = [];
            }
            if (!in_array($indicator, $grouped_data['categories'][$category])) {
                $grouped_data['categories'][$category][] = $indicator;
            }

            // Group by regions
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

            // Handle "Rank" indicators
            if (preg_match('/\s[Rr]ank$/', $indicator)) { // Если индикатор заканчивается на " Rank"
                $clean_indicator = preg_replace('/\s[Rr]ank$/', '', $indicator); // Убираем " Rank"

                if (!empty($subcategory)) {
                    $grouped_data['regions'][$region]['categories'][$category][$clean_indicator]['subcategories'][$subcategory]['rank'] = $value;
                } else {
                    if (!isset($grouped_data['regions'][$region]['categories'][$category][$clean_indicator])) {
                        $grouped_data['regions'][$region]['categories'][$category][$clean_indicator] = [
                            'value' => null,
                            'subcategories' => [],
                        ];
                    }

                    $grouped_data['regions'][$region]['categories'][$category][$clean_indicator]['rank'] = $value;
                }
            } else {
                if (!isset($grouped_data['regions'][$region]['categories'][$category][$indicator])) {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator] = [
                        'value' => null,
                        'subcategories' => [],
                    ];
                }

                if (empty($subcategory)) {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator]['value'] = $value;
                } else {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator]['subcategories'][$subcategory] = $value;
                }
            }

        }

        //error_log('reg data' . print_r($grouped_data, true));

        return $grouped_data;
    }


    public static function get_map_data()
    {
        global $wpdb;

        $map_table = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

        // Check global mode
        if (is_multisite()) {
            $global_blog_id = get_site_option('msa_tool_global_data', 0);

            if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
                $map_table = $wpdb->get_blog_prefix($global_blog_id) . 'msa_tool_map_keys';

                if ($wpdb->get_var("SHOW TABLES LIKE '$map_table'") != $map_table) {
                    error_log("Global table $map_table does not exist.");
                    return [];
                }
            }
        }

        // Verify table existence
        if ($wpdb->get_var("SHOW TABLES LIKE '$map_table'") != $map_table) {
            error_log("Table $map_table does not exist.");
            return [];
        }

        // Retrieve data from map_keys table
        $results = $wpdb->get_results("SELECT region_slug, map_id FROM $map_table", ARRAY_A);

        if (!$results) {
            error_log("No data found in $map_table.");
        }

        return $results;
    }
}
