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

    public static function get_map_data()
    {
        // Логика получения данных карты
        if (is_multisite()) {
            $global_blog_id = get_site_option('msa_tool_global_data', null);

            if ($global_blog_id) {
                switch_to_blog($global_blog_id);
                $map_data = MSA_Tool_Database::get_map_data();
                restore_current_blog();
            } else {
                $map_data = MSA_Tool_Database::get_map_data();
            }
        } else {
            $map_data = MSA_Tool_Database::get_map_data();
        }

        return $map_data;
    }


}
