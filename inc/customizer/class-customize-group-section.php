<?php
/**
 * Non-expandable Customizer group heading section.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a root-level group label between real panels/sections.
 */
class Art_Theme_Customize_Group_Section extends WP_Customize_Section {

	/**
	 * Section type slug for JS sectionConstructor.
	 *
	 * @var string
	 */
	public $type = 'art_theme_group';

	/**
	 * Whether to draw a divider above this group heading.
	 *
	 * @var bool
	 */
	public $divider_before = false;

	/**
	 * Export data to JS.
	 *
	 * @return array<string, mixed>
	 */
	public function json() {
		$json                   = parent::json();
		$json['dividerBefore'] = (bool) $this->divider_before;

		return $json;
	}
}
