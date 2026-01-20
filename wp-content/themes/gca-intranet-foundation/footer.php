<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<footer role="contentinfo">
	<?php
	wp_nav_menu([
		'theme_location' => 'footer',
		'container'      => false,
	]);
	?>
</footer>

<?php wp_footer(); ?>
</body>
</html>