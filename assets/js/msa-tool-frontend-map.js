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
            zoom: 3,
            center: [-100, 39]
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
                            color: [0, 0, 255, 0.3],
                            outline: {color: [0, 0, 255], width: 1}
                        },
                        attributes: {
                            mapId: mapIdStr
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
                        outline: {color: [0, 0, 0], width: 1}
                    };
                } else {
                    // Blue for not selected
                    graphic.symbol = {
                        type: "simple-fill",
                        color: [245, 222, 179, 0.5],
                        outline: {color: [100, 100, 100], width: 1}
                    };
                }
            }
        }


        function updateSelector(regionSlug, mapId, add) {
            const mapIdStr = String(mapId);
            const selectorContainer = $("#msa-custom-select .msa-selected-items");
            const placeholder = $("#msa-custom-select .msa-placeholder");
            const option = $(`.msa-option[data-slug="${regionSlug}"]`);

            if (regionSlug === alwaysActiveRegion && !add) return;

            if (add) {
                if (!selectedRegions.includes(regionSlug)) {
                    const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
                    if (otherSelectedCount >= maxRegions) return;

                    selectedRegions.push(regionSlug);

                    const selectedItem = $("<span>")
                        .addClass("msa-selected-item")
                        .attr("data-slug", regionSlug)
                        .text(option.text())
                        .on("click", () => updateSelector(regionSlug, mapIdStr, false));

                    selectorContainer.append(selectedItem);
                    option.addClass("selected");
                    placeholder.hide();
                }
            } else {
                selectedRegions = selectedRegions.filter(slug => slug !== regionSlug);
                selectorContainer.find(`[data-slug="${regionSlug}"]`).remove();
                option.removeClass("selected");
                if (selectedRegions.length === 1 && selectedRegions[0] === alwaysActiveRegion) placeholder.show();
            }

            updateTableColumns();
            updateAddLocationButton();
            const activeMapIds = selectedRegions.map(slug => String($(`.msa-option[data-slug="${slug}"]`).data("map-id")));
            updateRegionsColors(activeMapIds);
        }


        function updateTableColumns() {
            $(".msa-region-column").each(function () {
                const slug = $(this).data("region-slug");
                const isRankColumn = $(this).hasClass("msa-rank-column");

                if (selectedRegions.includes(slug)) {
                    $(this).css("display", "table-cell");
                } else {
                    $(this).css("display", "none");
                }
            });
        }

        function updateAddLocationButton() {
            const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
            if (otherSelectedCount >= maxRegions) {
                $("#msa-add-location").addClass("hidden");
            } else {
                $("#msa-add-location").removeClass("hidden");
            }
        }


        $(document).on("click", ".msa-option", function () {
            const regionSlug = $(this).data("slug");
            const mapId = String($(this).data("map-id"));
            const isSelected = selectedRegions.includes(regionSlug);

            // If not selected and we are at max (excluding Orlando), show alert
            if (!isSelected && regionSlug !== alwaysActiveRegion) {
                const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
                if (otherSelectedCount >= maxRegions) {
                    alert("You can select up to 5 additional locations.");
                    return;
                }
            }

            updateSelector(regionSlug, mapId, !isSelected);
        });

        // view.on("click", async function (event) {
        //     const query = featureLayer.createQuery();
        //     query.geometry = event.mapPoint;
        //     query.returnGeometry = false;
        //     query.outFields = ["*"];
        //
        //     try {
        //         const result = await featureLayer.queryFeatures(query);
        //         if (result.features.length > 0) {
        //             const mapId = String(result.features[0].attributes.CBSAFP);
        //             const region = msaMapData.regions.find(r => String(r.map_id) === mapId);
        //
        //             if (region) {
        //                 const regionSlug = region.region_slug;
        //
        //                 // Check if region is Orlando
        //                 if (regionSlug === alwaysActiveRegion) {
        //                     // Since Orlando can't be removed or toggled off, do nothing if already selected.
        //                     if (!selectedRegions.includes(alwaysActiveRegion)) {
        //                         // Shouldn't happen since we initialize with Orlando anyway.
        //                         selectedRegions.push(alwaysActiveRegion);
        //                         updateTableColumns();
        //                     }
        //                     // Just update colors (it should already be orange)
        //                     const activeMapIds = selectedRegions.map(slug => String($(`.msa-option[data-slug="${slug}"]`).data("map-id")));
        //                     updateRegionsColors(activeMapIds);
        //                     return;
        //                 }
        //
        //                 // Check max if trying to add
        //                 if (!selectedRegions.includes(regionSlug)) {
        //                     const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
        //                     if (otherSelectedCount >= maxRegions) {
        //                         alert("You can select up to 5 additional locations.");
        //                         return;
        //                     }
        //                 }
        //
        //                 updateSelector(regionSlug, mapId, !selectedRegions.includes(regionSlug));
        //             }
        //         }
        //     } catch (error) {
        //         console.error("Error querying map region:", error);
        //     }
        // });

        view.on("pointer-move", async (event) => {
            const response = await view.hitTest(event);
            if (response.results.some(r => r.graphic && r.graphic.layer === graphicsLayer)) {
                view.container.style.cursor = "pointer";
            } else {
                view.container.style.cursor = "default";
            }
        });

        view.when(() => {
            updateTableColumns();
            renderBaseRegions();
            $("#msa-tool-content .msa-category").find(".msa-toggle-category").click();
            $("#msa-include-rank").click();

            view.on("click", async function (event) {
                $("#map-loader").show();

                try {
                    const response = await view.hitTest(event);

                    if (response.results.length > 0) {
                        const hit = response.results.find(r => r.graphic && r.graphic.layer === graphicsLayer);
                        if (hit) {
                            const mapId = hit.graphic.attributes.mapId;
                            const region = msaMapData.regions.find(r => String(r.map_id) === mapId);

                            if (region) {
                                const regionSlug = region.region_slug;
                                if (regionSlug === alwaysActiveRegion) {
                                    if (!selectedRegions.includes(alwaysActiveRegion)) {
                                        selectedRegions.push(alwaysActiveRegion);
                                        updateTableColumns();
                                    }
                                    const activeMapIds = selectedRegions.map(slug =>
                                        String($(`.msa-option[data-slug="${slug}"]`).data("map-id"))
                                    );
                                    updateRegionsColors(activeMapIds);
                                    return;
                                }

                                if (!selectedRegions.includes(regionSlug)) {
                                    const otherSelectedCount = selectedRegions.filter(r => r !== alwaysActiveRegion).length;
                                    if (otherSelectedCount >= maxRegions) {
                                        alert("You can select up to 5 additional locations.");
                                        return;
                                    }
                                }
                                updateSelector(regionSlug, mapId, !selectedRegions.includes(regionSlug));
                            }
                        }
                    }
                } catch (error) {
                    console.error("Error on hitTest:", error);
                } finally {
                    $("#map-loader").hide();
                }
            });

        });


    });
});
