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
			[
				'label'      => __( 'Search', 'custom-post-type-widget-blocks' ),
				'buttonText' => __( 'Search', 'custom-post-type-widget-blocks' ),
			]
		);

		$input_id        = 'wp-block-custom-post-type-widget-blocks-search__input-' . ( ++self::$block_id );
		$classnames      = $this->get_classnames( $attributes );
		$post_type        = ( ! empty( $attributes['postType'] ) ) ? $attributes['postType'] : '';
		$show_label      = ( ! empty( $attributes['showLabel'] ) ) ? true : false;
		$use_icon_button = ( ! empty( $attributes['buttonUseIcon'] ) ) ? true : false;
		$show_input      = ( ! empty( $attributes['buttonPosition'] ) && 'button-only' === $attributes['buttonPosition'] ) ? false : true;
		$show_button     = ( ! empty( $attributes['buttonPosition'] ) && 'no-button' === $attributes['buttonPosition'] ) ? false : true;
		$label_markup    = '';
		$input_markup    = '';
		$button_markup   = '';
		$inline_styles   = $this->get_styles( $attributes );

		$color_classes    = $this->get_color_classes( $attributes );
		$is_button_inside = ! empty( $attributes['buttonPosition'] ) &&
			'button-inside' === $attributes['buttonPosition'];
		// Border color classes need to be applied to the elements that have a border color.
		$border_color_classes = $this->get_border_color_classes( $attributes );

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
			$input_classes = ! $is_button_inside ? $border_color_classes : '';
			$input_markup = sprintf(
				'<input type="search" id="%s" class="wp-block-search__input %s" name="s" value="%s" placeholder="%s" %s required />',
				$input_id,
				esc_attr( $input_classes ),
				esc_attr( get_search_query() ),
				esc_attr( $attributes['placeholder'] ),
				$inline_styles['input']
			);
		}

		if ( $post_type ) {
			$input_markup .= '<input type="hidden" name="post_type" value="' . $post_type . '">';
		}

		if ( $show_button ) {
			$button_internal_markup = '';
			$button_classes         = $color_classes;

			if ( ! $is_button_inside ) {
				$button_classes .= ' ' . $border_color_classes;
			}

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
				esc_attr( $button_classes ),
				$inline_styles['button'],
				$button_internal_markup
			);
		}

		$field_markup_classes = $is_button_inside ? $border_color_classes : '';
		$field_markup       = sprintf(
			'<div class="wp-block-custom-post-type-widget-blocks-search__inside-wrapper %s" %s>%s</div>',
			esc_attr( $field_markup_classes ),
			$inline_styles['wrapper'],
			$input_markup . $button_markup
		);

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => $classnames ] );

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
		$wrapper_styles   = [];
		$button_styles    = [];
		$input_styles     = [];
		$is_button_inside = ! empty( $attributes['buttonPosition'] ) &&
			'button-inside' === $attributes['buttonPosition'];

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

		// Add border width styles.
		$has_border_width = ! empty( $attributes['style']['border']['width'] );

		if ( $has_border_width ) {
			$border_width = $attributes['style']['border']['width'];

			if ( $is_button_inside ) {
				$wrapper_styles[] = sprintf( 'border-width: %s;', esc_attr( $border_width ) );
			} else {
				$button_styles[] = sprintf( 'border-width: %s;', esc_attr( $border_width ) );
				$input_styles[]  = sprintf( 'border-width: %s;', esc_attr( $border_width ) );
			}
		}

		// Add border radius styles.
		$has_border_radius = ! empty( $attributes['style']['border']['radius'] );

		if ( $has_border_radius ) {
			$default_padding = '4px';
			$border_radius   = $attributes['style']['border']['radius'];

			if ( is_array( $border_radius ) ) {
				// Apply styles for individual corner border radii.
				foreach ( $border_radius as $key => $value ) {
					if ( null !== $value ) {
						// Convert camelCase key to kebab-case.
						$name = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $key ) );

						// Add shared styles for individual border radii for input & button.
						$border_style    = sprintf(
							'border-%s-radius: %s;',
							esc_attr( $name ),
							esc_attr( $value )
						);
						$input_styles[]  = $border_style;
						$button_styles[] = $border_style;

						// Add adjusted border radius styles for the wrapper element
						// if button is positioned inside.
						if ( $is_button_inside && intval( $value ) !== 0 ) {
							$wrapper_styles[] = sprintf(
								'border-%s-radius: calc(%s + %s);',
								esc_attr( $name ),
								esc_attr( $value ),
								$default_padding
							);
						}
					}
				}
			} else {
				// Numeric check is for backwards compatibility purposes.
				$border_radius   = is_numeric( $border_radius ) ? $border_radius . 'px' : $border_radius;
				$border_style    = sprintf( 'border-radius: %s;', esc_attr( $border_radius ) );
				$input_styles[]  = $border_style;
				$button_styles[] = $border_style;

				if ( $is_button_inside && intval( $border_radius ) !== 0 ) {
					// Adjust wrapper border radii to maintain visual consistency
					// with inner elements when button is positioned inside.
					$wrapper_styles[] = sprintf(
						'border-radius: calc(%s + %s);',
						esc_attr( $border_radius ),
						$default_padding
					);
				}
			}
		}

		// Add border color styles.
		$has_border_color = ! empty( $attributes['style']['border']['color'] );

		if ( $has_border_color ) {
			$border_color = $attributes['style']['border']['color'];

			// Apply wrapper border color if button placed inside.
			if ( $is_button_inside ) {
				$wrapper_styles[] = sprintf( 'border-color: %s;', esc_attr( $border_color ) );
			} else {
				$button_styles[] = sprintf( 'border-color: %s;', esc_attr( $border_color ) );
				$input_styles[]  = sprintf( 'border-color: %s;', esc_attr( $border_color ) );
			}
		}

		// Add color styles.
		$has_text_color = ! empty( $attributes['style']['color']['text'] );
		if ( $has_text_color ) {
			$button_styles[] = sprintf( 'color: %s;', esc_attr( $attributes['style']['color']['text'] ) );
		}

		$has_background_color = ! empty( $attributes['style']['color']['background'] );
		if ( $has_background_color ) {
			$button_styles[] = sprintf( 'background-color: %s;', esc_attr( $attributes['style']['color']['background'] ) );
		}

		$has_custom_gradient = ! empty( $attributes['style']['color']['gradient'] );
		if ( $has_custom_gradient ) {
			$button_styles[] = sprintf( 'background: %s;', $attributes['style']['color']['gradient'] );
		}

		return [
			'input'   => ! empty( $input_styles ) ? sprintf( ' style="%s"', safecss_filter_attr( implode( ' ', $input_styles ) ) ) : '',
			'button'  => ! empty( $button_styles ) ? sprintf( ' style="%s"', safecss_filter_attr( implode( ' ', $button_styles ) ) ) : '',
			'wrapper' => ! empty( $wrapper_styles ) ? sprintf( ' style="%s"', safecss_filter_attr( implode( ' ', $wrapper_styles ) ) ) : '',
		];
	}

	/**
	 * Returns border color classnames depending on whether there are named or custom border colors.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The border color classnames to be applied to the block elements.
	 */
	public function get_border_color_classes( $attributes ) {
		$has_custom_border_color = ! empty( $attributes['style']['border']['color'] );
		$border_color_classes    = ! empty( $attributes['borderColor'] ) ? sprintf( 'has-border-color has-%s-border-color', $attributes['borderColor'] ) : '';
		// If there's a border color style and no `borderColor` text string, we still want to add the generic `has-border-color` class name to the element.
		if ( $has_custom_border_color && empty( $attributes['borderColor'] ) ) {
			$border_color_classes = 'has-border-color';
		}
		return $border_color_classes;
	}

	/**
	 * Returns color classnames depending on whether there are named or custom text and background colors.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The color classnames to be applied to the block elements.
	 */
	public function get_color_classes( $attributes ) {
		$classnames = [];

		// Text color.
		$has_named_text_color  = ! empty( $attributes['textColor'] );
		$has_custom_text_color = ! empty( $attributes['style']['color']['text'] );
		if ( $has_named_text_color ) {
			$classnames[] = sprintf( 'has-text-color has-%s-color', $attributes['textColor'] );
		} elseif ( $has_custom_text_color ) {
			// If a custom 'textColor' was selected instead of a preset, still add the generic `has-text-color` class.
			$classnames[] = 'has-text-color';
		}

		// Background color.
		$has_named_background_color  = ! empty( $attributes['backgroundColor'] );
		$has_custom_background_color = ! empty( $attributes['style']['color']['background'] );
		$has_named_gradient          = ! empty( $attributes['gradient'] );
		$has_custom_gradient         = ! empty( $attributes['style']['color']['gradient'] );
		if (
			$has_named_background_color ||
			$has_custom_background_color ||
			$has_named_gradient ||
			$has_custom_gradient
		) {
			$classnames[] = 'has-background';
		}
		if ( $has_named_background_color ) {
			$classnames[] = sprintf( 'has-%s-background-color', $attributes['backgroundColor'] );
		}
		if ( $has_named_gradient ) {
			$classnames[] = sprintf( 'has-%s-gradient-background', $attributes['gradient'] );
		}

		return implode( ' ', $classnames );
	}

}
