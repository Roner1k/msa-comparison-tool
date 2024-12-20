<?php

require_once plugin_dir_path(__FILE__) . '../vendor/tecnickcom/tcpdf/tcpdf.php';

class MSA_Tool_PDF_Export
{

    /**
     * Генерация PDF и сохранение в директорию экспорта.
     *
     * @param string $content Содержимое PDF.
     * @return string|false URL файла или false в случае ошибки.
     */
    public static function generate_pdf($categories)
    {
        error_log("[PDF EXPORT] Starting PDF generation...");

        try {
            $pdf = new TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('MSA Tool');
            $pdf->SetTitle('Orlando MSA Comparison Export');
            $pdf->SetSubject('Export Example');
            $pdf->SetKeywords('PDF, Export, MSA');

            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 20);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);

            foreach ($categories as $category) {
                // Заголовок категории
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->Cell(0, 10, $category['name'], 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 12);

                $html = '<table border="1" cellpadding="4" cellspacing="0">';

                // Выводим заголовки таблицы (thead)
                if (!empty($category['headers'])) {
                    foreach ($category['headers'] as $headerRow) {
                        $html .= '<tr>';
                        foreach ($headerRow as $cell) {
                            $html .= '<th>' . htmlspecialchars($cell) . '</th>';
                        }
                        $html .= '</tr>';
                    }
                }

                // Выводим строки таблицы (tbody)
                if (!empty($category['rows'])) {
                    foreach ($category['rows'] as $row) {
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                }

                $html .= '</table>';

                $pdf->writeHTML($html, true, false, true, false, '');
            }

            $timestamp = time();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';
            $base_url = $upload_dir['baseurl'] . '/msa-tool/exports';
            $dynamic_filename = "Orlando-MSA-Comparison-{$timestamp}.pdf";
            $output_path = "{$base_dir}/{$dynamic_filename}";
            $file_url = "{$base_url}/{$dynamic_filename}";

            // Создаем директорию если она не существует
            if (!file_exists($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            $pdf->Output($output_path, 'F');
            error_log("[PDF EXPORT] PDF saved at: {$output_path}");

            return $file_url;

        } catch (Exception $e) {
            error_log("[PDF EXPORT ERROR] " . $e->getMessage());
            return null;
        }
    }

}
