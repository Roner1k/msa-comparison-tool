<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MSA_Custom_TCPDF extends TCPDF
{
    public function Header()
    {
        // Path to the logo
        $logo_path = plugin_dir_path(__FILE__) . '../assets/img/orlandoedc_logo.png';

        // Original logo dimensions
        $original_width = 333;
        $original_height = 108;

        // New width for the logo (in mm)
        $new_width = 50;

        // Calculate proportional height
        $new_height = ($original_height / $original_width) * $new_width;

        // Add the logo
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, $new_width, $new_height);
        }

        // Header text
        $this->SetFont('helvetica', 'B', 22); // Font size
        $this->SetTextColor(244, 123, 32); // Orange color (#F47B20)
        $this->SetXY(10 + $new_width + 20, 10 + ($new_height / 2) - 5); // Align text with logo
        $this->Cell(0, 0, 'HOW ORLANDO COMPARES?', 0, 0, 'L');
    }

    public function Footer()
    {
        // Position the footer 15mm from the bottom
        $this->SetY(-15);
        // Set the font
        $this->SetFont('helvetica', 'I', 8);
        // Set text color
        $this->SetTextColor(128, 128, 128);
        // Add page numbering
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

class MSA_Tool_Export
{
    /**
     * Generates a PDF and saves it in the export directory.
     *
     * @param array $categories The content for the PDF.
     * @return string|null URL of the file or null in case of an error.
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

            // Generate PDF content (tables)
            foreach ($categories as $category) {
                // Category header
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->SetTextColor(244, 123, 32);
                $pdf->Cell(0, 10, $category['name'], 0, 1, 'L');
                $pdf->Ln(5);

                // Table styling
                $html = '<table cellpadding="4" cellspacing="0" style="border-collapse: collapse;">';

                // Header rows
                if (!empty($category['headers'])) {
                    foreach ($category['headers'] as $headerRow) {
                        $html .= '<tr>';
                        foreach ($headerRow as $cell) {
                            $html .= '<th style="font-size: 11px; font-weight: bold; color: rgb(244, 123, 32); text-align: center;">' . htmlspecialchars($cell) . '</th>';
                        }
                        $html .= '</tr>';
                    }
                }

                // Data rows
                if (!empty($category['rows'])) {
                    foreach ($category['rows'] as $rowIndex => $row) {
                        $rowColor = ($rowIndex % 2 === 0) ? 'background-color: #F5F5F5;' : 'background-color: #FFFFFF;';
                        $html .= '<tr style="' . $rowColor . '">';
                        foreach ($row as $colIndex => $cell) {
                            $cellStyle = $colIndex === 0
                                ? 'font-weight: bold;'
                                : ($colIndex === 1
                                    ? 'background-color: rgb(244, 123, 32); color: white;'
                                    : 'color: black;');
                            $html .= '<td style="font-size: 9px; ' . $cellStyle . ' text-align: left; padding: 4px;">' . htmlspecialchars($cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                }

                $html .= '</table>';
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->Ln(10);
            }

            // Additional information
            $additional_info = is_multisite()
                ? switch_to_blog_and_get_option('msa_tool_global_data', 'msa_tool_export_info')
                : get_option('msa_tool_export_info', '');

            if (!empty($additional_info)) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->writeHTML($additional_info, true, false, true, false, '');
            }

            // Save the PDF
            $timestamp = time();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';
            $base_url = $upload_dir['baseurl'] . '/msa-tool/exports';
            $filename = "Orlando-MSA-Comparison-{$timestamp}.pdf";

            if (!file_exists($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            $output_path = "{$base_dir}/{$filename}";
            $pdf->Output($output_path, 'F');

            return "{$base_url}/{$filename}";
        } catch (Exception $e) {
            error_log("[PDF EXPORT ERROR] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generates an Excel file and saves it in the export directory.
     *
     * @param array $categories The content for the Excel file.
     * @return string|null URL of the file or null in case of an error.
     */
    public static function generate_excel($categories)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('MSA Comparison');

            // Set headers
            $sheet->setCellValue('A1', 'Category');
            $sheet->setCellValue('B1', 'Indicator / Subcategory');

            // Generate region headers
            $column = 'C';
            if (!empty($categories[0]['headers'][0])) {
                foreach ($categories[0]['headers'][0] as $index => $header) {
                    if ($index % 2 === 1) {
                        $sheet->setCellValue("{$column}1", $header);
                        $column++;
                        $sheet->setCellValue("{$column}1", 'Rank');
                        $column++;
                    }
                }
            }

            // Fill data rows
            $row = 2;
            foreach ($categories as $category) {
                foreach ($category['rows'] as $dataRow) {
                    $sheet->setCellValue("A{$row}", $category['name']);
                    $sheet->setCellValue("B{$row}", $dataRow[0]);

                    $col = 'C';
                    foreach (array_slice($dataRow, 1) as $value) {
                        $sheet->setCellValue("{$col}{$row}", $value);
                        $col++;
                    }

                    $row++;
                }
            }

            // Save the Excel file
            $timestamp = time();
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'] . '/msa-tool/exports';
            $base_url = $upload_dir['baseurl'] . '/msa-tool/exports';
            $filename = "Orlando-MSA-Comparison-{$timestamp}.xlsx";

            if (!file_exists($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            $output_path = "{$base_dir}/{$filename}";
            $writer = new Xlsx($spreadsheet);
            $writer->save($output_path);

            return "{$base_url}/{$filename}";
        } catch (Exception $e) {
            error_log("[EXCEL EXPORT ERROR] " . $e->getMessage());
            return null;
        }
    }
}
