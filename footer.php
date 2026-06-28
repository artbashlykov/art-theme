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

	<?php if ( Art_Theme_Footer_Settings::has_visible_content() ) : ?>
		<div class="art-theme-canvas__flex-fill" aria-hidden="true"></div>
	<?php endif; ?>

	<?php art_theme_render_site_footer(); ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
