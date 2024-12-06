<?php if (!empty($data)): ?>
    <div id="msa-tool-container">

        <!-- Фильтры -->
        <div id="msa-tool-filters">
            <!-- Здесь можно будет добавить фильтры -->
        </div>

        <!-- Основной контент -->
        <div id="msa-tool-content">
            <?php foreach ($data['categories'] as $category => $indicators): ?>
                <div class="msa-category">
                    <!-- Заголовок категории -->
                    <div class="msa-category-header">
                        <h3>
                            <?php echo esc_html($category); ?>
                        </h3>
                        <button class="msa-toggle-category" data-category="<?php echo esc_attr($category); ?>">Toggle</button>
                    </div>

                    <!-- Таблица категории -->
                    <div class="msa-category-content" style="display: none;">
                        <table class="msa-table">
                            <thead>
                            <tr>
                                <th>Indicator</th>
                                <?php foreach ($data['regions'] as $region => $region_data): ?>
                                    <th><?php echo esc_html($region); ?></th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($indicators as $indicator): ?>
                                <tr>
                                    <td><?php echo esc_html($indicator); ?></td>
                                    <?php foreach ($data['regions'] as $region => $region_data): ?>
                                        <td>
                                            <?php echo isset($region_data[$category][$indicator])
                                                ? esc_html($region_data[$category][$indicator])
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
