<?php


class MSA_Tool_PDF_Export1
{
    /*
        public static function generate_pdf($table_data) {
            // Подключаем TCPDF
            if (!class_exists('TCPDF')) {
                require_once plugin_dir_path(__FILE__) . '../vendor/tecnickcom/tcpdf/tcpdf.php';
            }

            // Логируем данные таблицы
            error_log('Received Table Data: ' . print_r($table_data, true));

            // Создаем новый PDF-документ
            $pdf = new TCPDF();
            error_log('TCPDF object created successfully.');

            // Устанавливаем свойства документа
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('MSA Tool');
            $pdf->SetTitle('Exported Data');
            $pdf->SetSubject('Table Export');
            $pdf->SetKeywords('PDF, Export, MSA');

            // Настройки страницы
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(TRUE, 10);

            $pdf->AddPage();
            error_log('Page added to PDF.');

            $pdf->SetFont('helvetica', '', 12);

            // Генерируем HTML-контент для таблицы
            $html = '<h1>Exported Table</h1>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0">';
            $html .= '<thead><tr>';
            foreach ($table_data['headers'] as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($table_data['rows'] as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            error_log('Generated HTML for PDF: ' . $html);

            // Добавляем HTML-контент в PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            error_log('Preparing to output PDF.');

            // Проверяем, были ли отправлены заголовки
            if (headers_sent()) {
                error_log('Headers already sent before PDF output.');
                return;
            }

            // Очищаем буферы вывода
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Отправляем файл клиенту
            $pdf->Output('exported_table.pdf', 'D'); // 'D' — загрузка файла
            exit;
        }
    */
}

class MSA_Tool_PDF_Export
{
    public static function generate_pdf($table_data)
    {
        error_log("[PDF EXPORT] Starting PDF generation...");

        // Подключаем TCPDF
        if (!class_exists('TCPDF')) {
            require_once plugin_dir_path(__FILE__) . '../vendor/tecnickcom/tcpdf/tcpdf.php';
            error_log("[PDF EXPORT] TCPDF class loaded.");
        }

        try {
            // Создаем новый PDF-документ
            $pdf = new TCPDF();
            error_log("[PDF EXPORT] TCPDF object created.");

            // Устанавливаем свойства документа
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('MSA Tool');
            $pdf->SetTitle('Exported Data');
            $pdf->SetSubject('Table Export');
            $pdf->SetKeywords('PDF, Export, MSA');
            error_log("[PDF EXPORT] PDF properties set.");

            // Устанавливаем отступы
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(TRUE, 10);

            // Добавляем страницу
            $pdf->AddPage();
            error_log("[PDF EXPORT] Page added.");

            // Устанавливаем шрифт
            $pdf->SetFont('helvetica', '', 12);
            error_log("[PDF EXPORT] Font set.");

            // Генерируем HTML-контент для таблицы
            $html = '<h1>Exported Table</h1>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0">';
            $html .= '<thead><tr>';
            foreach ($table_data['headers'] as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($table_data['rows'] as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            error_log("[PDF EXPORT] HTML content generated: " . $html);

            // Добавляем HTML-контент в PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            error_log("[PDF EXPORT] HTML content written to PDF.");

            // Сохраняем PDF на сервере
            $output_path = plugin_dir_path(__FILE__) . '../exported_table.pdf';
            $pdf->Output($output_path, 'F');
            error_log("[PDF EXPORT] PDF saved to server: " . $output_path);

            // Отправляем сообщение об успешном завершении
            wp_send_json_success(['message' => 'PDF generated successfully!', 'file' => $output_path]);

        } catch (Exception $e) {
            error_log("[PDF EXPORT] Error occurred: " . $e->getMessage());
            wp_send_json_error(['message' => 'Error generating PDF: ' . $e->getMessage()]);
        }
    }
}






