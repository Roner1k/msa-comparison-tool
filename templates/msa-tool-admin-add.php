<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msa_tool_add_nonce_field']) && wp_verify_nonce($_POST['msa_tool_add_nonce_field'], 'msa_tool_add_nonce')) {
    if (isset($_GET['new-map-row'])) {
         $table_name = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';
        $data = [
            'region_slug' => sanitize_text_field($_POST['region_slug']),
            'map_id' => sanitize_text_field($_POST['map_id']),
        ];
        $inserted = $wpdb->insert($table_name, $data, ['%s', '%s']);
    } else {
        // Додавання в таблицю tool_data
        $table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';
        $data = [
            'region' => sanitize_text_field($_POST['region']),
            'slug' => sanitize_text_field($_POST['slug']),
            'category' => sanitize_text_field($_POST['category']),
            'indicator' => sanitize_text_field($_POST['indicator']),
            'value' => sanitize_text_field($_POST['value']),
        ];
        $inserted = $wpdb->insert($table_name, $data, ['%s', '%s', '%s', '%s', '%s']);
    }

    if ($inserted) {
        wp_redirect(admin_url('admin.php?page=msa-tool-region-mapping'));
        exit;
    }
}

// Вивід HTML відбувається після обробки POST-запиту
$is_map_row = isset($_GET['new-map-row']);

?>
<div class="wrap">
    <h1><?php echo $is_map_row ? 'Add New Mapping Row' : 'Add New Data Row'; ?></h1>
    <form method="post"><?php wp_nonce_field('msa_tool_add_nonce', 'msa_tool_add_nonce_field'); ?><?php if ($is_map_row): ?>
            <table class="form-table">
                <tr>
                    <th><label for="region_slug">Region Slug</label></th>
                    <td><input type="text" id="region_slug" name="region_slug" value="" required></td>
                </tr>
                <tr>
                    <th><label for="map_id">Map ID</label></th>
                    <td><input type="text" id="map_id" name="map_id" value="" required></td>
                </tr>
            </table>
        <?php else: ?>
            <table class="form-table">
                <tr>
                    <th><label for="region">Region</label></th>
                    <td><input type="text" id="region" name="region" value="" required></td>
                </tr>
                <tr>
                    <th><label for="slug">Slug</label></th>
                    <td><input type="text" id="slug" name="slug" value="" required></td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td><input type="text" id="category" name="category" value="" required></td>
                </tr>
                <tr>
                    <th><label for="indicator">Indicator</label></th>
                    <td><input type="text" id="indicator" name="indicator" value="" required></td>
                </tr>
                <tr>
                    <th><label for="value">Value</label></th>
                    <td><input type="text" id="value" name="value" value="" required></td>
                </tr>
            </table>
        <?php endif; ?>

        <p>
            <button type="submit" class="button button-primary">Save Row</button>
        </p>
    </form>
</div>