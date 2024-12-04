<?php
class MSA_Tool_Database {

    public static function get_all_data() {
        global $wpdb;
        $table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';

         if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }

        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        return $results ? $results : [];
    }




    public static function insert_data($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'msa_tool_data';
        $wpdb->insert($table_name, $data);
    }



    public static function get_row_by_id($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'msa_tool_data';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }

    public static function update_row($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'msa_tool_data';
        $wpdb->update($table_name, $data, ['id' => $id]);
    }

    public static function delete_row($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'msa_tool_data';
        $wpdb->delete($table_name, ['id' => $id]);
    }
}