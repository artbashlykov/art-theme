<?php
/**
 * Search results template.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<header class="art-theme-search-header">
	<h1 class="art-theme-page-title">
		<?php
		printf(
			/* translators: %s: search query. */
			esc_html__( 'Search Results for: %s', 'art-theme' ),
			esc_html( get_search_query() )
		);
		?>
	</h1>
</header>

<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', get_post_type() );
	endwhile;

	art_theme_render_pagination();
	?>
<?php else : ?>
	<?php get_template_part( 'template-parts/content', 'none' ); ?>
<?php endif; ?>

<?php
get_footer();
