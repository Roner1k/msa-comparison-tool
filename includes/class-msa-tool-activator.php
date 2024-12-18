<?php
class MSA_Tool_Activator
{
    public static function activate()
    {
        global $wpdb;

        // Для мультисайту
        if (is_multisite()) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);

                // Створення таблиць для кожного сайту
                self::create_data_table($wpdb->get_blog_prefix() . 'msa_tool_data');
                self::create_map_keys_table($wpdb->get_blog_prefix() . 'msa_tool_map_keys');

                restore_current_blog();
            }
        } else {
            // Для одиночного сайту
            self::create_data_table($wpdb->get_blog_prefix() . 'msa_tool_data');
            self::create_map_keys_table($wpdb->get_blog_prefix() . 'msa_tool_map_keys');
        }
    }

    private static function create_data_table($table_name)
    {
        global $wpdb;

        // Перевірка, чи існує таблиця
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            // SQL для створення таблиці msa_tool_data
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                indicator VARCHAR(255) NOT NULL,
                region VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                value TEXT NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY unique_data (category(50), indicator(50), region(50))
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);

//            error_log("Table created: $table_name");
        }
    }

    private static function create_map_keys_table($table_name)
    {
        global $wpdb;

        // Перевірка, чи існує таблиця
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            // SQL для створення таблиці msa_tool_map_keys
            $sql = "CREATE TABLE $table_name (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    region_slug VARCHAR(255) NOT NULL,
    map_id VARCHAR(255) NULL, -- Дозволяємо NULL
    PRIMARY KEY (id),
    UNIQUE KEY unique_slug (region_slug(100)),
    UNIQUE KEY unique_map_id (map_id(100))
) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);

//            error_log("Table created: $table_name");
        }
    }
}