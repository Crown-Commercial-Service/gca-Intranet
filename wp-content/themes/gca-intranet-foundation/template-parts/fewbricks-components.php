<div class="govuk-grid-row">
    <div class="govuk-grid-column-full gca-radius-bordered">
        <?php
        /**
         * Reusable Component Loader: fewbricks-components.php
         */
        $rows = get_field('page_components_rows');

        if ($rows) :
            foreach ($rows as $row) :
                $layout = $row['acf_fc_layout'];

                // 1. Clean the string: 'accordion_accordion' becomes 'accordion'
                // This assumes your naming convention is always 'name_name'
                $template_name = str_replace('_', '-', explode('_', $layout)[0]);
                
                // 2. Locate the file (e.g., fewbricks-templates/accordion.php)
                $template_file = "fewbricks-templates/{$template_name}.php";

                // 3. Check if it exists before including to prevent fatal errors
                if ( locate_template($template_file) ) {
                    include(locate_template($template_file));
                } else {
                    // Optional: Helpful debug message for developers
                    echo "";
                }
            endforeach;
        endif;
        ?>
    </div>
</div>
