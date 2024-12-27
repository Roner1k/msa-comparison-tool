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
                <label class="msa-checkbox"> <input type="checkbox"
                                                    id="msa-include-rank"><span>Include Rank</span></label>
            </div>


            <div class="msa-controls">
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
                        <a id="view-hidden-fields">View Hidden Fields</a>
                    </div>

                </div>
            </div>


            <?php
            // Сортируем регионы (пример, как у вас сделано)
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
                        <button class="msa-toggle-category" data-category="<?php echo esc_attr($category); ?>">
                            Toggle
                        </button>
                    </div>
                    <div class="msa-category-header">
                        <label class="msa-checkbox">
                            <input type="checkbox" class="msa-category-checkbox"
                                   data-category="<?php echo esc_attr($category); ?>"
                                   checked><span>Include in Download</span> </label>
                    </div>

                    <!-- Category Content -->
                    <div class="msa-category-content" style="display: none;">
                        <table class="msa-table">
                            <thead>
                            <tr>
                                <th><!--Indicator--></th>
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
                                <th class="hide-row-col"><!--Hide Row--></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $rowIndex = 0; ?>
                            <?php foreach ($indicators as $indicator):
                                // Пропускаем, если это 'Rank' — у вас уже логика выше
                                if (strpos($indicator, 'Rank') !== false) {
                                    continue;
                                }
                                // Для каждого региона мы можем проверить subcategories
                                // Но удобнее один раз получить "есть ли subcategories"
                                // из первого региона или из $data['regions'][...]
                                // Мы пойдем через $data['regions'][$regionName]['categories'][$category][$indicator]
                                // Условимся, что структура одинаковая.

                                // Возьмём "subcategories" из первого региона, если нужно,
                                // но корректнее пройтись по всем. Пример (ниже) возьмём первый:
                                $firstRegionName = array_key_first($data['regions']);
                                $indicatorData = $data['regions'][$firstRegionName]['categories'][$category][$indicator] ?? null;
                                $hasSubcategories = false;
                                if (isset($indicatorData['subcategories']) && !empty($indicatorData['subcategories'])) {
                                    $hasSubcategories = true;
                                }
                                ?>
                                <!-- Основная строка индикатора -->
                                <tr class="table-row"
                                    data-row-id="row-<?php echo esc_attr($category); ?>-<?php echo $rowIndex; ?>">
                                    <td>
                                        <?php
                                        // Если есть подкатегории, можно сделать плюсик для раскрытия
                                        if ($hasSubcategories): ?>
                                            <span class="toggle-subcategories"
                                                  data-indicator="<?php echo esc_attr($indicator); ?>">
                                                ➕
                                            </span>
                                        <?php endif; ?>
                                        <?php echo esc_html($indicator); ?>
                                    </td>

                                    <!-- Выводим ячейки для регионов -->
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

                                <?php
                                // Если у индикатора есть подкатегории, выводим отдельные строки
                                if ($hasSubcategories):
                                    // Теперь, чтобы корректно вывести значения подкатегорий
                                    // нам нужно пройтись по списку subcategories, но у всех регионов.
                                    // Один из подходов:
                                    // 1) Собираем полный список subcat'ов из всех регионов (union).
                                    $allSubcats = [];
                                    foreach ($data['regions'] as $rgName => $rgData) {
                                        $sc = $rgData['categories'][$category][$indicator]['subcategories'] ?? [];
                                        $allSubcats = array_merge($allSubcats, array_keys($sc));
                                    }
                                    $allSubcats = array_unique($allSubcats);

                                    // 2) Для каждого subcat делаем новую строку
                                    foreach ($allSubcats as $subcatName):
                                        $subRowIndex = $rowIndex . '-sc-' . sanitize_title($subcatName);
                                        ?>
                                        <tr class="table-row msa-subcategory-row"
                                            data-parent-indicator="<?php echo esc_attr($indicator); ?>"
                                            data-subcat-row-id="row-<?php echo esc_attr($category); ?>-<?php echo esc_attr($subRowIndex); ?>"
                                            style="display: none;">
                                            <!-- Первый столбец: Название subcategory -->
                                            <td>
                                                — <?php echo esc_html($subcatName); ?>
                                            </td>

                                            <!-- Далее: ячейки по регионам -->
                                            <?php foreach ($data['regions'] as $rgName => $rgData): ?>
                                                <?php
                                                $subValue = $rgData['categories'][$category][$indicator]['subcategories'][$subcatName] ?? '-';
                                                $subRank = '-'; // Если у подкатегорий тоже бывают rank, можно аналогично хранить
                                                ?>
                                                <td class="msa-region-column"
                                                    data-region-slug="<?php echo esc_attr($rgData['slug']); ?>">
                                                    <?php echo esc_html($subValue); ?>
                                                </td>
                                                <td class="msa-region-column msa-rank-column hidden"
                                                    data-region-slug="<?php echo esc_attr($rgData['slug']); ?>">
                                                    <?php echo esc_html($subRank); ?>
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
