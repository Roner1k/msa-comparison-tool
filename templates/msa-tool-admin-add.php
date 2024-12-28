<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msa_tool_add_nonce_field']) && wp_verify_nonce($_POST['msa_tool_add_nonce_field'], 'msa_tool_add_nonce')) {
    global $wpdb;

    $is_map_row = isset($_GET['new-map-row']);

    if ($is_map_row) {
        // Adding to msa_tool_map_keys
        $table_name = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';
        $data = [
            'region_slug' => sanitize_text_field($_POST['region_slug']),
            'map_id'      => sanitize_text_field($_POST['map_id']),
        ];

        // Insert data
        $inserted = $wpdb->insert($table_name, $data, ['%s', '%s']);

        if ($inserted) {
            $redirect_page = 'msa-tool-region-mapping';
            wp_redirect(admin_url('admin.php?page=' . $redirect_page));
            exit;
        } else {
            self::show_notification('Error adding new mapping entry: ' . $wpdb->last_error, 'error');
        }
    } else {
        // Adding to msa_tool_data with subcategory
        $table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';
        $data = [
            'region'     => sanitize_text_field($_POST['region']),
            'slug'       => sanitize_text_field($_POST['slug']),
            'category'   => sanitize_text_field($_POST['category']),
            'subcategory'=> isset($_POST['subcategory']) ? sanitize_text_field($_POST['subcategory']) : null,
            'indicator'  => sanitize_text_field($_POST['indicator']),
            'value'      => sanitize_text_field($_POST['value']),
        ];

        // Check uniqueness based on (category, subcategory, indicator, slug)
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE category = %s AND subcategory = %s AND indicator = %s AND slug = %s",
            $data['category'],
            $data['subcategory'],
            $data['indicator'],
            $data['slug']
        ));

        if ($exists > 0) {
            // Record already exists
            self::show_notification('A record with these parameters already exists. Please choose different values.', 'error');
        } else {
            // Insert data if no duplicate exists
            $inserted = $wpdb->insert($table_name, $data, ['%s', '%s', '%s', '%s', '%s', '%s']);
            if ($inserted) {
                $redirect_page = 'msa-tool-results';
                wp_redirect(admin_url('admin.php?page=' . $redirect_page));
                exit;
            } else {
                self::show_notification('Error adding new entry: ' . $wpdb->last_error, 'error');
            }
        }
    }
}

// Render HTML form after processing POST request
$is_map_row = isset($_GET['new-map-row']);
?>
<div class="wrap">
    <h1><?php echo $is_map_row ? 'Add New Mapping Row' : 'Add New Data Row'; ?></h1>
    <form method="post">
        <?php wp_nonce_field('msa_tool_add_nonce', 'msa_tool_add_nonce_field'); ?>
        <?php if ($is_map_row): ?>
            <!-- Form for msa_tool_map_keys -->
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
            <!-- Form for msa_tool_data with Subcategory field -->
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
                    <th><label for="subcategory">Subcategory</label></th>
                    <td><input type="text" id="subcategory" name="subcategory" value=""></td>
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
