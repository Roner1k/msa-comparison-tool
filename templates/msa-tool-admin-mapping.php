<?php
global $wpdb;

// Table name
$table_name = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

// Set sorting parameters
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id'; // Column to sort by
$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'ASC'; // Sort direction

// Allowed columns for sorting
$allowed_columns = ['id', 'region_slug', 'map_id'];
if (!in_array($orderby, $allowed_columns)) {
    $orderby = 'id';
}

// Get the current page
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1; // Current page
$per_page = 50; // Records per page
$offset = ($paged - 1) * $per_page; // Offset

// Get the total number of records
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Retrieve data with sorting and pagination
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ),
    ARRAY_A
);

// Get the current page parameter
$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'msa-tool-region-mapping';
?>

<div class="wrap">
    <h1>Region Mapping Results</h1>
    <h2>New Mapping</h2>
    <a href="<?php echo admin_url('admin.php?page=msa-tool-add&new-map-row'); ?>" class="button button-primary">Add Map Row</a>

    <?php if (!empty($results)): ?>
        <table class="widefat fixed" style="margin-top: 20px;">
            <thead>
            <tr>
                <?php
                // Function to add sorting links
                function sort_link($column, $current_orderby, $current_order, $current_page)
                {
                    $next_order = ($current_orderby === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
                    return '<a href="' . esc_url(add_query_arg(['orderby' => $column, 'order' => $next_order], admin_url('admin.php?page=' . $current_page))) . '">' . ucfirst(str_replace('_', ' ', $column)) . '</a>';
                }

                echo '<th>' . sort_link('id', $orderby, $order, $current_page) . '</th>';
                echo '<th>' . sort_link('region_slug', $orderby, $order, $current_page) . '</th>';
                echo '<th>' . sort_link('map_id', $orderby, $order, $current_page) . '</th>';
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $row): ?>
                <?php
                $edit_url = admin_url('admin.php?page=msa-tool-edit&edit-map-row&id=' . $row['id']);
                $delete_url = wp_nonce_url(
                    admin_url('admin.php?page=msa-tool-region-mapping&delete_id=' . $row['id']),
                    'msa_tool_delete_map_nonce_' . $row['id']
                );
                ?>
                <tr>
                    <td>
                        <?php echo esc_html($row['id']); ?>
                        <a href="<?php echo esc_url($edit_url); ?>" class="button button-secondary">Edit</a>
                        <a href="<?php echo esc_url($delete_url); ?>" class="button button-secondary delete-link">Delete</a>
                    </td>
                    <td><?php echo esc_html($row['region_slug']); ?></td>
                    <td><?php echo esc_html($row['map_id']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Pagination
        $total_pages = ceil($total_items / $per_page);

        if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php
                        $class = ($i == $paged) ? 'class="current"' : '';
                        $url = esc_url(add_query_arg(['paged' => $i, 'orderby' => $orderby, 'order' => $order], admin_url('admin.php?page=' . $current_page)));
                        ?>
                        <a <?php echo $class; ?> href="<?php echo $url; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>No mapping data available in the database.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteLinks = document.querySelectorAll('.wrap .delete-link');
        deleteLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (!confirm('Are you sure you want to delete this entry?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
