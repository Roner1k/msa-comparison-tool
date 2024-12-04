<?php
global $wpdb;

// Ім'я таблиці
$table_name = $wpdb->get_blog_prefix() . 'msa_tool_map_keys';

// Установка параметрів сортування
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id'; // Поле для сортування
$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'ASC'; // Напрямок сортування

// Дозволені поля для сортування
$allowed_columns = ['id', 'region_slug', 'map_id'];
if (!in_array($orderby, $allowed_columns)) {
    $orderby = 'id';
}

// Отримання поточної сторінки
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1; // Поточна сторінка
$per_page = 50; // Кількість записів на сторінці
$offset = ($paged - 1) * $per_page; // Зміщення

// Отримання загальної кількості записів
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Отримання даних із сортуванням і пагінацією
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ),
    ARRAY_A
);

// Отримання поточного параметра сторінки
$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'msa-tool-region-mapping';
?>

<div class="wrap">
    <h1>Region Mapping Results</h1>
    <h2>New Mapping</h2>
    <a href="<?php echo admin_url('admin.php?page=msa-tool-add&new-map-row'); ?>" class="button button-primary">Add Map Row</a>

    <?php
    // Перевірка наявності даних
    if (!empty($results)) {
        echo '<table class="widefat fixed" style="margin-top: 20px;">';
        echo '<thead>';
        echo '<tr>';

        // Функція для додавання посилання на сортування
        function sort_link($column, $current_orderby, $current_order, $current_page)
        {
            $next_order = ($current_orderby === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
            return '<a href="' . esc_url(add_query_arg(['orderby' => $column, 'order' => $next_order], admin_url('admin.php?page=' . $current_page))) . '">' . ucfirst(str_replace('_', ' ', $column)) . '</a>';
        }

        echo '<th>' . sort_link('id', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('region_slug', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('map_id', $orderby, $order, $current_page) . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Виведення даних
        foreach ($results as $row) {
            $edit_url = admin_url('admin.php?page=msa-tool-edit&edit-map-row&id=' . $row['id']);
            $delete_url = wp_nonce_url(
                admin_url('admin.php?page=msa-tool-region-mapping&delete_id=' . $row['id']),
                'msa_tool_delete_map_nonce_' . $row['id']
            );

            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '<a href="' . esc_url($edit_url) . '" class="button button-secondary">Edit</a> ';
            echo '<a href="' . esc_url($delete_url) . '" class="button button-secondary delete-link">Delete</a></td>';
            echo '<td>' . esc_html($row['region_slug']) . '</td>';
            echo '<td>' . esc_html($row['map_id']) . '</td>';
            echo '</tr>';
        }


        echo '</tbody>';
        echo '</table>';

        // Пагінація
        $total_pages = ceil($total_items / $per_page);

        if ($total_pages > 1) {
            echo '<div class="tablenav bottom">';
            echo '<div class="tablenav-pages">';

            // Посилання на пагінацію
            for ($i = 1; $i <= $total_pages; $i++) {
                $class = ($i == $paged) ? 'class="current"' : '';
                $url = esc_url(add_query_arg(['paged' => $i, 'orderby' => $orderby, 'order' => $order], admin_url('admin.php?page=' . $current_page)));
                echo '<a ' . $class . ' href="' . $url . '">' . $i . '</a> ';
            }

            echo '</div>';
            echo '</div>';
        }
    } else {
        // Якщо даних немає
        echo '<p>No mapping data available in the database.</p>';
    }
    ?>
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

