<?php
/**
 * Layout shell (close).
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;
?>

		</main>
	</div>

	<?php
	$art_theme_blank_template = function_exists( 'art_theme_is_blank_template_view' ) && art_theme_is_blank_template_view();

	if ( ! $art_theme_blank_template && Art_Theme_Footer_Settings::has_visible_content() ) :
		?>
		<div class="art-theme-canvas__flex-fill" aria-hidden="true"></div>
	<?php endif; ?>

	<?php if ( ! $art_theme_blank_template ) : ?>
		<?php art_theme_render_site_footer(); ?>
	<?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
