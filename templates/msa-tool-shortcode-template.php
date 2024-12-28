<?php if (!empty($data)): ?>
    <div id="msa-tool-container">

        <!-- Map Section -->
        <div id="msa-tool-map">
            <div id="viewDiv" style="width: 100%; height: 400px;"></div>
        </div>

        <!-- Location Selector -->
        <div id="msa-tool-location-selector">
            <label>Select locations to compare:</label>
            <div id="msa-custom-select" class="msa-custom-select">
                <div class="msa-selected-items">
                    <span class="msa-placeholder">Choose up to 5 locations</span>
                    <span id="msa-add-location" class="msa-add-location">+ Add Location</span>
                </div>
                <ul class="msa-options">
                    <li>
                        <input type="text" id="msa-search-input" placeholder="Search locations..."/>
                    </li>
                    <?php foreach ($data['regions'] as $region_name => $region_data): ?>
                        <li
                                class="msa-option"
                                data-slug="<?php echo esc_attr($region_data['slug']); ?>"
                                data-map-id="<?php echo esc_attr($region_data['map_id'] ?? ''); ?>"
                        >
                            <?php echo esc_html($region_name); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Table Section -->
        <div id="msa-tool-content">
            <!-- Include Rank Checkbox -->
            <div class="msa-rank-toggle">
                <label class="msa-checkbox">
                    <input type="checkbox" id="msa-include-rank">
                    <span>Include Rank</span>
                </label>
            </div>

            <div class="msa-controls">
                <!-- Download Buttons -->
                <div class="msa-control-downloads">
                    <h4>Download Report</h4>
                    <div class="msa-buttons">
                        <button id="export-pdf">Export to PDF</button>
                        <button id="export-xlsx">Export to XLSX</button>
                    </div>
                </div>

                <!-- Toggle All Button -->
                <div class="msa-control-btns">
                    <div class="msa-toggle-all-container">
                        <button id="msa-toggle-all">Toggle All</button>
                    </div>
                    <div>
                        <a id="msa-view-hidden-fields">View Hidden Fields</a>
                    </div>
                </div>
            </div>

            <?php
            // Sort regions, prioritizing "Orlando, FL"
            uksort($data['regions'], function ($regionA, $regionB) {
                if ($regionA === 'Orlando, FL') return -1;
                if ($regionB === 'Orlando, FL') return 1;
                return strcasecmp($regionA, $regionB);
            });
            ?>

            <?php foreach ($data['categories'] as $category => $indicators): ?>
                <div class="msa-category">
                    <!-- Category Header -->
                    <div class="msa-category-header">
                        <h3><?php echo esc_html($category); ?></h3>
                        <button class="msa-toggle-category" data-category="<?php echo esc_attr($category); ?>">Toggle</button>
                    </div>
                    <div class="msa-category-header">
                        <label class="msa-checkbox">
                            <input type="checkbox" class="msa-category-checkbox"
                                   data-category="<?php echo esc_attr($category); ?>" checked>
                            <span>Include in Download</span>
                        </label>
                    </div>

                    <!-- Category Content -->
                    <div class="msa-category-content" style="display: none;">
                        <table class="msa-table">
                            <thead>
                            <tr>
                                <th></th>
                                <?php foreach ($data['regions'] as $region_name => $region_data): ?>
                                    <th class="msa-region-column"
                                        data-region-slug="<?php echo esc_attr($region_data['slug']); ?>">
                                        <?php echo esc_html($region_name); ?>
                                    </th>
                                    <th class="msa-region-column msa-rank-column hidden"
                                        data-region-slug="<?php echo esc_attr($region_data['slug']); ?>">
                                        Rank
                                    </th>
                                <?php endforeach; ?>
                                <th class="hide-row-col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $rowIndex = 0; ?>
                            <?php foreach ($indicators as $indicator): ?>
                                <?php if (strpos($indicator, 'Rank') !== false) continue; ?>

                                <?php
                                // Check if the indicator has subcategories
                                $firstRegionName = array_key_first($data['regions']);
                                $indicatorData = $data['regions'][$firstRegionName]['categories'][$category][$indicator] ?? null;
                                $hasSubcategories = isset($indicatorData['subcategories']) && !empty($indicatorData['subcategories']);
                                ?>
                                <!-- Main Indicator Row -->
                                <tr class="table-row"
                                    data-row-id="row-<?php echo esc_attr($category); ?>-<?php echo $rowIndex; ?>"
                                    data-has-subcategories="<?php echo $hasSubcategories ? 'true' : 'false'; ?>">
                                    <td><?php echo esc_html($indicator); ?></td>

                                    <?php foreach ($data['regions'] as $region_name => $region_data): ?>
                                        <?php
                                        $val = $region_data['categories'][$category][$indicator]['value'] ?? '-';
                                        $rank = $region_data['categories'][$category][$indicator]['rank'] ?? '-';
                                        ?>
                                        <td class="msa-region-column"
                                            data-region-slug="<?php echo esc_attr($region_data['slug']); ?>">
                                            <?php echo esc_html($val); ?>
                                        </td>
                                        <td class="msa-region-column msa-rank-column hidden"
                                            data-region-slug="<?php echo esc_attr($region_data['slug']); ?>">
                                            <?php echo esc_html($rank); ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td class="hide-row-col">
                                        <button class="hide-row-btn"
                                                data-row-id="row-<?php echo esc_attr($category); ?>-<?php echo $rowIndex; ?>">
                                            ×
                                        </button>
                                    </td>
                                </tr>

                                <?php if ($hasSubcategories): ?>
                                    <?php
                                    // Collect all subcategories across regions
                                    $allSubcats = [];
                                    foreach ($data['regions'] as $rgName => $rgData) {
                                        $sc = $rgData['categories'][$category][$indicator]['subcategories'] ?? [];
                                        $allSubcats = array_merge($allSubcats, array_keys($sc));
                                    }
                                    $allSubcats = array_unique($allSubcats);
                                    ?>

                                    <?php foreach ($allSubcats as $subcatName): ?>
                                        <?php $subRowIndex = $rowIndex . '-sc-' . sanitize_title($subcatName); ?>
                                        <tr class="table-row msa-subcategory-row"
                                            data-parent-row-id="row-<?php echo esc_attr($category); ?>-<?php echo $rowIndex; ?>"
                                            style="display: none;">
                                            <td>— <?php echo esc_html($subcatName); ?></td>
                                            <?php foreach ($data['regions'] as $rgName => $rgData): ?>
                                                <?php
                                                $subValue = $rgData['categories'][$category][$indicator]['subcategories'][$subcatName] ?? '-';
                                                ?>
                                                <td class="msa-region-column"
                                                    data-region-slug="<?php echo esc_attr($rgData['slug']); ?>">
                                                    <?php echo esc_html($subValue); ?>
                                                </td>
                                                <td class="msa-region-column msa-rank-column hidden"
                                                    data-region-slug="<?php echo esc_attr($rgData['slug']); ?>">
                                                    -
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="hide-row-col">
                                                <button class="hide-row-btn"
                                                        data-row-id="row-<?php echo esc_attr($category); ?>-<?php echo esc_attr($subRowIndex); ?>">
                                                    ×
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php $rowIndex++; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <p>No data available.</p>
<?php endif; ?>
