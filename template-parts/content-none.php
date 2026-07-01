<?php
/**
 * Empty content state.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="art-theme-no-results">
	<?php if ( is_search() ) : ?>
		<h1 class="art-theme-page-title"><?php esc_html_e( 'Ничего не найдено', 'art-theme' ); ?></h1>
		<p><?php esc_html_e( 'По вашему запросу ничего не найдено.', 'art-theme' ); ?></p>
	<?php else : ?>
		<h1 class="art-theme-page-title"><?php esc_html_e( 'Записей пока нет', 'art-theme' ); ?></h1>
		<p><?php esc_html_e( 'Когда появятся публикации, они отобразятся здесь.', 'art-theme' ); ?></p>
	<?php endif; ?>
</section>
