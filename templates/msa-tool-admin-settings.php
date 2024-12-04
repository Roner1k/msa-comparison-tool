<div class="wrap">
    <h1>MSA Comparison Tool</h1>
    <p>On this page, you can upload an XLSX file for debugging or importing data into the database.</p>
    <p>Need help? <a href="<?php echo esc_url(plugins_url('assets/example-data-short.xlsx', dirname(__FILE__))); ?>" download>
            Download the example XLSX file
        </a>.
    </p>

    <h2>Import Settings</h2>

    <!-- Форма для загрузки файла -->
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


    <!-- Опция для мультисайта -->
    <?php if (is_multisite()) : ?>
        <form method="post">
            <?php wp_nonce_field('msa_tool_global', 'msa_tool_global_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="msa_tool_global_data">Enable Global Data Mode:</label>
                    </th>
                    <td>
                        <input type="checkbox" id="msa_tool_global_data" name="msa_tool_global_data" value="1"
                            <?php checked((int)get_site_option('msa_tool_global_data') === get_current_blog_id()); ?>>

                        <p class="description">If enabled, this site will manage data globally for all subsites in the
                            network.</p>

                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="msa_tool_global_submit" class="button button-primary">Save Settings</button>
            </p>
        </form>


    <?php endif; ?>

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
