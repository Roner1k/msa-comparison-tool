<?php if (!empty($data)): ?>
<div id="msa-tool-container">

    <!-- Map Section -->
    <div id="msa-tool-map">
        <div id="viewDiv" style="width: 100%; height: 700px;"></div>
    </div>
    <?php //var_dump($data['regions']);?>
    <!-- Location Selector -->
    <div id="msa-tool-location-selector">
        <label>Select locations to compare:</label>
        <div id="msa-custom-select" class="msa-custom-select">
            <div class="msa-selected-items">
                <span class="msa-placeholder">Choose up to 5 locations</span>
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


        <!-- Table Section -->
        <div id="msa-tool-content">
            <?php foreach ($data['categories'] as $category => $indicators): ?>
                <div class="msa-category">

                    <!-- Category Header -->
                    <div class="msa-category-header">
                        <h3><?php echo esc_html($category); ?></h3>
                        <button class="msa-toggle-category" data-category="<?php echo esc_attr($category); ?>">Toggle
                        </button>
                    </div>

                    <!-- Category Content -->
                    <div class="msa-category-content" style="display: none;">
                        <table class="msa-table">
                            <thead>
                            <tr>
                                <th>Indicator</th>
                                <?php
                                // Сортируем регионы: Orlando, FL первым, остальные по алфавиту
                                uksort($data['regions'], function ($regionA, $regionB) {
                                    if ($regionA === 'Orlando, FL') return -1; // Orlando, FL всегда первый
                                    if ($regionB === 'Orlando, FL') return 1;  // Остальные регионы после
                                    return strcasecmp($regionA, $regionB);     // Алфавитная сортировка
                                });

                                // Выводим регионы в отсортированном порядке
                                foreach ($data['regions'] as $region_name => $region_data): ?>
                                    <th class="msa-region-column"
                                        data-region-slug="<?php echo esc_attr($region_data['slug']); ?>"
                                        style="<?php echo ($region_name === 'Orlando, FL') ? '' : 'display: none;'; ?>">
                                        <?php echo esc_html($region_name); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($indicators as $indicator): ?>
                                <tr>
                                    <td><?php echo esc_html($indicator); ?></td>
                                    <?php foreach ($data['regions'] as $region_name => $region_data): ?>
                                        <td class="msa-region-column"
                                            data-region-slug="<?php echo esc_attr($region_data['slug']); ?>"
                                            style="<?php echo ($region_data['slug'] === "orlando-fl") ? '' : 'display: none;'; ?>">
                                            <?php echo isset($region_data['categories'][$category][$indicator])
                                                ? esc_html($region_data['categories'][$category][$indicator])
                                                : '-'; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
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
