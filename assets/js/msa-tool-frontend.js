jQuery(document).ready(function ($) {
    console.log("MSA Tool Frontend script loaded");

    // Тогглы для категорий
    $(".msa-toggle-category").on("click", function () {
        const categoryContent = $(this).closest(".msa-category").find(".msa-category-content");
        categoryContent.toggle();
    });

    // Открыть первый аккордеон при загрузке страницы
    $(".msa-category").first().find(".msa-category-content").show();

    // Кнопка "Toggle All"
    let allExpanded = false; // Состояние всех аккордеонов

    $("#msa-toggle-all").on("click", function () {
        if (allExpanded) {
            $(".msa-category-content").slideUp();
            allExpanded = false;
            $(this).text("Expand All");
        } else {
            $(".msa-category-content").slideDown();
            allExpanded = true;
            $(this).text("Collapse All");
        }
    });

    // Работа с выпадающим списком
    const customSelect = $("#msa-custom-select");
    const options = $(".msa-option");
    const searchInput = $("#msa-search-input");

    // Фильтрация списка
    searchInput.on("input", function () {
        const filter = $(this).val().toLowerCase();
        options.each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(filter));
        });
    });

    // Открытие/закрытие выпадающего меню
    customSelect.on("click", function (e) {
        if (!$(e.target).hasClass("msa-selected-item") && e.target !== searchInput[0]) {
            customSelect.find(".msa-options").toggle();
        }
    });

    // Закрытие меню при клике вне него
    $(document).on("click", function (e) {
        if (!customSelect[0].contains(e.target)) {
            customSelect.find(".msa-options").hide();
        }
    });

    // Чекбокс для включения/исключения Rank колонок
    $("#msa-include-rank").on("change", function () {
        const showRank = $(this).is(":checked");
        if (showRank) {
            $(".msa-rank-column").removeClass("hidden");
        } else {
            $(".msa-rank-column").addClass("hidden");
        }
    });

    //add button


});

//remove rows
jQuery(document).ready(function ($) {
    // Скрытие строки
    $(".hide-row-btn").on("click", function () {
        const rowId = $(this).data("row-id");
        $(`tr[data-row-id="${rowId}"]`).addClass("hidden-row");
    });

    // Показ скрытых строк
    $("#view-hidden-fields").on("click", function () {
        $(".hidden-row").removeClass("hidden-row");
    });
});

//subcats
jQuery(document).ready(function ($) {
    $('.toggle-subcategories').on('click', function () {
        const indicator = $(this).data('indicator');
        // Найти все строки .msa-subcategory-row у которых data-parent-indicator=indicator
        $(`.msa-subcategory-row[data-parent-indicator="${indicator}"]`).toggle();
    });
});


// export pdf
/*
jQuery(document).ready(function ($) {
    $('#export-pdf').on('click', function () {
        const categories = [];

        $('.msa-category').each(function () {
            const $category = $(this);
            const categoryName = $category.find('.msa-category-header h3').text().trim();
            const includeInDownload = $category.find('.msa-category-checkbox').is(':checked');

            // Пропускаем категорию, если не стоит галочка "Include in Download"
            if (!includeInDownload) return;

            const $table = $category.find('table.msa-table');
            if ($table.length === 0) {
                return;
            }

            // Сбор заголовков таблицы
            const headers = [];
            $table.find('thead tr').each(function () {
                const $headerRow = $(this);
                // Пропускаем скрытые строки заголовка
                if ($headerRow.is(':hidden')) return;

                const headerRowData = [];
                $headerRow.find('th:visible').each(function () {
                    const $cell = $(this);
                    // Можно добавить дополнительную проверку на классы, если нужно
                    if ($cell.hasClass('hide-row-col')) return;
                    headerRowData.push($cell.text().trim());
                });

                if (headerRowData.length > 0) {
                    headers.push(headerRowData);
                }
            });

            // Сбор строк таблицы
            const tableData = [];
            $table.find('tbody tr').each(function () {
                const $row = $(this);

                // Пропускаем скрытые строки
                if ($row.is(':hidden')) return;

                const rowData = [];
                // Берем только видимые ячейки, пропускаем hide-row-col
                $row.find('td:visible').each(function () {
                    const $cell = $(this);
                    if ($cell.hasClass('hide-row-col')) return;
                    rowData.push($cell.text().trim());
                });

                if (rowData.length > 0) {
                    tableData.push(rowData);
                }
            });

            // Если нет данных, пропускаем категорию
            if (headers.length === 0 && tableData.length === 0) return;

            categories.push({
                name: categoryName,
                headers: headers,
                rows: tableData,
            });
        });

        // Проверяем, есть ли что экспортировать
        if (categories.length === 0) {
            alert('No categories selected for download.');
            return;
        }

        // Отправляем данные на сервер
        $.ajax({
            url: msaToolData.ajaxurl,
            type: 'POST',
            data: {
                action: 'export_pdf',
                categories: JSON.stringify(categories),
            },
            success: function (response) {
                if (response.success && response.data.file) {
                    window.location.href = response.data.file;
                } else {
                    console.error('Error:', response.data.message || 'Unknown error');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            },
        });
    });
});
*/
// export pdf
// export pdf
// export pdf
jQuery(document).ready(function ($) {

    // Функция, которая раскрывает все категории и подкатегории
    function revealAllForPdf() {
        // Насильно раскрыть все аккордеоны категорий
        $(".msa-category-content").slideDown(0);  // мгновенно, без анимации
        allExpanded = true;                       // чтобы кнопка "Toggle All" понимала текущее состояние
        $("#msa-toggle-all").text("Collapse All");

        // Раскрыть все подкатегории
        $(".msa-subcategory-row").show();

        // Если хотите раскрыть скрытые строки (hidden-row), раскомментируйте:
        // $(".hidden-row").removeClass("hidden-row");
    }

    $('#export-pdf').on('click', function () {
        revealAllForPdf();

        const categories = [];

        // Собираем данные как раньше...
        $('.msa-category').each(function () {
            const $category = $(this);
            const categoryName = $category.find('.msa-category-header h3').text().trim();
            const includeInDownload = $category.find('.msa-category-checkbox').is(':checked');

            if (!includeInDownload) return;

            const $table = $category.find('table.msa-table');
            if ($table.length === 0) return;

            const headers = [];
            $table.find('thead tr').each(function () {
                const $headerRow = $(this);
                if ($headerRow.is(':hidden')) return;

                const headerRowData = [];
                $headerRow.find('th:visible').each(function () {
                    const $cell = $(this);
                    if ($cell.hasClass('msa-rank-column') && $cell.hasClass('hidden')) return;
                    if ($cell.hasClass('hide-row-col')) return;
                    headerRowData.push($cell.text().trim());
                });

                if (headerRowData.length > 0) {
                    headers.push(headerRowData);
                }
            });

            const tableData = [];
            $table.find('tbody tr').each(function () {
                const $row = $(this);
                if ($row.is(':hidden')) return;

                const rowData = [];
                $row.find('td:visible').each(function () {
                    const $cell = $(this);
                    if ($cell.hasClass('msa-rank-column') && $cell.hasClass('hidden')) return;
                    if ($cell.hasClass('hide-row-col')) return;
                    rowData.push($cell.text().trim());
                });

                if (rowData.length > 0) {
                    tableData.push(rowData);
                }
            });

            if (headers.length === 0 && tableData.length === 0) return;

            categories.push({
                name: categoryName,
                headers: headers,
                rows: tableData,
            });
        });

        if (categories.length === 0) {
            alert('No categories selected for download.');
            return;
        }

        $.ajax({
            url: msaToolData.ajaxurl,
            type: 'POST',
            data: {
                action: 'export_pdf',
                categories: JSON.stringify(categories),
            },
            success: function (response) {
                if (response.success && response.data.file) {
                    // Открыть в новой вкладке
                    window.open(response.data.file, '_blank');
                } else {
                    console.error('Error:', response.data.message || 'Unknown error');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            },
        });
    });

});

