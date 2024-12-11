jQuery(document).ready(function ($) {

    console.log("ArcGIS Map script loaded");
    /*
        require([
            "esri/WebMap",
            "esri/views/MapView"
        ], function (WebMap, MapView) {
            // Create WebMap instance using the specified ID
            const webMap = new WebMap({
                portalItem: {
                    id: "5c0c0595be9c422bb95ace1bc48f610e" // Replace with your map ID
                }
            });

            // Create MapView instance
            const view = new MapView({
                container: "viewDiv", // ID of the container div
                map: webMap,
                zoom: 10, // Initial zoom level
                center: [-81.379234, 28.538336] // Center coordinates (Orlando, FL)
            });

            // Log when the map is successfully loaded
            view.when(function () {
                console.log("Map loaded successfully!");

                // Listen for click events on the map
                view.on("click", function (event) {
                    // Identify features at the clicked location
                    view.hitTest(event).then(function (response) {
                        if (response.results.length > 0) {
                            // Loop through results to find FeatureLayer results
                            response.results.forEach(function (result) {
                                if (result.graphic && result.graphic.layer.type === "feature") {
                                    // Log all attributes of the clicked feature
                                    console.log("Clicked feature attributes:", result.graphic.attributes);

                                    // Example: log a specific attribute
                                    console.log("GEOID:", result.graphic.attributes.GEOID || "Not available");
                                }
                            });
                        } else {
                            console.log("No features found at clicked location.");
                        }
                    }).catch(function (error) {
                        console.error("Error during hitTest:", error);
                    });
                });
            }).catch(function (error) {
                console.error("Error loading map: ", error);
            });
        });
        */
    require([
        "esri/WebMap",
        "esri/views/MapView",
        "esri/tasks/QueryTask",
        "esri/tasks/support/Query"
    ], function (WebMap, MapView, QueryTask, Query) {
        // Создание WebMap с вашим ID карты
        const webMap = new WebMap({
            portalItem: {
                id: "5c0c0595be9c422bb95ace1bc48f610e" // Ваш ID карты
            }
        });

        // Инициализация MapView
        const view = new MapView({
            container: "viewDiv", // Контейнер карты
            map: webMap,
            zoom: 5, // Начальное приближение
            center: [-95, 37] // Центр карты (пример: центр США)
        });

        // Настройка QueryTask с вашим URL слоя
        const queryTask = new QueryTask({
            url: "https://services2.arcgis.com/3KQnhNHIDCtyRpO4/arcgis/rest/services/tl_2023_us_cbsa_s/FeatureServer/0" // Ваш Feature Layer
        });

        // Слушаем событие клика по карте
        view.on("click", async function (event) {
            // Конвертируем экранные координаты в географические
            const mapPoint = event.mapPoint;

            // Настраиваем запрос
            const query = new Query();
            query.geometry = mapPoint; // Используем точку клика
            query.returnGeometry = false; // Геометрия не возвращается, только атрибуты
            query.outFields = ["*"]; // Возвращаем все атрибуты

            try {
                // Выполняем запрос
                const result = await queryTask.execute(query);

                if (result.features.length > 0) {
                    // Получаем первый объект
                    const feature = result.features[0];

                    // Лог атрибутов объекта в консоль
                    console.log("Clicked feature attributes:", feature.attributes);
                } else {
                    console.log("No features found at clicked location.");
                }
            } catch (error) {
                console.error("Error querying features:", error);
            }
        });

        // Лог при успешной загрузке карты
        view.when(function () {
            console.log("Map successfully loaded.");
        }).catch(function (error) {
            console.error("Error loading map:", error);
        });
    });


});
