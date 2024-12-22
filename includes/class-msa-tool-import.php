<?php

class MSA_Tool_Import
{
    public static function handle_file_import()
    {
        require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        if (!isset($_FILES['msa_tool_import_file']['tmp_name']) || empty($_FILES['msa_tool_import_file']['tmp_name'])) {
            add_settings_error('msa_tool_messages', 'msa_tool_error', 'No file uploaded.', 'error');
            return;
        }

        $uploaded_file = $_FILES['msa_tool_import_file']['tmp_name'];
        $file_name = $_FILES['msa_tool_import_file']['name'];

        $upload_dir = wp_upload_dir();
        $target_dir = trailingslashit($upload_dir['basedir']) . 'msa-imports/';

        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }

        $target_file = $target_dir . 'last_import.xlsx';

        if (file_exists($target_file)) {
            unlink($target_file);
        }

        if (!move_uploaded_file($uploaded_file, $target_file)) {
            add_settings_error('msa_tool_messages', 'msa_tool_error', 'Failed to save the uploaded file.', 'error');
            return;
        }

        $log_entry = [
            'date' => current_time('mysql'),
            'file_name' => $file_name,
            'records_imported' => 0,
            'map_records_created' => 0,
            'info' => null,
        ];

        try {
            // Обработка файла
            $result = self::process_file($target_file);
            $log_entry['records_imported'] = $result['data_count'];
            $log_entry['map_records_created'] = $result['map_count'];
            $log_entry['info'] = 'Success: Import completed successfully.';
            add_settings_error('msa_tool_messages', 'msa_tool_success', 'File processed and saved successfully!', 'success');
        } catch (Exception $e) {
            $log_entry['info'] = 'Error: ' . $e->getMessage();
            add_settings_error('msa_tool_messages', 'msa_tool_error', 'Error during import: ' . $e->getMessage(), 'error');
        }

        // Обновляем логи
        $import_logs = get_option('msa_tool_import_logs', []);
        array_unshift($import_logs, $log_entry); // Добавляем новый импорт в начало
        $import_logs = array_slice($import_logs, 0, 10); // Оставляем только 10 последних записей
        update_option('msa_tool_import_logs', $import_logs);
    }


    public static function process_file($uploaded_file)
    {
        global $wpdb;
        $data_table = $wpdb->get_blog_prefix() . 'msa_tool_data';
        $map_table = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

        $imported_count = 0;
        $map_created_count = 0;

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploaded_file);
            $sheet = $spreadsheet->getSheet(0);
            $rows = $sheet->toArray();

            if (empty($rows)) {
                throw new Exception('The uploaded file is empty.');
            }

            // Проверяем обязательные заголовки
            $header = $rows[0];
            $required_headers = ['Category', 'Indicator'];
            foreach ($required_headers as $required) {
                if (!in_array($required, $header)) {
                    throw new Exception("Missing required header: $required");
                }
            }

            // Индексы обязательных колонок
            $category_index = array_search('Category', $header);
            $indicator_index = array_search('Indicator', $header);

            // Проверяем, есть ли подкатегория
            $subcategory_index = array_search('Subcategory', $header);
            // Если колонка Subcategory не найдена, $subcategory_index будет false

            // Очистка таблицы данных
            $wpdb->query("TRUNCATE TABLE $data_table");

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Пропускаем заголовок

                $category = $row[$category_index] ?? null;
                $indicator = $row[$indicator_index] ?? null;

                if (empty($category) || empty($indicator)) {
                    continue; // Пропускаем строки без категории или индикатора
                }

                // Получаем подкатегорию, если она есть
                $subcategory = null;
                if ($subcategory_index !== false && !empty($row[$subcategory_index])) {
                    $subcategory = $row[$subcategory_index];
                }

                // Перебираем остальные колонки
                for ($i = 0; $i < count($row); $i++) {
                    // Пропускаем обязательные колонки Category, Indicator и (опционально) Subcategory
                    if ($i === $category_index || $i === $indicator_index || ($subcategory_index !== false && $i === $subcategory_index)) {
                        continue;
                    }

                    $value = $row[$i];
                    $region_name = $header[$i] ?? null;

                    if (empty($region_name) || empty($value)) {
                        continue; // Пропускаем колонки без заголовков или значений
                    }

                    $slug = self::generate_slug($region_name);

                    // Добавляем данные в таблицу с учетом subcategory
                    $wpdb->insert(
                        $data_table,
                        [
                            'category' => $category,
                            'subcategory' => $subcategory, // Добавляем подкатегорию
                            'indicator' => $indicator,
                            'region' => $region_name,
                            'slug' => $slug,
                            'value' => $value,
                        ],
                        ['%s', '%s', '%s', '%s', '%s', '%s']
                    );

                    if ($wpdb->last_error) {
                        throw new Exception('Database error in data table: ' . $wpdb->last_error);
                    }

                    $imported_count++;

                    // Добавляем slug в таблицу ключей карты, если он уникален
                    $existing_slug = $wpdb->get_var(
                        $wpdb->prepare("SELECT region_slug FROM $map_table WHERE region_slug = %s", $slug)
                    );

                    if (!$existing_slug) {
                        $wpdb->insert(
                            $map_table,
                            [
                                'region_slug' => $slug,
                                'map_id' => null,
                            ],
                            ['%s', '%s']
                        );

                        if ($wpdb->last_error) {
                            throw new Exception('Database error in map keys table: ' . $wpdb->last_error);
                        }

                        $map_created_count++;
                    }
                }
            }

            return [
                'data_count' => $imported_count,
                'map_count' => $map_created_count,
            ];
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new Exception('Error reading the file: ' . $e->getMessage());
        }
    }


    private static function log_import($file_name, $data_count, $map_count, $info)
    {
        $logs = get_option('msa_tool_import_logs', []);

        $logs[] = [
            'date' => current_time('mysql'),
            'file_name' => $file_name,
            'records_imported' => $data_count,
            'map_records_created' => $map_count,
            'info' => $info, // Было исправлено на 'info'
        ];

        // Сохраняем только последние 10 записей
        $logs = array_slice($logs, -10);

        update_option('msa_tool_import_logs', $logs);
    }


    public static function generate_slug($region)
    {
        return sanitize_title($region);
    }
}

/*
class MSA_Tool_Import
{
    public static function handle_file_import()
    {
        require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        if (!isset($_FILES['msa_tool_import_file']['tmp_name']) || empty($_FILES['msa_tool_import_file']['tmp_name'])) {
            add_settings_error('msa_tool_messages', 'msa_tool_error', 'No file uploaded.', 'error');
            return;
        }

        $uploaded_file = $_FILES['msa_tool_import_file']['tmp_name'];
        $file_name = $_FILES['msa_tool_import_file']['name'];

        $upload_dir = wp_upload_dir();
        $target_dir = trailingslashit($upload_dir['basedir']) . 'msa-imports/';

        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }

        $target_file = $target_dir . 'last_import.xlsx';

        if (file_exists($target_file)) {
            unlink($target_file);
        }

        if (!move_uploaded_file($uploaded_file, $target_file)) {
            add_settings_error('msa_tool_messages', 'msa_tool_error', 'Failed to save the uploaded file.', 'error');
            return;
        }

        $log_entry = [
            'date' => current_time('mysql'),
            'file_name' => $file_name,
            'records_imported' => 0,
            'map_records_created' => 0,
            'info' => null,
        ];

        try {
            // Обработка файла
            $result = self::process_file($target_file);
            $log_entry['records_imported'] = $result['data_count'];
            $log_entry['map_records_created'] = $result['map_count'];
            $log_entry['info'] = 'Success: Import completed successfully.';
            add_settings_error('msa_tool_messages', 'msa_tool_success', 'File processed and saved successfully!', 'success');
        } catch (Exception $e) {
            $log_entry['info'] = 'Error: ' . $e->getMessage();
            add_settings_error('msa_tool_messages', 'msa_tool_error', 'Error during import: ' . $e->getMessage(), 'error');
        }

        // Обновляем логи
        $import_logs = get_option('msa_tool_import_logs', []);
        array_unshift($import_logs, $log_entry); // Добавляем новый импорт в начало
        $import_logs = array_slice($import_logs, 0, 10); // Оставляем только 10 последних записей
        update_option('msa_tool_import_logs', $import_logs);
    }



    public static function process_file($uploaded_file)
    {
        global $wpdb;
        $data_table = $wpdb->get_blog_prefix() . 'msa_tool_data';
        $map_table = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

        $imported_count = 0;
        $map_created_count = 0;

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploaded_file);
            $sheet = $spreadsheet->getSheet(0);
            $rows = $sheet->toArray();

            if (empty($rows)) {
                throw new Exception('The uploaded file is empty.');
            }

            // Проверяем обязательные заголовки
            $header = $rows[0];
            $required_headers = ['Category', 'Indicator'];
            foreach ($required_headers as $required) {
                if (!in_array($required, $header)) {
                    throw new Exception("Missing required header: $required");
                }
            }

            // Индексы обязательных колонок
            $category_index = array_search('Category', $header);
            $indicator_index = array_search('Indicator', $header);

            // Очистка таблицы данных
            $wpdb->query("TRUNCATE TABLE $data_table");

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Пропускаем заголовок

                $category = $row[$category_index] ?? null;
                $indicator = $row[$indicator_index] ?? null;

                if (empty($category) || empty($indicator)) {
                    continue; // Пропускаем строки без категории или индикатора
                }

                foreach ($row as $i => $value) {
                    if ($i === $category_index || $i === $indicator_index) continue; // Пропускаем обязательные колонки

                    $region_name = $header[$i] ?? null;
                    if (empty($region_name) || empty($value)) {
                        continue; // Пропускаем колонки без заголовков или значений
                    }

                    $slug = self::generate_slug($region_name);

                    // Добавляем данные в таблицу
                    $wpdb->insert(
                        $data_table,
                        [
                            'category' => $category,
                            'indicator' => $indicator,
                            'region' => $region_name,
                            'slug' => $slug,
                            'value' => $value,
                        ],
                        ['%s', '%s', '%s', '%s', '%s']
                    );

                    if ($wpdb->last_error) {
                        throw new Exception('Database error in data table: ' . $wpdb->last_error);
                    }

                    $imported_count++;

                    // Добавляем slug в таблицу ключей карты, если он уникален
                    $existing_slug = $wpdb->get_var(
                        $wpdb->prepare("SELECT region_slug FROM $map_table WHERE region_slug = %s", $slug)
                    );

                    if (!$existing_slug) {
                        $wpdb->insert(
                            $map_table,
                            [
                                'region_slug' => $slug,
                                'map_id' => null,
                            ],
                            ['%s', '%s']
                        );

                        if ($wpdb->last_error) {
                            throw new Exception('Database error in map keys table: ' . $wpdb->last_error);
                        }

                        $map_created_count++;
                    }
                }
            }

            return [
                'data_count' => $imported_count,
                'map_count' => $map_created_count,
            ];
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new Exception('Error reading the file: ' . $e->getMessage());
        }
    }


    private static function log_import($file_name, $data_count, $map_count, $info)
    {
        $logs = get_option('msa_tool_import_logs', []);

        $logs[] = [
            'date' => current_time('mysql'),
            'file_name' => $file_name,
            'records_imported' => $data_count,
            'map_records_created' => $map_count,
            'info' => $info, // Было исправлено на 'info'
        ];

        // Сохраняем только последние 10 записей
        $logs = array_slice($logs, -10);

        update_option('msa_tool_import_logs', $logs);
    }



    public static function generate_slug($region)
    {
        return sanitize_title($region);
    }
}
*/
