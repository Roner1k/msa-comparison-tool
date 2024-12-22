<?php
global $wpdb;

// Имя таблицы
$table_name = $wpdb->get_blog_prefix() . 'msa_tool_data';

// Установка параметров сортировки
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id'; // Поле для сортировки
$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'ASC'; // Направление сортировки

// Разрешённые поля для сортировки — добавляем сюда 'subcategory'
$allowed_columns = ['id', 'category', 'subcategory', 'indicator', 'region', 'slug', 'value'];
if (!in_array($orderby, $allowed_columns)) {
    $orderby = 'id';
}

// Получение текущей страницы
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1; // Текущая страница
$per_page = 100; // Количество записей на странице
$offset = ($paged - 1) * $per_page; // Смещение

// Получение общего числа записей
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Извлечение данных с сортировкой и пагинацией
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ),
    ARRAY_A
);

// Получение текущего параметра страницы
$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'msa-tool-results';
?>
<div class="wrap">
    <h1>Imported Data</h1>
    <h2>New record</h2>
    <a href="<?php echo admin_url('admin.php?page=msa-tool-add'); ?>" class="button button-primary">Add Row</a>

    <?php
    // Проверка наличия данных
    if (!empty($results)) {
        $total_pages = ceil($total_items / $per_page);
        if ($total_pages > 1) {
            echo '<div class="tablenav bottom">';
            echo '<div class="tablenav-pages">';

            // Ссылки пагинации
            for ($i = 1; $i <= $total_pages; $i++) {
                $class = ($i == $paged) ? 'class="current"' : '';
                $url = esc_url(add_query_arg(['paged' => $i, 'orderby' => $orderby, 'order' => $order], admin_url('admin.php?page=' . $current_page)));
                echo '<a ' . $class . ' href="' . $url . '">' . $i . '</a> ';
            }

            echo '</div>';
            echo '</div>';
        }


        echo '<table class="widefat fixed" style="margin-top: 20px;">';
        echo '<thead>';
        echo '<tr>';

        // Функция для добавления ссылки на сортировку
        function sort_link($column, $current_orderby, $current_order, $current_page)
        {
            $next_order = ($current_orderby === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
            return '<a href="' . esc_url(add_query_arg(['orderby' => $column, 'order' => $next_order], admin_url('admin.php?page=' . $current_page))) . '">' . ucfirst($column) . '</a>';
        }

        echo '<th>' . sort_link('id', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('category', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('subcategory', $orderby, $order, $current_page) . '</th>'; // Новая колонка
        echo '<th>' . sort_link('indicator', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('region', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('slug', $orderby, $order, $current_page) . '</th>';
        echo '<th>' . sort_link('value', $orderby, $order, $current_page) . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Вывод данных
        foreach ($results as $row) {
            $edit_url = admin_url('admin.php?page=msa-tool-edit&id=' . $row['id']);
            $delete_url = wp_nonce_url(admin_url('admin.php?page=msa-tool-results&delete_id=' . $row['id']), 'msa_tool_delete_nonce_' . $row['id']);

            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . ' <a href="' . esc_url($edit_url) . '" class="button button-secondary">Edit</a>
<a href="' . esc_url($delete_url) . '" class="button button-secondary delete-link">Delete</a></td>';
            echo '<td>' . esc_html($row['category']) . '</td>';
            echo '<td>' . esc_html($row['subcategory']) . '</td>'; // Выводим значение subcategory
            echo '<td>' . esc_html($row['indicator']) . '</td>';
            echo '<td>' . esc_html($row['region']) . '</td>';
            echo '<td>' . esc_html($row['slug']) . '</td>';
            echo '<td>' . esc_html($row['value']) . '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Пагинация
//        $total_pages = ceil($total_items / $per_page);

        if ($total_pages > 1) {
            echo '<div class="tablenav bottom">';
            echo '<div class="tablenav-pages">';

            // Ссылки пагинации
            for ($i = 1; $i <= $total_pages; $i++) {
                $class = ($i == $paged) ? 'class="current"' : '';
                $url = esc_url(add_query_arg(['paged' => $i, 'orderby' => $orderby, 'order' => $order], admin_url('admin.php?page=' . $current_page)));
                echo '<a ' . $class . ' href="' . $url . '">' . $i . '</a> ';
            }

            echo '</div>';
            echo '</div>';
        }
    } else {
        // Если данных нет
        echo '<h1>Imported Data</h1>';
        echo '<p>No data available in the database.</p>';
    }
    ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteLinks = document.querySelectorAll('.wrap .delete-link');
        deleteLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (!confirm('Are you sure you want to delete this row?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
