<?php
/**
 * Back-compat shim:
 * Some templates still load `template-parts/single/default-1col`.
 * The 1-col layout was moved to `template-layout-1col`.
 */
get_template_part('template-parts/single/template-layout-1col');