<?php

/**

 * Page loop item.

 *

 * @package Art_Theme

 */



defined( 'ABSPATH' ) || exit;



$settings     = Art_Theme_Page_Settings::get_for_singular();

$show_title   = art_theme_should_show_singular_entry_title();

$classes      = array( 'art-theme-post', 'art-theme-post--page' );



if ( Art_Theme_Page_Settings::is_full_width_template( $settings ) ) {

	$classes[] = 'art-theme-post--page-full';

} else {

	$classes[] = 'art-theme-post--page-boxed';

}



if ( ! $show_title ) {

	$classes[] = 'art-theme-post--page-no-title';

}

?>



<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>

	<div class="art-theme-page-body">

		<?php if ( $show_title ) : ?>

			<header class="art-theme-entry-header">

				<?php the_title( '<h1 class="art-theme-entry-title">', '</h1>' ); ?>

			</header>

		<?php endif; ?>



		<div class="art-theme-entry-content">

			<?php the_content(); ?>

		</div>

	</div>

</article>

