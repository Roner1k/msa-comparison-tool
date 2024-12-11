<?php if (!empty($data)): ?>
    <div id="msa-tool-container">

        <div id="msa-tool-map">
            <div id="viewDiv" style="width: 100%; height: 700px;">
            </div>

        </div>

        <div id="msa-tool-filters">
            <div class="map-item-list">
                <div class="map-item" data-object-id="1">Location 1</div>
                <div class="map-item" data-object-id="2">Location 2</div>
                <div class="map-item" data-object-id="3">Location 3</div>
            </div>

        </div>


        <div id="msa-tool-content">
            <?php foreach ($data['categories'] as $category => $indicators): ?>
                <div class="msa-category">

                    <div class="msa-category-header">
                        <h3>
                            <?php echo esc_html($category); ?>
                        </h3>
                        <button class="msa-toggle-category" data-category="<?php echo esc_attr($category); ?>">Toggle</button>
                    </div>


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
