<?php

class MSA_Tool_Activator
{
    public static function activate()
    {
        global $wpdb;
        self::create_directories();


        /**
         * Global Mode Explanation:
         *
         * In multisite WordPress networks, global mode centralizes data management by storing all data
         * in a designated "global site" within the network. This enables centralized data handling and
         * synchronization across all subsites.
         *
         * Key Features:
         * 1. **Centralized Data**: All data is stored and managed in the global site's database tables.
         * 2. **Context Switching**: Functions temporarily switch to the global site context when accessing
         *    or modifying data, and return to the original site context afterward.
         * 3. **Access Restriction**: Attempts to access plugin settings or data editing pages on any site
         *    other than the global site are blocked. This ensures consistent and centralized data management.
         *
         * When Global Mode is disabled:
         * - Each subsite in the network uses its local database tables for plugin data.
         */

        if (is_multisite()) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);

                // Create tables for each site
                self::create_data_table($wpdb->get_blog_prefix() . 'msa_tool_data');
                self::create_map_keys_table($wpdb->get_blog_prefix() . 'msa_tool_map_keys');

                restore_current_blog();
            }
        } else {
            // For a single site
            self::create_data_table($wpdb->get_blog_prefix() . 'msa_tool_data');
            self::create_map_keys_table($wpdb->get_blog_prefix() . 'msa_tool_map_keys');
        }
    }

    private static function create_data_table($table_name)
    {
        global $wpdb;

        // Check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            category VARCHAR(255) NOT NULL,
            subcategory VARCHAR(255) NULL,
            indicator VARCHAR(255) NOT NULL,
            region VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            value TEXT NOT NULL,
            PRIMARY KEY (id)            
        ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }


    private static function create_map_keys_table($table_name)
    {
        global $wpdb;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            // SQL to create the msa_tool_map_keys table
            $sql = "CREATE TABLE $table_name (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    region_slug VARCHAR(255) NOT NULL,
    map_id VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_slug (region_slug(100)),
    UNIQUE KEY unique_map_id (map_id(100))
) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);

        }
    }

    private static function create_directories()
    {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/msa-tool';

        $directories = [
            $base_dir,
            $base_dir . '/exports',
            $base_dir . '/imports',
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (wp_mkdir_p($dir)) {
                    error_log("[MSA TOOL] Directory created: {$dir}");
                } else {
                    error_log("[MSA TOOL ERROR] Failed to create directory: {$dir}");
                }
            }
        }
    }
}