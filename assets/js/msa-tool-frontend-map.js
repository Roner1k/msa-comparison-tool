/*
jQuery(document).ready(function ($) {


    require([
        "esri/WebMap",
        "esri/views/MapView",
        "esri/layers/FeatureLayer",
        "esri/Graphic"
    ], function (WebMap, MapView, FeatureLayer, Graphic) {
        // Инициализация карты
        const webMap = new WebMap({
            portalItem: {
                id: msaMapData.portalItemId
            }
        });

        const view = new MapView({
            container: "viewDiv",
            map: webMap,
            zoom: 5,
            center: [-95, 37]
        });

        const featureLayer = new FeatureLayer({
            url: msaMapData.featureLayerUrl
        });

        webMap.add(featureLayer);

        // Подсветка регионов на карте
        function highlightRegions(mapIds) {
            if (!mapIds || mapIds.length === 0) {
                view.graphics.removeAll();
                return;
            }

            // Создаем запрос для подсветки регионов
            const query = featureLayer.createQuery();
            query.where = `CBSAFP IN (${mapIds.map(id => `'${id}'`).join(",")})`;

            featureLayer.queryFeatures(query).then(function (result) {
                view.graphics.removeAll();

                result.features.forEach(feature => {
                    const highlightSymbol = {
                        type: "simple-fill",
                        color: [0, 0, 255, 0.3],
                        outline: {
                            color: [0, 0, 255],
                            width: 2
                        }
                    };

                    const graphic = new Graphic({
                        geometry: feature.geometry,
                        symbol: highlightSymbol
                    });

                    view.graphics.add(graphic);
                });
                console.log(`Regions with map IDs ${mapIds.join(", ")} highlighted.`);
            }).catch(function (error) {
                console.error("Error querying features for highlight:", error);
            });
        }

        // Синхронизация с селектором
        let selectedRegions = ["orlando-fl"]; // Orlando всегда активен
        const maxRegions = 5;

        function updateSelector(regionSlug, mapId, add) {
            const selectorContainer = $("#msa-custom-select .msa-selected-items");
            const placeholder = $("#msa-custom-select .msa-placeholder");
            const option = $(`.msa-option[data-slug="${regionSlug}"]`);

            if (add) {
                if (!selectedRegions.includes(regionSlug)) {
                    selectedRegions.push(regionSlug);

                    // Добавляем регион в селектор
                    const selectedItem = $("<span>")
                        .addClass("msa-selected-item")
                        .attr("data-slug", regionSlug)
                        .attr("data-map-id", mapId)
                        .text(option.text())
                        .on("click", function () {
                            updateSelector(regionSlug, mapId, false);
                        });

                    selectorContainer.append(selectedItem);
                    option.addClass("selected");

                    placeholder.hide();
                }
            } else {
                selectedRegions = selectedRegions.filter(slug => slug !== regionSlug);
                selectorContainer.find(`[data-slug="${regionSlug}"]`).remove();
                option.removeClass("selected");

                if (selectedRegions.length === 0) {
                    placeholder.show();
                }
            }

            updateTableColumns();
            const activeMapIds = selectedRegions.map(slug => $(`.msa-option[data-slug="${slug}"]`).data("map-id"));
            highlightRegions(activeMapIds);
        }

        // Обновление колонок таблицы
        function updateTableColumns() {
            $(".msa-region-column").each(function () {
                const slug = $(this).data("region-slug");
                $(this).toggle(selectedRegions.includes(slug));
            });
        }

        // Обработка клика в селекторе
        $(document).on("click", ".msa-option", function () {
            const regionSlug = $(this).data("slug");
            const mapId = $(this).data("map-id");
            const isSelected = selectedRegions.includes(regionSlug);

            if (!isSelected && selectedRegions.length >= maxRegions) {
                alert("You can select up to 5 locations.");
                return;
            }

            updateSelector(regionSlug, mapId, !isSelected);
        });

        // Обработка клика на карте
        view.on("click", async function (event) {
            const query = featureLayer.createQuery();
            query.geometry = event.mapPoint;
            query.returnGeometry = false;
            query.outFields = ["*"];

            try {
                const result = await featureLayer.queryFeatures(query);
                if (result.features.length > 0) {
                    const mapId = result.features[0].attributes.CBSAFP;
                    const region = msaMapData.regions.find(r => r.map_id == mapId);

                    if (region) {
                        if (selectedRegions.length >= maxRegions && !selectedRegions.includes(region.region_slug)) {
                            alert("You can select up to 5 locations.");
                            return;
                        }

                        updateSelector(region.region_slug, mapId, !selectedRegions.includes(region.region_slug));
                    }
                }
            } catch (error) {
                console.error("Error querying map region:", error);
            }
        });

        // Инициализация подсветки и таблицы
        view.when(() => {
            updateTableColumns();
            const activeMapIds = selectedRegions.map(slug => $(`.msa-option[data-slug="${slug}"]`).data("map-id"));
            highlightRegions(activeMapIds);
        });
    });
});
*/



jQuery(document).ready(function ($) {
    require([
        "esri/WebMap",
        "esri/views/MapView",
        "esri/layers/FeatureLayer",
        "esri/Graphic"
    ], function (WebMap, MapView, FeatureLayer, Graphic) {
        // Инициализация карты
        const webMap = new WebMap({
            portalItem: { id: msaMapData.portalItemId }
        });

        const view = new MapView({
            container: "viewDiv",
            map: webMap,
            zoom: 5,
            center: [-95, 37]
        });

        // Базовый слой со стилем
        const featureLayer = new FeatureLayer({
            url: msaMapData.featureLayerUrl,
            renderer: {
                type: "simple",
                symbol: {
                    type: "simple-fill",
                    color: [200, 200, 255, 0.1], // Тусклый голубой для всех регионов
                    outline: {
                        color: [100, 100, 200, 0.3],
                        width: 1
                    }
                }
            },
            popupEnabled: false
        });

        webMap.add(featureLayer);

        // Подсветка активных регионов
        function highlightRegions(activeMapIds) {
            console.log("Highlighting these Map IDs:", activeMapIds); // Проверка перед запросом

            if (!activeMapIds || activeMapIds.length === 0) {
                view.graphics.removeAll();
                return;
            }

            const query = featureLayer.createQuery();
            query.where = `CBSAFP IN (${activeMapIds.map(id => `'${id}'`).join(",")})`;
            query.returnGeometry = true;

            featureLayer.queryFeatures(query).then(function (result) {
                view.graphics.removeAll();

                result.features.forEach(feature => {
                    const highlightSymbol = {
                        type: "simple-fill",
                        color: [0, 0, 255, 0.6],
                        outline: {
                            color: [0, 0, 150],
                            width: 2
                        }
                    };

                    const graphic = new Graphic({
                        geometry: feature.geometry,
                        symbol: highlightSymbol
                    });

                    view.graphics.add(graphic);
                });

                console.log(`Successfully highlighted regions: ${activeMapIds.join(", ")}`);
            }).catch(function (error) {
                console.error("Error highlighting regions:", error);
            });
        }


        // Логика синхронизации
        let selectedRegions = [];
        const maxRegions = 5;

        function updateSelector(regionSlug, mapId, add) {
            const selectorContainer = $("#msa-custom-select .msa-selected-items");
            const placeholder = $("#msa-custom-select .msa-placeholder");
            const option = $(`.msa-option[data-slug="${regionSlug}"]`);

            if (add) {
                if (!selectedRegions.includes(regionSlug)) {
                    selectedRegions.push(regionSlug);

                    // Добавляем регион в селектор
                    const selectedItem = $("<span>")
                        .addClass("msa-selected-item")
                        .attr("data-slug", regionSlug)
                        .attr("data-map-id", mapId)
                        .text(option.text())
                        .on("click", () => updateSelector(regionSlug, mapId, false));

                    selectorContainer.append(selectedItem);
                    option.addClass("selected");

                    placeholder.hide();
                }
            } else {
                selectedRegions = selectedRegions.filter(slug => slug !== regionSlug);
                selectorContainer.find(`[data-slug="${regionSlug}"]`).remove();
                option.removeClass("selected");

                if (selectedRegions.length === 0) placeholder.show();
            }

            // Собираем активные map IDs
            const activeMapIds = selectedRegions
                .map(slug => $(`.msa-option[data-slug="${slug}"]`).data("map-id"))
                .filter(id => id !== undefined && id !== ""); // Проверяем на валидность

            console.log("Active Map IDs:", activeMapIds); // Отладка - проверяем IDs

            highlightRegions(activeMapIds); // Передаём актуальные ID для подсветки
        }


        // Обработка клика на карте
        view.on("click", async function (event) {
            const query = featureLayer.createQuery();
            query.geometry = event.mapPoint;
            query.returnGeometry = false;
            query.outFields = ["*"];

            try {
                const result = await featureLayer.queryFeatures(query);
                if (result.features.length > 0) {
                    const mapId = result.features[0].attributes.CBSAFP;
                    const region = msaMapData.regions.find(r => r.map_id == mapId);

                    if (region) {
                        updateSelector(region.region_slug, mapId, !selectedRegions.includes(region.region_slug));
                    }
                }
            } catch (error) {
                console.error("Error querying map region:", error);
            }
        });

        // Обработка клика в селекторе
        $(document).on("click", ".msa-option", function () {
            const regionSlug = $(this).data("slug");
            const mapId = $(this).data("map-id");
            updateSelector(regionSlug, mapId, !selectedRegions.includes(regionSlug));
        });

        view.when(() => {
            console.log("Custom base styles applied to the map.");
            const activeMapIds = selectedRegions.map(slug => $(`.msa-option[data-slug="${slug}"]`).data("map-id"));
            highlightRegions(activeMapIds);
        });
    });
});
