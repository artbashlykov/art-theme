<?php
/**
 * Single post loop item.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

$settings = Art_Theme_Single_Settings::get();
$classes  = array( 'art-theme-post', 'art-theme-post--single' );

if ( Art_Theme_Single_Settings::is_full_width_template( $settings ) ) {
	$classes[] = 'art-theme-post--single-full';
} else {
	$classes[] = 'art-theme-post--single-boxed';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<div class="art-theme-single-body">
		<?php art_theme_render_single_entry_header( $settings ); ?>

		<div class="art-theme-entry-content">
			<?php the_content(); ?>
		</div>
	</div>
</article>
