<?php

/**

 * Single post template.

 *

 * @package Art_Theme

 */



defined( 'ABSPATH' ) || exit;



get_header();



while ( have_posts() ) :

	the_post();



	$post_type = get_post_type();



	if ( 'post' === $post_type ) {

		get_template_part( 'template-parts/content', 'post' );

	} elseif ( art_theme_uses_page_template_layout( $post_type ) ) {

		get_template_part( 'template-parts/content', 'page' );

	} else {

		get_template_part( 'template-parts/content', $post_type );

	}

endwhile;



get_footer();

