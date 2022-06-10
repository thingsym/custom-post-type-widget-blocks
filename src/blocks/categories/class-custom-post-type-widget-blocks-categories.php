<?php
/**
 * Custom Post Type Widget Blocks Calendar
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

/**
 * Core class Custom_Post_Type_Widget_Blocks_Categories
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Categories {
	/**
	 * Ensure that the ID attribute only appears in the markup once
	 *
	 * @since 1.0.2
	 *
	 * @static
	 * @access public
	 * @var int
	 */
	public static $block_id = 0;

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
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/categories',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function build_dropdown_script( $dropdown_id ) {
		ob_start();
		?>
		<script type='text/javascript'>
		/* <![CDATA[ */
		( function() {
			var dropdown = document.getElementById( '<?php echo esc_js( $dropdown_id ); ?>' );
			function onCatChange() {
				if ( dropdown.options[ dropdown.selectedIndex ].value != -1 ) {
					return dropdown.form.submit();
				}
			}
			dropdown.onchange = onCatChange;
		})();
		/* ]]> */
		</script>
		<?php
		return ob_get_clean();
	}

	public function render_callback( $attributes ) {
		self::$block_id++;

		$args = [
			'echo'         => false,
			'taxonomy'     => $attributes['taxonomy'],
			'hierarchical' => ! empty( $attributes['showHierarchy'] ),
			'orderby'      => 'name',
			'show_count'   => ! empty( $attributes['showPostCounts'] ),
			'title_li'     => '',
		];
		if ( ! empty( $attributes['showOnlyTopLevel'] ) && $attributes['showOnlyTopLevel'] ) {
			$args['parent'] = 0;
		}

		if ( ! empty( $attributes['displayAsDropdown'] ) ) {
			$id                       = 'wp-block-custom-post-type-widget-blocks-categories-' . self::$block_id;
			$args['id']               = $id;
			$args['show_option_none'] = __( 'Select Category', 'custom-post-type-widget-blocks' );
			$args['name']             = 'category' === $attributes['taxonomy'] ? 'category_name' : $attributes['taxonomy'];
			$args['value_field']      = 'slug';
			$wrapper_markup           = '<div %1$s>%2$s</div>';
			$items_markup             = '<form action="' . esc_url( home_url() ) . '" method="get">';
			/**
			 * Filters the arguments for the Categories widget drop-down.
			 *
			 * Filter hook: custom_post_type_widget_blocks/categories/widget_categories_dropdown_args
			 *
			 * @since 2.8.0
			 * @since 4.9.0 Added the `$instance` parameter.
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param array  $cat_args An array of Categories widget drop-down arguments.
			 */
			$items_markup .= wp_dropdown_categories( apply_filters( 'custom_post_type_widget_blocks/categories/widget_categories_dropdown_args', $args ) );
			$items_markup .= '</form>';

			$type = 'dropdown';

			if ( ! is_admin() ) {
				$wrapper_markup .= $this->build_dropdown_script( $id );
			}
		} else {
			$wrapper_markup = '<ul %1$s>%2$s</ul>';
			/**
			 * Filters the arguments for the Categories widget.
			 *
			 * Filter hook: custom_post_type_widget_blocks/categories/widget_categories_args
			 *
			 * @see wp_list_categories()
			 *
			 * @param array  $args An array of Categories widget arguments.
			 */
			$items_markup = wp_list_categories(
				apply_filters(
					'custom_post_type_widget_blocks/categories/widget_categories_args',
					$args
				)
			);
			$type         = 'list';
		}

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => "wp-block-custom-post-type-widget-blocks-categories-{$type}" ] );

		return sprintf(
			$wrapper_markup,
			$wrapper_attributes,
			$items_markup
		);
	}
}
