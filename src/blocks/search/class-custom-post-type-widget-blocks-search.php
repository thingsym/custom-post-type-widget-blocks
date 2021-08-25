<?php
/**
 * Custom Post Type Widget Blocks Search
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

/**
 * Core class Custom_Post_Type_Widget_Blocks_Search
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Search {
	/**
	 * Ensure that the ID attribute only appears in the markup once
	 *
	 * @since 1.0.2
	 *
	 * @static
	 * @access private
	 * @var int
	 */
	private static $block_id = 0;

	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );
	}

	/**
	 * register block_type from metadata
	 *
	 * @since 1.3.0
	 */
	public function register_block_type() {
		register_block_type(
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/search',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function render_callback( $attributes ) {
		static $instance_id = 0;

		// Older versions of the Search block defaulted the label and buttonText
		// attributes to `__( 'Search' )` meaning that many posts contain `<!--
		// wp:search /-->`. Support these by defaulting an undefined label and
		// buttonText to `__( 'Search' )`.
		$attributes = wp_parse_args(
			$attributes,
			array(
				'label'      => __( 'Search', 'custom-post-type-widget-blocks' ),
				'buttonText' => __( 'Search', 'custom-post-type-widget-blocks' ),
			)
		);

		$input_id        = 'wp-block-custom-post-type-widget-blocks-search__input-' . ++self::$block_id;
		$classnames      = $this->get_classnames( $attributes );
		$postType        = ( ! empty( $attributes['postType'] ) ) ? $attributes['postType'] : '';
		$show_label      = ( ! empty( $attributes['showLabel'] ) ) ? true : false;
		$use_icon_button = ( ! empty( $attributes['buttonUseIcon'] ) ) ? true : false;
		$show_input      = ( ! empty( $attributes['buttonPosition'] ) && 'button-only' === $attributes['buttonPosition'] ) ? false : true;
		$show_button     = ( ! empty( $attributes['buttonPosition'] ) && 'no-button' === $attributes['buttonPosition'] ) ? false : true;
		$label_markup    = '';
		$input_markup    = '';
		$button_markup   = '';
		$inline_styles   = $this->get_styles( $attributes );

		if ( $show_label ) {
			if ( ! empty( $attributes['label'] ) ) {
				$label_markup = sprintf(
					'<label for="%s" class="wp-block-custom-post-type-widget-blocks-search__label">%s</label>',
					$input_id,
					$attributes['label']
				);
			} else {
				$label_markup = sprintf(
					'<label for="%s" class="wp-block-custom-post-type-widget-blocks-search__label screen-reader-text">%s</label>',
					$input_id,
					__( 'Search', 'custom-post-type-widget-blocks' )
				);
			}
		}

		if ( $show_input ) {
			$input_markup = sprintf(
				'<input type="search" id="%s" class="wp-block-search__input" name="s" value="%s" placeholder="%s" %s required />',
				$input_id,
				esc_attr( get_search_query() ),
				esc_attr( $attributes['placeholder'] ),
				$inline_styles['shared']
			);
		}

		if ( $postType ) {
			$input_markup .= '<input type="hidden" name="post_type" value="' . $postType . '">';
		}

		if ( $show_button ) {
			$button_internal_markup = '';
			$button_classes         = '';

			if ( ! $use_icon_button ) {
				if ( ! empty( $attributes['buttonText'] ) ) {
					$button_internal_markup = $attributes['buttonText'];
				}
			} else {
				$button_classes        .= 'has-icon';
				$button_internal_markup =
					'<svg id="search-icon" class="search-icon" viewBox="0 0 24 24" width="24" height="24">
								<path d="M13.5 6C10.5 6 8 8.5 8 11.5c0 1.1.3 2.1.9 3l-3.4 3 1 1.1 3.4-2.9c1 .9 2.2 1.4 3.6 1.4 3 0 5.5-2.5 5.5-5.5C19 8.5 16.5 6 13.5 6zm0 9.5c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"></path>
						</svg>';
			}

			$button_markup = sprintf(
				'<button type="submit" class="wp-block-search__button %s"%s>%s</button>',
				$button_classes,
				$inline_styles['shared'],
				$button_internal_markup
			);
		}

		$field_markup       = sprintf(
			'<div class="wp-block-custom-post-type-widget-blocks-search__inside-wrapper"%s>%s</div>',
			$inline_styles['wrapper'],
			$input_markup . $button_markup
		);

		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classnames ) );

		return sprintf(
			'<form role="search" method="get" action="%s" %s>%s</form>',
			esc_url( home_url( '/' ) ),
			$wrapper_attributes,
			$label_markup . $field_markup
		);
	}

	/**
	 * Builds the correct top level classnames for the 'core/search' block.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The classnames used in the block.
	 */
	public function get_classnames( $attributes ) {
		$classnames = [];
		$classnames[] = 'wp-block-search';

		if ( ! empty( $attributes['buttonPosition'] ) ) {
			if ( 'button-inside' === $attributes['buttonPosition'] ) {
				$classnames[] = 'wp-block-search__button-inside';
			}

			if ( 'button-outside' === $attributes['buttonPosition'] ) {
				$classnames[] = 'wp-block-search__button-outside';
			}

			if ( 'no-button' === $attributes['buttonPosition'] ) {
				$classnames[] = 'wp-block-search__no-button';
			}

			if ( 'button-only' === $attributes['buttonPosition'] ) {
				$classnames[] = 'wp-block-search__button-only';
			}
		}

		if ( isset( $attributes['buttonUseIcon'] ) ) {
			if ( ! empty( $attributes['buttonPosition'] ) && 'no-button' !== $attributes['buttonPosition'] ) {
				if ( $attributes['buttonUseIcon'] ) {
					$classnames[] = 'wp-block-search__icon-button';
				} else {
					$classnames[] = 'wp-block-search__text-button';
				}
			}
		}

		return implode( ' ', $classnames );
	}

	/**
	 * Builds an array of inline styles for the search block.
	 *
	 * The result will contain one entry for shared styles such as those for the
	 * inner input or button and a second for the inner wrapper should the block
	 * be positioning the button "inside".
	 *
	 * @param  array $attributes The block attributes.
	 *
	 * @return array Style HTML attribute.
	 */
	public function get_styles( $attributes ) {
		$shared_styles  = array();
		$wrapper_styles = array();

		// Add width styles.
		$has_width   = ! empty( $attributes['width'] ) && ! empty( $attributes['widthUnit'] );
		$button_only = ! empty( $attributes['buttonPosition'] ) && 'button-only' === $attributes['buttonPosition'];

		if ( $has_width && ! $button_only ) {
			$wrapper_styles[] = sprintf(
				'width: %d%s;',
				esc_attr( $attributes['width'] ),
				esc_attr( $attributes['widthUnit'] )
			);
		}

		// Add border radius styles.
		$has_border_radius = ! empty( $attributes['style']['border']['radius'] );

		if ( $has_border_radius ) {
			// Shared style for button and input radius values.
			$border_radius   = $attributes['style']['border']['radius'];
			$border_radius   = is_numeric( $border_radius ) ? $border_radius . 'px' : $border_radius;
			$shared_styles[] = sprintf( 'border-radius: %s;', esc_attr( $border_radius ) );

			// Apply wrapper border radius if button placed inside.
			$button_inside = ! empty( $attributes['buttonPosition'] ) &&
				'button-inside' === $attributes['buttonPosition'];

			if ( $button_inside ) {
				// We adjust the border radius value for the outer wrapper element
				// to make it visually consistent with the radius applied to inner
				// elements. calc() is used to support non-pixel CSS units.
				$default_padding  = '4px';
				$wrapper_styles[] = sprintf(
					'border-radius: calc(%s + %s);',
					esc_attr( $border_radius ),
					esc_attr( $default_padding )
				);
			}
		}

		return array(
			'shared'  => ! empty( $shared_styles ) ? sprintf( ' style="%s"', implode( ' ', $shared_styles ) ) : '',
			'wrapper' => ! empty( $wrapper_styles ) ? sprintf( ' style="%s"', implode( ' ', $wrapper_styles ) ) : '',
		);
	}

}
