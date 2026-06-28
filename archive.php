<?php
/**
 * Archive template.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

art_theme_render_archive_header();

if ( have_posts() ) :
	$blog_settings = Art_Theme_Blog_Settings::get();
	echo '<div class="art-theme-archive-grid">';

	while ( have_posts() ) :
		the_post();
		get_template_part(
			'template-parts/content',
			'archive',
			array(
				'blog_settings' => $blog_settings,
			)
		);
	endwhile;

	echo '</div>';

	art_theme_render_pagination();
else :
	get_template_part( 'template-parts/content', 'none' );
endif;

get_footer();
