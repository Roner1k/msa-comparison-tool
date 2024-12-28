<?php

class MSA_Tool_Shortcode_Handler
{
    public static function get_data($atts)
    {
        // Logic to retrieve data
        if (is_multisite()) {
            $global_blog_id = get_site_option('msa_tool_global_data', null);

            if ($global_blog_id) {
                switch_to_blog($global_blog_id);
                $data = MSA_Tool_Database::get_grouped_data(); // Retrieve grouped data
                restore_current_blog();
            } else {
                $data = MSA_Tool_Database::get_grouped_data(); // Retrieve grouped data
            }
        } else {
            $data = MSA_Tool_Database::get_grouped_data(); // Retrieve grouped data
        }

        return $data;
    }

    public static function get_map_data()
    {
        // Logic to retrieve map data
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
