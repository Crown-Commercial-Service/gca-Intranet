<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<a class="skip-link" href="#main">
	<?php esc_html_e('Skip to main content', 'gca-intranet'); ?>
</a>

<header role="banner">
	<nav role="navigation" aria-label="<?php esc_attr_e('Primary navigation', 'gca-intranet'); ?>">
		<?php
		wp_nav_menu([
			'theme_location' => 'primary',
			'container'      => false,
		]);
		?>
	</nav>
</header>