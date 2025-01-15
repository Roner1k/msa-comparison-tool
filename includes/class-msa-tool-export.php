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
        $original_width = 1200;
        $original_height = 388;

        // New width for the logo (in mm)
        $new_width = 50;

        // Calculate proportional height
        $new_height = ($original_height / $original_width) * $new_width;

        // Add the header text
        $this->SetFont('arialbmt', '', 22); // Font size
        $this->SetTextColor(244, 123, 32); // Orange color (#F47B20)
        $this->SetXY(15, 15); // Position text at the top-left corner
        $this->Cell(0, 0, 'HOW ORLANDO COMPARES', 0, 0, 'L');

        // Add the logo aligned to the right
        if (file_exists($logo_path)) {
            $this->Image($logo_path, $this->getPageWidth() - $new_width - 15, 12, $new_width, $new_height);
        }
    }


    public function Footer()
    {
        // Position the footer 15mm from the bottom
        $this->SetY(-15);
        // Set the font
        $this->SetFont('arialmt', '', 8);
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
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->AddPage();

            foreach ($categories as $category) {
                $pdf->SetFont('arialbmt', '', 14);
                $pdf->SetTextColor(244, 123, 32);
                $pdf->Cell(0, 10, $category['name'], 0, 1, 'L');
                $pdf->Ln(5);

                $html = '<table cellpadding="4" cellspacing="0" style="border-collapse: collapse; width: 100%;">';

                if (!empty($category['headers'])) {
                    $headerRow = $category['headers'][0];
                    $html .= '<tr>';

                    $html .= '<th style="font-size: 11px; font-weight: bold; text-align: left;"></th>';

                    $rankExists = in_array('Rank', $headerRow);

                    for ($i = 1; $i < count($headerRow); $i += ($rankExists ? 2 : 1)) {
                        $regionName = htmlspecialchars($headerRow[$i]);
                        $regionHeader = $rankExists
                            ? $regionName . '<br><span style="font-size: 9px; font-weight: normal;">(Rank)</span>'
                            : $regionName;
                        $html .= '<th style="font-size: 11px; font-weight: bold; color: rgb(244, 123, 32); text-align: right;">' . $regionHeader . '</th>';
                    }
                    $html .= '</tr>';
                }



                if (!empty($category['rows'])) {
                    foreach ($category['rows'] as $rowIndex => $row) {
                        $rowColor = ($rowIndex % 2 === 0) ? 'background-color: #F5F5F5;' : 'background-color: #FFFFFF;';
                        $html .= '<tr style="' . $rowColor . '">';

                        $html .= '<td style="font-size: 9px; font-weight: bold; color: black; text-align: left; padding: 4px;">' . htmlspecialchars($row[0]) . '</td>';

                        for ($colIndex = 1; $colIndex < count($row); $colIndex += ($rankExists ? 2 : 1)) {
                            $value = htmlspecialchars($row[$colIndex]);
                            $rank = $rankExists && isset($row[$colIndex + 1]) && $row[$colIndex + 1] !== '-' ? htmlspecialchars($row[$colIndex + 1]) : null;
                            $combinedValue = $rank ? "$value ($rank)" : $value;

                            if ($colIndex === 1) {
                                $html .= '<td style="font-size: 9px; background-color: rgb(244, 123, 32); color: white; text-align: right; padding: 4px; font-family: arialmt;">' . $combinedValue . '</td>';
                            } else {
                                $html .= '<td style="font-size: 9px; color: black; text-align: right; padding: 4px; font-family: arialmt;">' . $combinedValue . '</td>';
                            }
                        }
                        $html .= '</tr>';
                    }
                }





                $html .= '</table>';
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->Ln(10);
            }

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

            if (!empty($additional_info)) {
                $pdf->AddPage();
                $pdf->SetFont('arialmt', '', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->writeHTML($additional_info, true, false, true, false, '');
            }

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
//            error_log("[MSA EXPORT] Categories structure:\n" . print_r($categories, true));

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('MSA Comparison');

            $sheet->setCellValue('A1', 'Category');
            $sheet->setCellValue('B1', 'Indicator / Subcategory');

            $column = 'C';

            if (!empty($categories[0]['headers'][0])) {

                $headers = $categories[0]['headers'][0];
                 $headers = array_filter($headers);
                $headers = array_values($headers);

                 for ($i = 0; $i < count($headers); $i++) {
                     if (strtolower($headers[$i]) === 'rank') {

                        $sheet->setCellValue("{$column}1", 'Rank');
                        $column++;
                        continue;
                    }

                    $sheet->setCellValue("{$column}1", $headers[$i]);
                    $column++;
                }
            }

            $row = 2;
            foreach ($categories as $category) {
                foreach ($category['rows'] as $dataRow) {

                    $sheet->setCellValue("A{$row}", $category['name']);
                    $sheet->setCellValue("B{$row}", $dataRow[0]);

                    $col = 'C';


                    for ($i = 1; $i < count($dataRow); $i++) {
                        $value = $dataRow[$i];
                        $sheet->setCellValue("{$col}{$row}", $value);
                        $col++;
                    }
                    $row++;
                }
            }

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
