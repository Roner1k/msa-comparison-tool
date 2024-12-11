console.log("MSA Tool Frontend script loaded");
document.addEventListener("DOMContentLoaded", () => {
    // Проверяем наличие данных
    if (typeof msaToolData !== "undefined") {
        // console.log("Received data:", msaToolData);

        // Пример обработки данных
        const {categories, regions} = msaToolData;

        // Отображение категорий
        Object.entries(categories).forEach(([category, indicators]) => {
            // console.log(`Category: ${category}`, indicators);
        });

        // Отображение регионов
        Object.entries(regions).forEach(([region, data]) => {
            // console.log(`Region: ${region}`, data);
        });

        // TODO: Реализовать динамическую отрисовку данных
    } else {
        console.error("msaToolData is not defined!");
    }
});

//toggle
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.msa-toggle-category').forEach(button => {
        button.addEventListener('click', function () {
            const categoryContent = this.closest('.msa-category').querySelector('.msa-category-content');
            if (categoryContent.style.display === 'none') {
                categoryContent.style.display = 'block';
            } else {
                categoryContent.style.display = 'none';
            }
        });
    });
});
