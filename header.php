<?php
/**
 * Document head and layout shell (open).
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="art-theme-canvas<?php echo esc_attr( art_theme_get_canvas_modifier_class() ); ?>">
	<?php art_theme_render_site_header(); ?>
	<div class="art-theme-shell<?php echo esc_attr( art_theme_get_shell_modifier_class() ); ?>">
		<main id="content" class="art-theme-main">
