jQuery(document).ready(function ($) {
    console.log("MSA Tool Frontend script loaded");

    // Toggle categories
    $(".msa-toggle-category").on("click", function () {
        const categoryContent = $(this).closest(".msa-category").find(".msa-category-content");
        categoryContent.toggle();
    });

    // Open the first accordion on page load
    $(".msa-category").first().find(".msa-category-content").show();

    // "Toggle All" button
    let allExpanded = false; // State of all accordions

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

    // Dropdown menu interactions
    const customSelect = $("#msa-custom-select");
    const options = $(".msa-option");
    const searchInput = $("#msa-search-input");

    // Filter the dropdown list
    searchInput.on("input", function () {
        const filter = $(this).val().toLowerCase();
        options.each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(filter));
        });
    });

    // Open/close the dropdown menu
    customSelect.on("click", function (e) {
        if (!$(e.target).hasClass("msa-selected-item") && e.target !== searchInput[0]) {
            customSelect.find(".msa-options").toggle();
        }
    });

    // Close the menu when clicking outside
    $(document).on("click", function (e) {
        if (!customSelect[0].contains(e.target)) {
            customSelect.find(".msa-options").hide();
        }
    });

    // Checkbox to include/exclude Rank columns
    $("#msa-include-rank").on("change", function () {
        const showRank = $(this).is(":checked");
        if (showRank) {
            $(".msa-rank-column").removeClass("hidden");
        } else {
            $(".msa-rank-column").addClass("hidden");
        }
    });

    // Hide rows
    $(".hide-row-btn").on("click", function () {
        const rowId = $(this).data("row-id");
        $(`tr[data-row-id="${rowId}"]`).addClass("hidden-row");
    });

    // Show hidden rows
    $("#msa-view-hidden-fields").on("click", function () {
        $(".hidden-row").removeClass("hidden-row");
    });

    // Toggle subcategories
    $('.table-row[data-has-subcategories="true"]').on("click", function () {
        const rowId = $(this).data("row-id");
        $(`.msa-subcategory-row[data-parent-row-id="${rowId}"]`).toggle();
    });

    // Function to reveal all categories and subcategories
    function revealAllForExport() {
        $(".msa-category-content").slideDown(0); // Instantly expand all categories
        allExpanded = true; // Update toggle button state
        $("#msa-toggle-all").text("Collapse All");

        // Show all subcategories
        $(".msa-subcategory-row").show();
    }

    // General function to collect category data
    function collectCategoriesData() {
        const categories = [];

        $(".msa-category").each(function () {
            const $category = $(this);
            const categoryName = $category.find(".msa-category-header h3").text().trim();
            const includeInDownload = $category.find(".msa-category-checkbox").is(":checked");

            if (!includeInDownload) return;

            const $table = $category.find("table.msa-table");
            if ($table.length === 0) return;

            const headers = [];
            $table.find("thead tr").each(function () {
                const $headerRow = $(this);
                if ($headerRow.is(":hidden")) return;

                const headerRowData = [];
                $headerRow.find("th:visible").each(function () {
                    const $cell = $(this);
                    if ($cell.hasClass("msa-rank-column") && $cell.hasClass("hidden")) return;
                    if ($cell.hasClass("hide-row-col")) return;
                    headerRowData.push($cell.text().trim());
                });

                if (headerRowData.length > 0) {
                    headers.push(headerRowData);
                }
            });

            const tableData = [];
            $table.find("tbody tr").each(function () {
                const $row = $(this);
                if ($row.is(":hidden")) return;

                const rowData = [];
                $row.find("td:visible").each(function () {
                    const $cell = $(this);
                    if ($cell.hasClass("msa-rank-column") && $cell.hasClass("hidden")) return;
                    if ($cell.hasClass("hide-row-col")) return;
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

        return categories;
    }

    // Export to PDF
    $("#export-pdf").on("click", function () {
        revealAllForExport();

        const categories = collectCategoriesData();

        if (categories.length === 0) {
            alert("No categories selected for download.");
            return;
        }

        $.ajax({
            url: msaToolData.ajaxurl,
            type: "POST",
            data: {
                action: "export_pdf",
                categories: JSON.stringify(categories),
            },
            success: function (response) {
                if (response.success && response.data.file) {
                    window.open(response.data.file, "_blank");
                } else {
                    console.error("Error:", response.data.message || "Unknown error");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            },
        });
    });

    // Export to Excel
    $("#export-xlsx").on("click", function () {
        revealAllForExport();

        const categories = collectCategoriesData();

        if (categories.length === 0) {
            alert("No categories selected for download.");
            return;
        }

        $.ajax({
            url: msaToolData.ajaxurl,
            type: "POST",
            data: {
                action: "export_excel",
                categories: JSON.stringify(categories),
            },
            success: function (response) {
                if (response.success && response.data.file) {
                    window.open(response.data.file, "_blank");
                } else {
                    console.error("Error:", response.data.message || "Unknown error");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            },
        });
    });
});
