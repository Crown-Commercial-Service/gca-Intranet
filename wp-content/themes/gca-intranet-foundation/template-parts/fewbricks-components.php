<div class="govuk-grid-row">
    <div class="govuk-grid-column-full gca-radius-bordered">
        <?php
        /**
         * Reusable Component Loader: fewbricks-components.php
         *
         * Supports full-width and 50/50 layout per component via the
         * 'layout_width' field added in project_brick::set_project_fields().
         * Consecutive half-width components are paired into a two-column row.
         * An unpaired half (odd one out) renders alone at half width.
         */
        $rows = get_field('page_components_rows');

        if ($rows) :
            $half_buffer = [];

            $flush_half_buffer = function() use (&$half_buffer) {
                if (empty($half_buffer)) {
                    return;
                }
                echo '<div class="govuk-grid-row gca-component-row--50-50">';
                foreach ($half_buffer as $buffered) {
                    $row = $buffered['row'];
                    echo '<div class="govuk-grid-column-one-half">';
                    if (locate_template($buffered['file'])) {
                        include(locate_template($buffered['file']));
                    }
                    echo '</div>';
                }
                echo '</div>';
                $half_buffer = [];
            };

            foreach ($rows as $row) :
                $layout        = $row['acf_fc_layout'];
                $template_name = str_replace('_', '-', explode('_', $layout)[0]);
                $template_file = "fewbricks-templates/{$template_name}.php";
                $width         = $row[$layout . '_layout_width'] ?? 'full';

                if ($width === 'half') {
                    $half_buffer[] = ['row' => $row, 'file' => $template_file];

                    if (count($half_buffer) === 2) {
                        $flush_half_buffer();
                    }
                } else {
                    $flush_half_buffer();

                    if (locate_template($template_file)) {
                        include(locate_template($template_file));
                    }
                }
            endforeach;

            // Flush any unpaired half-width component
            $flush_half_buffer();
        endif;
        ?>
    </div>
</div>
