<?php
/**
 * Featured / list image output.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

$post_id         = get_the_ID();
$is_single_post  = art_theme_is_single_post_view();
$is_archive_card = art_theme_is_blog_archive_view() && ! $is_single_post;
$image_id        = $is_archive_card ? art_theme_get_archive_post_image_id( $post_id ) : art_theme_get_post_image_id( $post_id );

if ( ! $image_id && ! $is_archive_card ) {
	return;
}

$size    = $is_single_post ? 'large' : 'medium_large';
$class   = $is_single_post ? 'art-theme-post__featured' : 'art-theme-post__thumb';
$wrapper = 'art-theme-post__thumbnail';

if ( $is_single_post ) {
	$wrapper .= ' art-theme-post__thumbnail--single';
	$single_settings = Art_Theme_Single_Settings::get();

	if ( 'original' === $single_settings['cover_aspect_ratio'] ) {
		$wrapper .= ' art-theme-post__thumbnail--original';
	}
} elseif ( $is_archive_card ) {
	$wrapper .= ' art-theme-post__thumbnail--archive';
}
?>

<div class="<?php echo esc_attr( $wrapper ); ?>">
	<?php if ( $is_single_post ) : ?>
		<?php
		echo wp_get_attachment_image(
			$image_id,
			$size,
			false,
			array(
				'class'         => $class,
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'sizes'         => '(max-width: 850px) 100vw, 850px',
			)
		);
		?>
	<?php elseif ( $is_archive_card ) : ?>
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true" class="art-theme-post__thumbnail-link">
			<?php if ( $image_id ) : ?>
				<?php
				echo wp_get_attachment_image(
					$image_id,
					$size,
					false,
					array(
						'class'    => $class,
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width: 640px) 100vw, 50vw',
					)
				);
				?>
			<?php else : ?>
				<span class="art-theme-post__thumbnail-placeholder" aria-hidden="true"></span>
			<?php endif; ?>
		</a>
	<?php else : ?>
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo wp_get_attachment_image(
				$image_id,
				$size,
				false,
				array(
					'class'    => $class,
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			?>
		</a>
	<?php endif; ?>
</div>
