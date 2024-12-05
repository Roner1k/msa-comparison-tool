<?php if (!empty($data)):

    echo '<div id="msa-tool-container">';
    echo '<div id="msa-tool-filters"></div>';
    echo '<div id="msa-tool-table"></div>';
    echo '</div>';

    ?>
    <h4>example</h4>
    <table>
        <thead>
        <tr>
            <th>Category</th>
            <th>Indicator</th>
            <th>Region</th>
            <th>Value</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $row): ?>
            <tr>
                <td><?php echo esc_html($row['category']); ?></td>
                <td><?php echo esc_html($row['indicator']); ?></td>
                <td><?php echo esc_html($row['region']); ?></td>
                <td><?php echo esc_html($row['value']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No data available.</p>
<?php endif; ?>
