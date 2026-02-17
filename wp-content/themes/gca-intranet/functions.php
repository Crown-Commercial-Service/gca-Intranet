<?php
if (!defined("ABSPATH")) { exit; }

add_action("wp_enqueue_scripts", function () {

  wp_enqueue_style(
    "gca-intranet-child",
    get_stylesheet_directory_uri() . "/style.css",
    [],
    filemtime(get_stylesheet_directory() . "/style.css")
  );

}, 999);
