console.log("MSA Tool Frontend script loaded");
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('msa-tool-container');
    const filters = document.getElementById('msa-tool-filters');
    const table = document.getElementById('msa-tool-table');

    // Получаем данные из PHP
    const { categories } = msaToolData;

    // Создаем фильтр (позже тут будут регионы)
    const regionSelect = document.createElement('select');
    regionSelect.innerHTML = `
        <option value="Orlando">Orlando</option>
        <option value="Virginia">Virginia</option>
        <option value="Alaska">Alaska</option>
    `;
    filters.appendChild(regionSelect);

    // Генерация таблицы
    function generateTable() {
        table.innerHTML = ''; // Очищаем таблицу

        Object.entries(categories).forEach(([category, indicators]) => {
            const categoryHeader = document.createElement('h3');
            categoryHeader.textContent = category;
            table.appendChild(categoryHeader);

            const categoryTable = document.createElement('table');
            categoryTable.className = 'category-table';
            table.appendChild(categoryTable);

            Object.entries(indicators).forEach(([indicator, values]) => {
                const row = document.createElement('tr');
                const indicatorCell = document.createElement('td');
                indicatorCell.textContent = indicator;
                row.appendChild(indicatorCell);

                const selectedRegion = regionSelect.value;
                const valueCell = document.createElement('td');
                valueCell.textContent = values[selectedRegion] || 'N/A';
                row.appendChild(valueCell);

                categoryTable.appendChild(row);
            });
        });
    }

    // Обновляем таблицу при изменении выбора региона
    regionSelect.addEventListener('change', generateTable);

    // Изначально генерируем таблицу
    generateTable();
});
