<?php

class MSA_Custom_TCPDF extends TCPDF
{
    public function Header()
    {
        // Путь к логотипу
        $logo_path = plugin_dir_path(__FILE__) . '../assets/img/orlandoedc_logo.png';

        // Исходные размеры логотипа
        $original_width = 333;
        $original_height = 108;

        // Новая ширина логотипа (в мм)
        $new_width = 50;

        // Рассчитываем пропорциональную высоту
        $new_height = ($original_height / $original_width) * $new_width;

        // Логотип
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, $new_width, $new_height); // Позиция и пропорциональные размеры
        }

        // Текст заголовка
        $this->SetFont('helvetica', 'B', 22); // Размер текста
        $this->SetTextColor(244, 123, 32); // Оранжевый цвет текста (#F47B20)
        $this->SetXY(10 + $new_width + 20, 10 + ($new_height / 2) - 5); // Текст выровнен по центру логотипа
        $this->Cell(0, 0, 'HOW ORLANDO COMPARES?', 0, 0, 'L');
    }
}

if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class MSA_Tool_Export
{

    /**
     * Генерация PDF и сохранение в директорию экспорта.
     *
     * @param array $categories Содержимое PDF.
     * @return string|null URL файла или null в случае ошибки.
     */
    public static function generate_pdf($categories)
    {
        try {
            $pdf = new MSA_Custom_TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('MSA Tool');
            $pdf->SetTitle('Orlando MSA Comparison Export');
            $pdf->SetSubject('Export Example');
            $pdf->SetKeywords('PDF, Export, MSA');

            $pdf->SetMargins(15, 40, 15);
            $pdf->SetAutoPageBreak(TRUE, 20);
            $pdf->AddPage();

            // Генерация содержимого PDF (таблицы)
            foreach ($categories as $category) {
                // Заголовок категории
                $pdf->SetFont('helvetica', 'B', 14); // Уменьшен размер шрифта
                $pdf->SetTextColor(244, 123, 32); // Цвет заголовка категории
                $pdf->Cell(0, 10, $category['name'], 0, 1, 'L');
                $pdf->Ln(5);

                // Стилизация таблицы
                $html = '<table cellpadding="4" cellspacing="0" style="border-collapse: collapse;">';

                // Генерация строк заголовков
                if (!empty($category['headers'])) {
                    foreach ($category['headers'] as $headerRow) {
                        $html .= '<tr>';
                        foreach ($headerRow as $cell) {
                            $html .= '<th style="font-size: 11px; font-weight: bold; color: rgb(244, 123, 32); text-align: center;">' . htmlspecialchars($cell) . '</th>';
                        }
                        $html .= '</tr>';
                    }
                }

                // Генерация строк данных
                if (!empty($category['rows'])) {
                    foreach ($category['rows'] as $rowIndex => $row) {
                        // Задаем цвет строки (парные строки с серым фоном)
                        $rowColor = ($rowIndex % 2 === 0) ? 'background-color: #F5F5F5;' : 'background-color: #FFFFFF;';
                        $html .= '<tr style="' . $rowColor . '">';

                        foreach ($row as $colIndex => $cell) {
                            // Первая колонка (жирный шрифт)
                            if ($colIndex === 0) {
                                $html .= '<td style="font-size: 9px; font-weight: bold; color: black; text-align: left; padding: 4px;">'
                                    . htmlspecialchars($cell) . '</td>';
                            }
                            // Вторая колонка (оранжевый фон и белый текст)
                            elseif ($colIndex === 1) {
                                $html .= '<td style="font-size: 9px; background-color: rgb(244, 123, 32); color: white; font-weight: normal; text-align: right; padding: 4px;">'
                                    . htmlspecialchars($cell) . '</td>';
                            }
                            // Остальные колонки
                            else {
                                $html .= '<td style="font-size: 9px; font-weight: normal; color: black; text-align: left; padding: 4px;">'
                                    . htmlspecialchars($cell) . '</td>';
                            }
                        }
                        $html .= '</tr>';
                    }
                }

                $html .= '</table>';

                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->Ln(10);
            }

            // Получаем дополнительный текст
            $additional_info = '';
            if (is_multisite()) {
                $global_blog_id = get_site_option('msa_tool_global_data', null);
                if ($global_blog_id) {
                    switch_to_blog($global_blog_id);
                    $additional_info = get_option('msa_tool_export_info', '');
                    restore_current_blog();
                } else {
                    $additional_info = get_option('msa_tool_export_info', '');
                }
            } else {
                $additional_info = get_option('msa_tool_export_info', '');
            }

             // Если текст есть, добавляем его в PDF
            if (!empty($additional_info)) {
                $pdf->AddPage(); // Добавляем новую страницу для текста (если требуется)
                $pdf->SetFont('helvetica', '', 12); // Шрифт Helvetica, обычный стиль
                $pdf->SetTextColor(0, 0, 0); // Черный цвет текста
                $pdf->writeHTML($additional_info, true, false, true, false, '');
            }


            // Сохранение PDF
            $timestamp = time();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';
            $base_url = $upload_dir['baseurl'] . '/msa-tool/exports';
            $dynamic_filename = "Orlando-MSA-Comparison-{$timestamp}.pdf";
            $output_path = "{$base_dir}/{$dynamic_filename}";
            $file_url = "{$base_url}/{$dynamic_filename}";

            if (!file_exists($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            $pdf->Output($output_path, 'F');
            return $file_url;

        } catch (Exception $e) {
            error_log("[PDF EXPORT ERROR] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Генерация Excel и сохранение в директорию экспорта.
     *
     * @param array $categories Содержимое Excel.
     * @return string|null URL файла или null в случае ошибки.
     */
    public static function generate_excel($categories)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Устанавливаем заголовок
            $sheet->setTitle('MSA Comparison');

            // Устанавливаем начальные координаты
            $currentRow = 1;

            // Генерация данных
            foreach ($categories as $category) {
                // Заголовок категории
                $sheet->setCellValue("A{$currentRow}", strtoupper($category['name']));
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(14);
                $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                $currentRow++;

                // Заголовки таблицы
                if (!empty($category['headers'])) {
                    foreach ($category['headers'] as $headerRow) {
                        $col = 'A';
                        foreach ($headerRow as $header) {
                            $sheet->setCellValue("{$col}{$currentRow}", $header);
                            $sheet->getStyle("{$col}{$currentRow}")->getFont()->setBold(true);
                            $col++;
                        }
                        $currentRow++;
                    }
                }

                // Данные таблицы
                if (!empty($category['rows'])) {
                    foreach ($category['rows'] as $rowIndex => $row) {
                        $col = 'A';
                        foreach ($row as $cell) {
                            $sheet->setCellValue("{$col}{$currentRow}", $cell);

                            // Чередуем цвет фона строк
                            if ($rowIndex % 2 === 0) {
                                $sheet->getStyle("{$col}{$currentRow}")
                                    ->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('F5F5F5');
                            }

                            $col++;
                        }
                        $currentRow++;
                    }
                }

                $currentRow++; // Пропускаем строку перед новой категорией
            }

            // Получаем дополнительный текст
            $additional_info = '';
            if (is_multisite()) {
                $global_blog_id = get_site_option('msa_tool_global_data', null);
                if ($global_blog_id) {
                    switch_to_blog($global_blog_id);
                    $additional_info = get_option('msa_tool_export_info', '');
                    restore_current_blog();
                } else {
                    $additional_info = get_option('msa_tool_export_info', '');
                }
            } else {
                $additional_info = get_option('msa_tool_export_info', '');
            }

            // Если текст есть, добавляем его в конец файла
            if (!empty($additional_info)) {
                $sheet->setCellValue("A{$currentRow}", 'Additional Information:');
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(12);
                $currentRow++;
                $sheet->setCellValue("A{$currentRow}", strip_tags($additional_info));
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setWrapText(true);
            }

            // Сохранение файла
            $timestamp = time();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';
            $base_url = $upload_dir['baseurl'] . '/msa-tool/exports';
            $dynamic_filename = "Orlando-MSA-Comparison-{$timestamp}.xlsx";
            $output_path = "{$base_dir}/{$dynamic_filename}";
            $file_url = "{$base_url}/{$dynamic_filename}";

            if (!file_exists($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($output_path);

            return $file_url;

        } catch (Exception $e) {
            error_log("[EXCEL EXPORT ERROR] " . $e->getMessage());
            return null;
        }
    }



}
