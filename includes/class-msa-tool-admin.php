<?php

class MSA_Tool_Admin
{
    public static function init()
    {
        add_action('admin_menu', [self::class, 'add_menu_pages']);
        add_action('admin_notices', [self::class, 'display_admin_notices']);

    }


    public static function render_settings_page()
    {
        // Обработка импорта файла
        if (isset($_POST['msa_tool_import_submit'])) {
            MSA_Tool_Import::handle_file_import();
        }

        // Обработка общей формы настроек (ArcGIS, Global Mode и новое поле)
        if (isset($_POST['msa_tool_settings_submit']) && check_admin_referer('msa_tool_settings', 'msa_tool_settings_nonce')) {
            // Обработка опции ArcGIS
            $disable_arcgis = isset($_POST['msa_tool_disable_arcgis']) ? 1 : 0;
            update_option('msa_tool_disable_arcgis', $disable_arcgis);

            // Обработка опции Global Data Mode, только если мультисайт
            if (is_multisite()) {
                $global_data = isset($_POST['msa_tool_global_data']) ? (int)get_current_blog_id() : null;

                if ($global_data) {
                    update_site_option('msa_tool_global_data', $global_data);
                } else {
                    delete_site_option('msa_tool_global_data');
                }
            }

            // Сохранение текстового поля для экспорта
            if (isset($_POST['msa_tool_export_info'])) {
                update_option('msa_tool_export_info', wp_kses_post($_POST['msa_tool_export_info']));
            }

            // Добавляем сообщение об успешном сохранении настроек
            add_settings_error('msa_tool_messages', 'msa_tool_success', 'Settings updated successfully.', 'success');
        }

        // Проверяем, активен ли глобальный режим
        if (self::render_global_mode_message()) {
            return;
        }

        // Подключаем шаблон страницы настроек
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-settings.php';
    }


    public static function render_results_page()
    {
        global $wpdb;

        // Проверяем наличие параметра удаления
        if (isset($_GET['delete_id']) && isset($_GET['_wpnonce'])) {
            $delete_id = intval($_GET['delete_id']);
            $nonce = sanitize_text_field($_GET['_wpnonce']);

            if (wp_verify_nonce($nonce, 'msa_tool_delete_nonce_' . $delete_id)) {
                $table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';
                $deleted = $wpdb->delete($table_name, ['id' => $delete_id], ['%d']);

                if ($deleted) {
                    // Добавляем параметр успеха удаления в URL
                    wp_redirect(admin_url('admin.php?page=msa-tool-results&delete_success=1'));
                    exit;
                } else {
                    self::show_notification('Error deleting entry: ' . $wpdb->last_error, 'error');
                }
            } else {
                self::show_notification('Invalid request. Deletion not authorized.', 'error');
            }
        }

        // Проверяем параметр успеха удаления
        if (isset($_GET['delete_success'])) {
            self::show_notification('Entry deleted successfully!', 'success');
        }

        // Отображаем уведомления
        settings_errors('msa_tool_messages');

        // Проверяем, активен ли глобальный режим
        if (self::render_global_mode_message()) {
            return;
        }

        // Подключаем шаблон
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-results.php';
    }


    public static function render_edit_page()
    {
        global $wpdb;

        // Определяем, с какой таблицей работаем
        $is_map_row = isset($_GET['edit-map-row']);
        $table_name = $is_map_row
            ? $wpdb->get_blog_prefix() . 'msa_tool_map_keys'
            : $wpdb->get_blog_prefix() . 'msa_tool_data';

        // Проверяем наличие ID
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            wp_die('Invalid entry ID.');
        }

        // Обработка сохранения изменений
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['msa_tool_save_changes'])
            && check_admin_referer('msa_tool_edit', 'msa_tool_edit_nonce')
        ) {
            if ($is_map_row) {
                // Обновление для таблицы map_keys
                $data = [
                    'region_slug' => sanitize_title($_POST['region_slug']),
                    'map_id' => sanitize_text_field($_POST['map_id']),
                ];
            } else {
                // Обновление для таблицы msa_tool_data с учетом subcategory
                $data = [
                    'region' => sanitize_text_field($_POST['region']),
                    'slug' => sanitize_title($_POST['slug']),
                    'category' => sanitize_text_field($_POST['category']),
                    'subcategory' => isset($_POST['subcategory']) ? sanitize_text_field($_POST['subcategory']) : null,
                    'indicator' => sanitize_text_field($_POST['indicator']),
                    'value' => sanitize_text_field($_POST['value']),
                ];
            }

            $updated = $wpdb->update($table_name, $data, ['id' => $id], array_fill(0, count($data), '%s'), ['%d']);

            if ($updated !== false) {
                // Успешное обновление
                $list_page = $is_map_row ? 'msa-tool-region-mapping' : 'msa-tool-results';
                $list_link = admin_url('admin.php?page=' . $list_page);
                $list_text = '<a href="' . esc_url($list_link) . '">Return to the list</a>';
                self::show_notification('Entry updated successfully! ' . $list_text, 'success');
            } else {
                // Ошибка при обновлении
                self::show_notification('Error updating entry: ' . $wpdb->last_error, 'error');
            }
        }

        // Получаем текущую запись
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        if (!$entry) {
            wp_die('Entry not found.');
        }

        // Отображаем уведомления
        settings_errors('msa_tool_messages');

        // Проверяем, активен ли глобальный режим
        if (self::render_global_mode_message()) {
            return;
        }

        // Подключаем шаблон (в котором уже есть поле subcategory)
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-edit.php';
    }

    public static function render_add_page()
    {
        global $wpdb;

        // Определяем, в какую таблицу добавляем
        $is_map_row = isset($_GET['new-map-row']);
        $table_name = $is_map_row
            ? $wpdb->get_blog_prefix() . 'msa_tool_map_keys'
            : $wpdb->get_blog_prefix() . 'msa_tool_data';

        // Если форма была отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msa_tool_add_nonce_field']) && wp_verify_nonce($_POST['msa_tool_add_nonce_field'], 'msa_tool_add_nonce')) {
            // Формируем данные для вставки
            if ($is_map_row) {
                $data = [
                    'region_slug' => sanitize_title($_POST['region_slug']),
                    'map_id' => sanitize_text_field($_POST['map_id']),
                ];
            } else {
                $data = [
                    'region' => sanitize_text_field($_POST['region']),
                    'slug' => sanitize_title($_POST['slug']),
                    'category' => sanitize_text_field($_POST['category']),
                    'subcategory' => sanitize_text_field($_POST['subcategory']),
                    'indicator' => sanitize_text_field($_POST['indicator']),
                    'value' => sanitize_text_field($_POST['value']),
                ];
            }

            // Вставка данных
            $inserted = $wpdb->insert($table_name, $data, array_fill(0, count($data), '%s'));

            if ($inserted) {
                // Успешное добавление
                $list_page = $is_map_row ? 'msa-tool-region-mapping' : 'msa-tool-results';
                $list_link = admin_url('admin.php?page=' . $list_page);
                $list_text = '<a href="' . esc_url($list_link) . '">View the updated list</a>';
                self::show_notification('New entry added successfully! ' . $list_text, 'success');
            } else {
                // Ошибка при добавлении
                self::show_notification('Error adding new entry: ' . $wpdb->last_error, 'error');
            }
        }

        // Отображаем уведомления
        settings_errors('msa_tool_messages');

        // Проверяем, активен ли глобальный режим
        if (self::render_global_mode_message()) {
            return;
        }

        // Вывод шаблона формы
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-add.php';
    }


    public static function render_region_mapping_page()
    {
        global $wpdb;

        // Проверяем наличие параметра удаления
        if (isset($_GET['delete_id']) && isset($_GET['_wpnonce'])) {
            $delete_id = intval($_GET['delete_id']);
            $nonce = sanitize_text_field($_GET['_wpnonce']);

            if (wp_verify_nonce($nonce, 'msa_tool_delete_map_nonce_' . $delete_id)) {
                $table_name = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';
                $deleted = $wpdb->delete($table_name, ['id' => $delete_id], ['%d']);

                if ($deleted) {
                    // Добавляем параметр успеха удаления в URL
                    wp_redirect(admin_url('admin.php?page=msa-tool-region-mapping&delete_success=1'));
                    exit;
                } else {
                    self::show_notification('Error deleting entry: ' . $wpdb->last_error, 'error');
                }
            } else {
                self::show_notification('Invalid request. Deletion not authorized.', 'error');
            }
        }

        // Проверяем параметр успеха удаления
        if (isset($_GET['delete_success'])) {
            self::show_notification('Mapping entry deleted successfully!', 'success');
        }

        // Отображаем уведомления
        settings_errors('msa_tool_messages');

        // Проверяем, активен ли глобальный режим
        if (self::render_global_mode_message()) {
            return;
        }

        // Подключаем шаблон
        include plugin_dir_path(__FILE__) . '../templates/msa-tool-admin-mapping.php';
    }


    public static function render_global_mode_message(): bool
    {
        if (is_multisite()) {
            $global_blog_id = (int)get_site_option('msa_tool_global_data', 0); // Приводим к int

            if ($global_blog_id && $global_blog_id !== get_current_blog_id()) {
                $global_blog_details = get_blog_details($global_blog_id);
                $global_site_name = $global_blog_details ? $global_blog_details->blogname : 'Global Site';
                $global_site_url = $global_blog_details ? $global_blog_details->siteurl : '';

                echo '<div class="notice notice-warning" style="margin-top: 20px;">
                <p><strong>Global Mode Enabled:</strong> Data is managed globally from the site 
                <a href="' . esc_url($global_site_url) . '/wp-admin/admin.php?page=msa-tool-settings" target="_blank">' . esc_html($global_site_name) . '</a>. 
                Editing or viewing data on this subsite is disabled.</p>
              </div>';
                return true; // Глобальный режим активен на другом сайте
            }
        }

        return false; // Глобальный режим не включён
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
            [__CLASS__, 'render_region_mapping_page']
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