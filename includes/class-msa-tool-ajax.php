<?php
/*
class MSA_Tool_Ajax {

    public static function init_hooks() {
        // Регистрируем обработчики только для фронтенда
        add_action('wp_ajax_export_pdf', [__CLASS__, 'export_pdf']); // Для авторизованных пользователей
        add_action('wp_ajax_nopriv_export_pdf', [__CLASS__, 'export_pdf']); // Для неавторизованных пользователей
    }

    public static function export_pdf()
    {
        error_log("[AJAX EXPORT] Starting export process...");

        self::include_pdf_library();
        self::clean_up_exports();

        $categories = isset($_POST['categories']) ? json_decode(stripslashes($_POST['categories']), true) : [];
        error_log("[AJAX EXPORT] Received categories: " . print_r($categories, true));

        if (empty($categories)) {
            error_log("[AJAX EXPORT] No categories received.");
            wp_send_json_error(['message' => 'No categories provided.']);
            wp_die();
        }

        $file_url = MSA_Tool_Export::generate_pdf($categories);

        if ($file_url) {
            wp_send_json_success(['message' => 'PDF generated successfully!', 'file' => $file_url]);
        } else {
            wp_send_json_error(['message' => 'Error generating PDF.']);
        }

        wp_die();
    }




    private static function include_pdf_library() {
        $pdf_export_class = plugin_dir_path(__FILE__) . 'class-msa-tool-export.php';

        if (!class_exists('TCPDF')) {
            require_once plugin_dir_path(__FILE__) . '../vendor/tecnickcom/tcpdf/tcpdf.php';
            error_log("[AJAX EXPORT] TCPDF library loaded.");
        }

        if (!class_exists('MSA_Tool_Export')) {
            require_once $pdf_export_class;
            error_log("[AJAX EXPORT] MSA_Tool_Export class loaded.");
        }
    }


    private static function clean_up_exports() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';

        if (!is_dir($base_dir)) {
            return; // Если директория не существует, ничего не делаем
        }

        $files = glob($base_dir . '/*.pdf');

        if (count($files) > 30) {
            // Сортируем файлы по времени создания (старые файлы первыми)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Удаляем старые файлы, пока их количество не будет равно 30
            while (count($files) > 30) {
                $file_to_delete = array_shift($files);
                if (file_exists($file_to_delete)) {
                    unlink($file_to_delete);
                    error_log("[AJAX EXPORT] Deleted old file: {$file_to_delete}");
                }
            }
        }
    }
}
*/

class MSA_Tool_Ajax
{

    public static function init_hooks()
    {
        // Регистрируем обработчики только для фронтенда
        add_action('wp_ajax_export_pdf', [__CLASS__, 'export_pdf']); // Для авторизованных пользователей
        add_action('wp_ajax_nopriv_export_pdf', [__CLASS__, 'export_pdf']); // Для неавторизованных пользователей

        add_action('wp_ajax_export_excel', [__CLASS__, 'export_excel']); // Для авторизованных пользователей
        add_action('wp_ajax_nopriv_export_excel', [__CLASS__, 'export_excel']); // Для неавторизованных пользователей
    }

    public static function export_pdf()
    {
        error_log("[AJAX EXPORT PDF] Starting export process...");

        self::include_pdf_library();
        self::clean_up_exports('pdf');

        $categories = isset($_POST['categories']) ? json_decode(stripslashes($_POST['categories']), true) : [];
//        error_log("[AJAX EXPORT PDF] Received categories: " . print_r($categories, true));

        if (empty($categories)) {
            error_log("[AJAX EXPORT PDF] No categories received.");
            wp_send_json_error(['message' => 'No categories provided.']);
            wp_die();
        }

        $file_url = MSA_Tool_Export::generate_pdf($categories);

        if ($file_url) {
            wp_send_json_success(['message' => 'PDF generated successfully!', 'file' => $file_url]);
        } else {
            wp_send_json_error(['message' => 'Error generating PDF.']);
        }

        wp_die();
    }

    public static function export_excel()
    {
        error_log("[AJAX EXPORT EXCEL] Starting export process...");

        self::include_excel_library();
        self::clean_up_exports('xlsx');

        $categories = isset($_POST['categories']) ? json_decode(stripslashes($_POST['categories']), true) : [];
//        error_log("[AJAX EXPORT EXCEL] Received categories: " . print_r($categories, true));

        if (empty($categories)) {
            error_log("[AJAX EXPORT EXCEL] No categories received.");
            wp_send_json_error(['message' => 'No categories provided.']);
            wp_die();
        }

        $file_url = MSA_Tool_Export::generate_excel($categories);

        if ($file_url) {
            wp_send_json_success(['message' => 'Excel generated successfully!', 'file' => $file_url]);
        } else {
            wp_send_json_error(['message' => 'Error generating Excel.']);
        }

        wp_die();
    }

    /**
     * Подключает библиотеку TCPDF и класс MSA_Tool_Export.
     */
    private static function include_pdf_library()
    {
        $export_class = plugin_dir_path(__FILE__) . 'class-msa-tool-export.php';

        if (!class_exists('TCPDF')) {
            require_once plugin_dir_path(__FILE__) . '../vendor/tecnickcom/tcpdf/tcpdf.php';
            error_log("[AJAX EXPORT PDF] TCPDF library loaded.");
        }

        if (!class_exists('MSA_Tool_Export')) {
            require_once $export_class;
            error_log("[AJAX EXPORT PDF] MSA_Tool_Export class loaded.");
        }
    }

    /**
     * Подключает библиотеку PhpSpreadsheet и класс MSA_Tool_Export.
     */
    private static function include_excel_library()
    {
        $export_class = plugin_dir_path(__FILE__) . 'class-msa-tool-export.php';

        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
            error_log("[AJAX EXPORT EXCEL] PhpSpreadsheet library loaded.");
        }

        if (!class_exists('MSA_Tool_Export')) {
            require_once $export_class;
            error_log("[AJAX EXPORT EXCEL] MSA_Tool_Export class loaded.");
        }
    }

    /**
     * Очищает директорию экспорта, оставляя только последние 30 файлов.
     * @param string $extension Расширение файлов для удаления (например, pdf или xlsx).
     */
    private static function clean_up_exports($extension)
    {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';

        if (!is_dir($base_dir)) {
            return; // Если директория не существует, ничего не делаем
        }

        $files = glob($base_dir . "/*.{$extension}");

        if (count($files) > 30) {
            // Сортируем файлы по времени создания (старые файлы первыми)
            usort($files, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Удаляем старые файлы, пока их количество не будет равно 30
            while (count($files) > 30) {
                $file_to_delete = array_shift($files);
                if (file_exists($file_to_delete)) {
                    unlink($file_to_delete);
                    error_log("[AJAX EXPORT] Deleted old file: {$file_to_delete}");
                }
            }
        }
    }
}
