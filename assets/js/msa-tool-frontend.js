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


// export pdf
jQuery(document).ready(function ($) {
    $('#export-pdf').on('click', function () {
        // Пример данных таблицы — заменить на реальный сбор данных
        const tableData = {
            headers: ['Indicator', 'Region 1', 'Region 2', 'Region 3'],
            rows: [
                ['GDP', '1000', '2000', '3000'],
                ['Population', '500', '600', '700'],
            ]
        };

        $.ajax({
            url: msaToolData.ajaxurl,
            type: 'POST',
            data: {
                action: 'export_pdf',
                table_data: JSON.stringify(tableData)
            },
            success: function (response) {
                console.log('PDF generation started');
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });
});
