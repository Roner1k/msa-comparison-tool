<?php

class MSA_Tool_Database
{
    /*
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
            'regions' => [],    // List of regions with their data
        ];

        foreach ($all_data as $row) {
            $category = $row['category'] ?? 'Unknown Category';
            $subcategory = $row['subcategory'] ?? '';
            $indicator = $row['indicator'] ?? 'Unknown Indicator';
            $region = $row['region'] ?? 'Unknown Region';
            $slug = $row['slug'] ?? sanitize_title($region);
            $value = $row['value'] ?? 0;
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
            if (strpos($indicator, 'Rank') !== false) {
                $clean_indicator = str_replace(' Rank', '', $indicator);
                $grouped_data['regions'][$region]['categories'][$category][$clean_indicator]['rank'] = $value;
            } else {
                // Process regular indicators
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

        return $grouped_data;
    }
    */
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

        // Log all data for inspection
       // error_log("All Data: " . print_r($all_data, true));

        // Initialize grouped data structure
        $grouped_data = [
            'categories' => [], // List of categories
            'regions' => [],    // List of regions with their data
        ];

        foreach ($all_data as $row) {
            $category = $row['category'] ?? 'Unknown Category';
            $subcategory = $row['subcategory'] ?? '';
            $indicator = $row['indicator'] ?? 'Unknown Indicator';
            $region = $row['region'] ?? 'Unknown Region';
            $slug = $row['slug'] ?? sanitize_title($region);
            $value = $row['value'] ?? 0;
            $map_id = $row['map_id'] ?? null;

            // Log each row being processed
//            error_log("Processing Row: " . print_r($row, true));

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
            if (strpos($indicator, 'Rank') !== false) {
                $clean_indicator = str_replace(' Rank', '', $indicator);
                if (!empty($subcategory)) {
                    // Сохранение ранга для подкатегории
                    $grouped_data['regions'][$region]['categories'][$category][$clean_indicator]['subcategories'][$subcategory]['rank'] = $value;
                } else {
                    // Сохранение ранга для индикатора верхнего уровня
                    $grouped_data['regions'][$region]['categories'][$category][$clean_indicator]['rank'] = $value;
                }
            } else {
                // Process regular indicators
                if (!isset($grouped_data['regions'][$region]['categories'][$category][$indicator])) {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator] = [
                        'value' => null,
                        'subcategories' => [],
                    ];
                }

                if (empty($subcategory)) {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator]['value'] = $value;

                    // Log indicator without subcategory
//                    error_log("Added Indicator Value: {$indicator} => {$value}");
                } else {
                    $grouped_data['regions'][$region]['categories'][$category][$indicator]['subcategories'][$subcategory] = $value;

                    // Log indicator with subcategory
//                    error_log("Added Subcategory: {$subcategory} => {$value}");
                }
            }
        }

        // Log the final grouped data structure
//        error_log(print_r($grouped_data['regions'], true));

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
