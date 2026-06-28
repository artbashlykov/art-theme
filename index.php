<?php
/**
 * Main template fallback.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( art_theme_is_blog_archive_view() ) {
	art_theme_render_archive_header();
}

if ( have_posts() ) :
	if ( art_theme_is_blog_archive_view() ) {
		$blog_settings = Art_Theme_Blog_Settings::get();
		echo '<div class="art-theme-archive-grid">';
	}

	while ( have_posts() ) :
		the_post();
		if ( art_theme_is_blog_archive_view() ) {
			get_template_part(
				'template-parts/content',
				'archive',
				array(
					'blog_settings' => $blog_settings,
				)
			);
		} else {
			get_template_part( 'template-parts/content', get_post_type() );
		}
	endwhile;

	if ( art_theme_is_blog_archive_view() ) {
		echo '</div>';
	}

	art_theme_render_pagination();
else :
	get_template_part( 'template-parts/content', 'none' );
endif;

get_footer();
