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


// альтернативна карта з синх регыонами
/*
jQuery(document).ready(function ($) {
    require([
        "esri/WebMap",
        "esri/views/MapView",
        "esri/layers/FeatureLayer",
        "esri/layers/GraphicsLayer",
        "esri/Graphic"
    ], function (WebMap, MapView, FeatureLayer, GraphicsLayer, Graphic) {
        // Create a WHERE clause for the needed regions only
        const selectedMapIds = msaMapData.regions.map(r => `'${r.map_id}'`).join(",");
        const whereClause = `CBSAFP IN (${selectedMapIds})`;

        // Initialize the map with portal item
        const webMap = new WebMap({
            portalItem: { id: msaMapData.portalItemId }
        });

        const view = new MapView({
            container: "viewDiv",
            map: webMap,
            zoom: 5,
            center: [-95, 37]
        });

        // Create the feature layer and make it transparent
        const featureLayer = new FeatureLayer({
            url: msaMapData.featureLayerUrl,
            opacity: 0
        });

        // Create a separate graphics layer to render the needed regions
        const graphicsLayer = new GraphicsLayer();
        webMap.addMany([featureLayer, graphicsLayer]);

        // Render only the filtered regions
        function renderRegions() {
            graphicsLayer.removeAll();

            featureLayer.queryFeatures({
                where: whereClause,
                returnGeometry: true,
                outFields: ["GEOID"]
            }).then(function (result) {
                result.features.forEach(feature => {
                    // If the feature passes the filter, paint it blue
                    const fillColor = [0, 0, 255, 0.8]; // Blue with 0.8 opacity

                    const graphic = new Graphic({
                        geometry: feature.geometry,
                        symbol: {
                            type: "simple-fill",
                            color: fillColor,
                            outline: { color: [128, 128, 128, 0.6], width: 1 }
                        }
                    });

                    graphicsLayer.add(graphic);
                });
            }).catch(function (error) {
                console.error("Error querying features:", error);
            });
        }

        // Once the view is ready, render the filtered regions
        view.when(() => {
            renderRegions();
        });
    });
});
*/
// All comments in English
// This version ensures that Orlando (orlando-fl) is always active and cannot be removed.
// Orlando does not count towards the 5-region limit. The user can select up to 5 additional regions besides Orlando.
//
// Changes from previous version:
// - When initializing selectedRegions, "orlando-fl" is included and never removed.
// - In the updateSelector function, if the user attempts to remove "orlando-fl", we ignore that request.
// - When checking the limit of 5, we count only the other regions, not Orlando.

jQuery(document).ready(function ($) {
    require([
        "esri/WebMap",
        "esri/views/MapView",
        "esri/layers/FeatureLayer",
        "esri/layers/GraphicsLayer",
        "esri/Graphic"
    ], function (WebMap, MapView, FeatureLayer, GraphicsLayer, Graphic) {
        // Orlando is always active and cannot be removed
        const alwaysActiveRegion = "orlando-fl";
        let selectedRegions = [alwaysActiveRegion];

        // Max regions (not counting Orlando)
        const maxRegions = 5;

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
            url: msaMapData.featureLayerUrl,
            opacity: 0
        });

        const graphicsLayer = new GraphicsLayer();
        webMap.addMany([featureLayer, graphicsLayer]);

        const allMapIds = msaMapData.regions.map(r => r.map_id);
        const whereClause = `CBSAFP IN (${allMapIds.map(id => `'${id}'`).join(",")})`;

        const allRegionsGraphics = {};

        function renderBaseRegions() {
            graphicsLayer.removeAll();

            featureLayer.queryFeatures({
                where: whereClause,
                returnGeometry: true,
                outFields: ["CBSAFP"]
            }).then(function (result) {
                result.features.forEach(feature => {
                    const mapIdStr = String(feature.attributes.CBSAFP);

                    const graphic = new Graphic({
                        geometry: feature.geometry,
                        symbol: {
                            type: "simple-fill",
                            color: [0, 0, 255, 0.3], // Blue by default
                            outline: {color: [0, 0, 255], width: 1}
                        }
                    });

                    graphicsLayer.add(graphic);
                    allRegionsGraphics[mapIdStr] = graphic;
                });

                const activeMapIds = selectedRegions.map(slug => String($(`.msa-option[data-slug="${slug}"]`).data("map-id")));
                updateRegionsColors(activeMapIds);
            }).catch(function (error) {
                console.error("Error querying base regions:", error);
            });
        }

        // Update the colors of regions based on selection
        function updateRegionsColors(selectedMapIds) {
            const selectedSet = new Set(selectedMapIds);

            for (const mapId in allRegionsGraphics) {
                const graphic = allRegionsGraphics[mapId];
                if (selectedSet.has(mapId)) {
                    // Orange for selected
                    graphic.symbol = {
                        type: "simple-fill",
                        color: [255, 165, 0, 0.8], // Orange
                        outline: {color: [255, 165, 0], width: 2}
                    };
                } else {
                    // Blue for not selected
                    graphic.symbol = {
                        type: "simple-fill",
                        color: [0, 0, 255, 0.3],
                        outline: {color: [0, 0, 255], width: 1}
                    };
                }
            }
        }

        function updateSelector(regionSlug, mapId, add) {
            const mapIdStr = String(mapId);
            const selectorContainer = $("#msa-custom-select .msa-selected-items");
            const placeholder = $("#msa-custom-select .msa-placeholder");
            const option = $(`.msa-option[data-slug="${regionSlug}"]`);

            // If trying to remove Orlando, do nothing
            if (regionSlug === alwaysActiveRegion && !add) {
                return;
            }

            if (add) {
                if (!selectedRegions.includes(regionSlug)) {
                    // Check limit: excluding Orlando
                    const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
                    if (otherSelectedCount >= maxRegions) {
                        alert("You can select up to 5 additional locations besides Orlando.");
                        return;
                    }

                    selectedRegions.push(regionSlug);

                    const selectedItem = $("<span>")
                        .addClass("msa-selected-item")
                        .attr("data-slug", regionSlug)
                        .attr("data-map-id", mapIdStr)
                        .text(option.text())
                        .on("click", function () {
                            updateSelector(regionSlug, mapIdStr, false);
                        });

                    selectorContainer.append(selectedItem);
                    option.addClass("selected");

                    placeholder.hide();
                }
            } else {
                // Removing a region other than Orlando
                selectedRegions = selectedRegions.filter(slug => slug !== regionSlug);
                selectorContainer.find(`[data-slug="${regionSlug}"]`).remove();
                option.removeClass("selected");

                if (selectedRegions.length === 1 && selectedRegions[0] === alwaysActiveRegion) {
                    placeholder.show();
                }
            }

            updateTableColumns();
            const activeMapIds = selectedRegions.map(slug => String($(`.msa-option[data-slug="${slug}"]`).data("map-id")));
            updateRegionsColors(activeMapIds);
        }

        function updateTableColumns() {
            $(".msa-region-column").each(function () {
                const slug = $(this).data("region-slug");
                $(this).toggle(selectedRegions.includes(slug));
            });
        }

        $(document).on("click", ".msa-option", function () {
            const regionSlug = $(this).data("slug");
            const mapId = String($(this).data("map-id"));
            const isSelected = selectedRegions.includes(regionSlug);

            // If not selected and we are at max (excluding Orlando), show alert
            if (!isSelected && regionSlug !== alwaysActiveRegion) {
                const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
                if (otherSelectedCount >= maxRegions) {
                    alert("You can select up to 5 additional locations besides Orlando.");
                    return;
                }
            }

            updateSelector(regionSlug, mapId, !isSelected);
        });

        view.on("click", async function (event) {
            const query = featureLayer.createQuery();
            query.geometry = event.mapPoint;
            query.returnGeometry = false;
            query.outFields = ["*"];

            try {
                const result = await featureLayer.queryFeatures(query);
                if (result.features.length > 0) {
                    const mapId = String(result.features[0].attributes.CBSAFP);
                    const region = msaMapData.regions.find(r => String(r.map_id) === mapId);

                    if (region) {
                        const regionSlug = region.region_slug;

                        // Check if region is Orlando
                        if (regionSlug === alwaysActiveRegion) {
                            // Since Orlando can't be removed or toggled off, do nothing if already selected.
                            if (!selectedRegions.includes(alwaysActiveRegion)) {
                                // Shouldn't happen since we initialize with Orlando anyway.
                                selectedRegions.push(alwaysActiveRegion);
                                updateTableColumns();
                            }
                            // Just update colors (it should already be orange)
                            const activeMapIds = selectedRegions.map(slug => String($(`.msa-option[data-slug="${slug}"]`).data("map-id")));
                            updateRegionsColors(activeMapIds);
                            return;
                        }

                        // Check max if trying to add
                        if (!selectedRegions.includes(regionSlug)) {
                            const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
                            if (otherSelectedCount >= maxRegions) {
                                alert("You can select up to 5 additional locations besides Orlando.");
                                return;
                            }
                        }

                        updateSelector(regionSlug, mapId, !selectedRegions.includes(regionSlug));
                    }
                }
            } catch (error) {
                console.error("Error querying map region:", error);
            }
        });

        view.when(() => {
            updateTableColumns();
            renderBaseRegions();
        });
    });
});
