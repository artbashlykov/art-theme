<?php

/**

 * Customizer control — drag-and-drop layout order.

 *

 * @package Art_Theme

 */



defined( 'ABSPATH' ) || exit;



/**

 * Sortable list control for layout order settings.

 */

class Art_Theme_Customize_Layout_Order_Control extends WP_Customize_Control {



	/**

	 * Control type slug.

	 *

	 * @var string

	 */

	public $type = 'art_theme_layout_order';



	/**

	 * Sanitize callback for order values.

	 *

	 * @var callable|null

	 */

	public $sanitize_order_callback = null;



	/**

	 * Labels callback for order items.

	 *

	 * @var callable|null

	 */

	public $layout_labels_callback = null;



	/**

	 * Render control markup.

	 */

	protected function render_content() {

		if ( empty( $this->label ) ) {

			return;

		}



		$sanitize_callback = $this->sanitize_order_callback;



		if ( ! is_callable( $sanitize_callback ) ) {

			$sanitize_callback = array( 'Art_Theme_Single_Settings', 'sanitize_meta_order' );

		}



		$labels_callback = $this->layout_labels_callback;



		if ( ! is_callable( $labels_callback ) ) {

			$labels_callback = array( 'Art_Theme_Single_Settings', 'get_layout_item_labels' );

		}



		$value = $this->value();



		if ( is_string( $value ) ) {

			$value = array_map( 'trim', explode( ',', $value ) );

		}



		$order  = call_user_func( $sanitize_callback, $value );

		$labels = call_user_func( $labels_callback );

		?>

		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

		<?php if ( ! empty( $this->description ) ) : ?>

			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>

		<?php endif; ?>

		<div

			class="art-theme-layout-order-field art-theme-layout-order-field--customize"

			data-customize-setting="<?php echo esc_attr( $this->setting->id ); ?>"

		>

			<?php art_theme_render_layout_order_list( $order, $labels ); ?>

			<input

				type="hidden"

				class="art-theme-layout-order-value"

				value="<?php echo esc_attr( implode( ',', $order ) ); ?>"

			/>

		</div>

		<?php

	}

}


