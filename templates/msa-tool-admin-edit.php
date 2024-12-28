<?php
$is_map_row = isset($_GET['edit-map-row']);
?>
<div class="wrap">
    <h1><?php echo $is_map_row ? 'Edit Map Key' : 'Edit Imported Row'; ?></h1>
    <form method="post">
        <?php wp_nonce_field('msa_tool_edit', 'msa_tool_edit_nonce'); ?>

        <?php if ($is_map_row): ?>
            <!-- Fields for the map_keys table -->
            <table class="form-table">
                <tr>
                    <th><label for="region_slug">Region Slug</label></th>
                    <td>
                        <input
                                type="text"
                                name="region_slug"
                                id="region_slug"
                                value="<?php echo esc_attr($entry['region_slug']); ?>"
                                required
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="map_id">Map ID</label></th>
                    <td>
                        <input
                                type="text"
                                name="map_id"
                                id="map_id"
                                value="<?php echo esc_attr($entry['map_id']); ?>"
                        >
                    </td>
                </tr>
            </table>
        <?php else: ?>
            <!-- Fields for the tool_data table with subcategory field -->
            <table class="form-table">
                <tr>
                    <th><label for="region">Region</label></th>
                    <td>
                        <input
                                type="text"
                                name="region"
                                id="region"
                                value="<?php echo esc_attr($entry['region']); ?>"
                                required
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="slug">Slug</label></th>
                    <td>
                        <input
                                type="text"
                                name="slug"
                                id="slug"
                                value="<?php echo esc_attr($entry['slug']); ?>"
                                required
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <input
                                type="text"
                                name="category"
                                id="category"
                                value="<?php echo esc_attr($entry['category']); ?>"
                                required
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="subcategory">Subcategory</label></th>
                    <td>
                        <input
                                type="text"
                                name="subcategory"
                                id="subcategory"
                                value="<?php echo esc_attr($entry['subcategory']); ?>"
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="indicator">Indicator</label></th>
                    <td>
                        <input
                                type="text"
                                name="indicator"
                                id="indicator"
                                value="<?php echo esc_attr($entry['indicator']); ?>"
                                required
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="value">Value</label></th>
                    <td>
                        <input
                                type="text"
                                name="value"
                                id="value"
                                value="<?php echo esc_attr($entry['value']); ?>"
                                required
                        >
                    </td>
                </tr>
            </table>
        <?php endif; ?>

        <p class="submit">
            <button
                    type="submit"
                    name="msa_tool_save_changes"
                    class="button button-primary"
            >
                Save Changes
            </button>
        </p>
    </form>
</div>
