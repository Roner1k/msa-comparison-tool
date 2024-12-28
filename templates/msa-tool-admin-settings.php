<div class="wrap">
    <h1>MSA Comparison Tool</h1>

    <!-- Settings Section -->
    <h2>MSA Tool Settings</h2>
    <form method="post">
        <?php wp_nonce_field('msa_tool_settings', 'msa_tool_settings_nonce'); ?>

        <table class="form-table">
            <!-- Option to disable ArcGIS scripts -->
            <tr>
                <th scope="row">
                    <label for="msa_tool_disable_arcgis">Disable ArcGIS Scripts:</label>
                </th>
                <td>
                    <input type="checkbox" id="msa_tool_disable_arcgis" name="msa_tool_disable_arcgis" value="1"
                        <?php checked(get_option('msa_tool_disable_arcgis', 0), 1); ?>>
                    <p class="description">Check this box to prevent ArcGIS scripts from being loaded by the plugin.</p>
                </td>
            </tr>

            <!-- Multisite Global Data Option -->
            <?php if (is_multisite()) : ?>
                <tr>
                    <th scope="row">
                        <label for="msa_tool_global_data">Enable Global Data Mode:</label>
                    </th>
                    <td>
                        <input type="checkbox" id="msa_tool_global_data" name="msa_tool_global_data" value="1"
                            <?php checked((int)get_site_option('msa_tool_global_data') === get_current_blog_id()); ?>>
                        <p class="description">If enabled, this site will manage data globally for all subsites in the network.</p>
                    </td>
                </tr>
            <?php endif; ?>

            <!-- Export File Additional Info -->
            <tr>
                <th scope="row">
                    <label for="msa_tool_export_info">Export File Additional Info:</label>
                </th>
                <td>
                    <?php
                    $content = get_option('msa_tool_export_info', '');
                    wp_editor($content, 'msa_tool_export_info', [
                        'textarea_name' => 'msa_tool_export_info',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny' => true,
                    ]);
                    ?>
                    <p class="description">Enter additional information to display at the end of the exported PDF file. You can include headers and paragraphs.</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" name="msa_tool_settings_submit">Save Settings</button>
        </p>
    </form>

    <!-- Import Section -->
    <h2>Import Settings</h2>
    <p>You can upload an XLSX file for debugging or importing data into the database.</p>
    <p>Need help? <a href="<?php echo esc_url(plugins_url('assets/example-data-short.xlsx', dirname(__FILE__))); ?>" download>
            Download the example XLSX file
        </a>.
    </p>

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('msa_tool_import', 'msa_tool_import_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="msa_tool_import_file">Select XLSX file:</label>
                </th>
                <td>
                    <input type="file" id="msa_tool_import_file" name="msa_tool_import_file" accept=".xlsx" required>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" name="msa_tool_import_submit">Import</button>
        </p>
    </form>

    <!-- Recent Imports Section -->
    <h2>Recent Imports</h2>
    <?php
    $import_logs = get_option('msa_tool_import_logs', []);
    if (!empty($import_logs)) {
        echo '<table class="widefat fixed" style="margin-top: 20px;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Date</th>';
        echo '<th>File Name</th>';
        echo '<th>Records Imported</th>';
        echo '<th>Map Records Created</th>';
        echo '<th>Info</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($import_logs as $log) {
            echo '<tr>';
            echo '<td>' . esc_html($log['date']) . '</td>';
            echo '<td>' . esc_html($log['file_name']) . '</td>';
            echo '<td>' . (isset($log['records_imported']) ? esc_html($log['records_imported']) : 'N/A') . '</td>';
            echo '<td>' . (isset($log['map_records_created']) ? esc_html($log['map_records_created']) : 'N/A') . '</td>';
            echo '<td>' . (!empty($log['info']) ? esc_html($log['info']) : 'No Info') . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No imports logged yet.</p>';
    }
    ?>
</div>
