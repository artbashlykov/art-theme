<?php
/**
 * Default post loop item.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'art-theme-post' ); ?>>
	<?php if ( is_singular() ) : ?>
		<?php get_template_part( 'template-parts/post', 'thumbnail' ); ?>
	<?php endif; ?>

	<header class="art-theme-entry-header">
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="art-theme-entry-title">', '</h1>' );
		} else {
			the_title(
				'<h2 class="art-theme-entry-title"><a href="' . esc_url( get_permalink() ) . '">',
				'</a></h2>'
			);
		}
		?>
	</header>

	<div class="art-theme-entry-content">
		<?php
		if ( is_singular() ) {
			the_content();
		} else {
			the_excerpt();
		}
		?>
	</div>
</article>
