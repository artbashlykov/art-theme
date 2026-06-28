<?php
/**
 * Page template.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', get_post_type() );
endwhile;

get_footer();
