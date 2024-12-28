<?php

class MSA_Tool_Admin
{
    public static function init()
    {
        // Initialize hooks
        add_action('admin_menu', [self::class, 'add_menu_pages']);
        add_action('admin_notices', [self::class, 'display_admin_notices']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_styles']);
    }

    public static function enqueue_admin_styles()
    {
        // Enqueue admin styles for MSA Tool pages
        $screen = get_current_screen();
        if (strpos($screen->id, 'msa-tool') !== false) {
            wp_enqueue_style(
                'msa-tool-admin-styles',
                plugin_dir_url(__FILE__) . '../assets/css/msa-tool-admin.css',
                [],
                '1.0.0'
            );
        }
    }

    public static function render_settings_page()
    {
        // Handle file import
        if (isset($_POST['msa_tool_import_submit'])) {
            MSA_Tool_Import::handle_file_import();
        }

        // Handle general settings
        if (isset($_POST['msa_tool_settings_submit']) && check_admin_referer('msa_tool_settings', 'msa_tool_settings_nonce')) {
            update_option('msa_tool_disable_arcgis', isset($_POST['msa_tool_disable_arcgis']) ? 1 : 0);

            // Handle Global Data Mode for multisite
            if (is_multisite()) {
                $global_data = isset($_POST['msa_tool_global_data']) ? (int)get_current_blog_id() : null;
                if ($global_data) {
                    update_site_option('msa_tool_global_data', $global_data);
                } else {
                    delete_site_option('msa_tool_global_data');
                }
            }

            // Save export info text
            if (isset($_POST['msa_tool_export_info'])) {
                update_option('msa_tool_export_info', wp_kses_post($_POST['msa_tool_export_info']));
            }

            add_settings_error('msa_tool_messages', 'msa_tool_success', 'Settings updated successfully.', 'success');
        }

        if (self::render_global_mode_message()) {
            return;
        }

        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-settings.php';
    }

    public static function render_results_page()
    {
        global $wpdb;

        // Handle row deletion
        if (isset($_GET['delete_id'], $_GET['_wpnonce'])) {
            $delete_id = intval($_GET['delete_id']);
            $nonce = sanitize_text_field($_GET['_wpnonce']);

            if (wp_verify_nonce($nonce, 'msa_tool_delete_nonce_' . $delete_id)) {
                $table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';
                $deleted = $wpdb->delete($table_name, ['id' => $delete_id], ['%d']);

                if ($deleted) {
                    wp_redirect(admin_url('admin.php?page=msa-tool-results&delete_success=1'));
                    exit;
                } else {
                    self::show_notification('Error deleting entry: ' . $wpdb->last_error, 'error');
                }
            } else {
                self::show_notification('Invalid request. Deletion not authorized.', 'error');
            }
        }

        // Display success message for deletion
        if (isset($_GET['delete_success'])) {
            self::show_notification('Entry deleted successfully!', 'success');
        }

        settings_errors('msa_tool_messages');

        if (self::render_global_mode_message()) {
            return;
        }

        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-results.php';
    }

    public static function render_edit_page()
    {
        global $wpdb;

        $is_map_row = isset($_GET['edit-map-row']);
        $table_name = $is_map_row
            ? $wpdb->get_blog_prefix() . 'msa_tool_map_keys'
            : $wpdb->get_blog_prefix() . 'msa_tool_data';

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            wp_die('Invalid entry ID.');
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['msa_tool_save_changes']) &&
            check_admin_referer('msa_tool_edit', 'msa_tool_edit_nonce')
        ) {
            $data = $is_map_row
                ? [
                    'region_slug' => sanitize_title($_POST['region_slug']),
                    'map_id' => sanitize_text_field($_POST['map_id']),
                ]
                : [
                    'region' => sanitize_text_field($_POST['region']),
                    'slug' => sanitize_title($_POST['slug']),
                    'category' => sanitize_text_field($_POST['category']),
                    'subcategory' => isset($_POST['subcategory']) ? sanitize_text_field($_POST['subcategory']) : null,
                    'indicator' => sanitize_text_field($_POST['indicator']),
                    'value' => sanitize_text_field($_POST['value']),
                ];

            $updated = $wpdb->update($table_name, $data, ['id' => $id], array_fill(0, count($data), '%s'), ['%d']);

            if ($updated !== false) {
                $list_page = $is_map_row ? 'msa-tool-region-mapping' : 'msa-tool-results';
                $list_link = admin_url('admin.php?page=' . $list_page);
                self::show_notification('Entry updated successfully! <a href="' . esc_url($list_link) . '">Return to the list</a>', 'success');
            } else {
                self::show_notification('Error updating entry: ' . $wpdb->last_error, 'error');
            }
        }

        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        if (!$entry) {
            wp_die('Entry not found.');
        }

        settings_errors('msa_tool_messages');

        if (self::render_global_mode_message()) {
            return;
        }

        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-edit.php';
    }

    public static function render_global_mode_message(): bool
    {
        if (is_multisite()) {
            $global_blog_id = (int)get_site_option('msa_tool_global_data', 0);

            if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
                $global_blog_details = get_blog_details($global_blog_id);
                $global_site_name = $global_blog_details ? $global_blog_details->blogname : 'Global Site';
                $global_site_url = $global_blog_details ? $global_blog_details->siteurl : '';

                echo '<div class="notice notice-warning" style="margin-top: 20px;">
                    <p><strong>Global Mode Enabled:</strong> Data is managed globally from the site 
                    <a href="' . esc_url($global_site_url) . '/wp-admin/admin.php?page=msa-tool-settings" target="_blank">' . esc_html($global_site_name) . '</a>. 
                    Editing or viewing data on this subsite is disabled.</p>
                </div>';

                return true;
            }
        }

        return false;
    }

    public static function display_admin_notices()
    {
        $screen = get_current_screen();
        if (strpos($screen->id, 'msa-tool') !== false) {
            settings_errors('msa_tool_messages');
        }
    }

    public static function show_notification($message, $type = 'success')
    {
        $allowed_types = ['success', 'error', 'warning', 'info'];
        if (!in_array($type, $allowed_types)) {
            $type = 'info';
        }

        add_settings_error('msa_tool_messages', 'msa_tool_' . $type, $message, $type);
    }

    public static function add_menu_pages()
    {
        add_menu_page(
            'MSA Tool Settings',
            'MSA Tool',
            'manage_options',
            'msa-tool-settings',
            [self::class, 'render_settings_page']
        );

        add_submenu_page(
            'msa-tool-settings',
            'Imported Data',
            'Imported Data',
            'manage_options',
            'msa-tool-results',
            [self::class, 'render_results_page']
        );

        add_submenu_page(
            'msa-tool-settings',
            'Region Mapping',
            'Region Mapping',
            'manage_options',
            'msa-tool-region-mapping',
            [self::class, 'render_region_mapping_page']
        );

        add_submenu_page(
            null,
            'Edit Data',
            'Edit Data',
            'manage_options',
            'msa-tool-edit',
            [self::class, 'render_edit_page']
        );

        add_submenu_page(
            null,
            'Add New Row',
            'Add Row',
            'manage_options',
            'msa-tool-add',
            [self::class, 'render_add_page']
        );
    }
}
