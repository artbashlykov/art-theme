<?php
/**
 * Archive loop item — card layout.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

$settings = ( isset( $args['blog_settings'] ) && is_array( $args['blog_settings'] ) )
	? $args['blog_settings']
	: Art_Theme_Blog_Settings::get();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'art-theme-archive-card' ); ?>>
	<?php if ( $settings['show_thumbnail'] ) : ?>
		<?php get_template_part( 'template-parts/post', 'thumbnail' ); ?>
	<?php endif; ?>

	<div class="art-theme-archive-card__body">
		<?php if ( $settings['show_date'] || $settings['show_category'] || $settings['show_reading_time'] ) : ?>
			<div class="art-theme-archive-card__meta">
				<?php if ( $settings['show_category'] && has_category() ) : ?>
					<?php
					$categories = get_the_category();
					$category   = $categories[0] ?? null;
					?>
					<?php if ( $category instanceof WP_Term ) : ?>
						<a class="art-theme-archive-card__category" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $settings['show_date'] ) : ?>
					<?php if ( $settings['show_category'] && has_category() ) : ?>
						<span class="art-theme-archive-card__meta-sep" aria-hidden="true">&middot;</span>
					<?php endif; ?>
					<time class="art-theme-archive-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				<?php endif; ?>

				<?php if ( $settings['show_reading_time'] ) : ?>
					<?php if ( ( $settings['show_category'] && has_category() ) || $settings['show_date'] ) : ?>
						<span class="art-theme-archive-card__meta-sep" aria-hidden="true">&middot;</span>
					<?php endif; ?>
					<span class="art-theme-archive-card__reading-time">
						<?php echo esc_html( art_theme_get_reading_time_label( get_the_ID() ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h2 class="art-theme-archive-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<?php if ( $settings['show_excerpt'] ) : ?>
			<div class="art-theme-archive-card__excerpt">
				<?php the_excerpt(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $settings['show_read_button'] ) : ?>
			<a class="art-theme-archive-card__button" href="<?php the_permalink(); ?>">
				<?php echo esc_html( $settings['read_button_text'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</article>
