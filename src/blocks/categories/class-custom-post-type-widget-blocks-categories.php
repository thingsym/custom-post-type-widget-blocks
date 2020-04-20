<?php
/**
 * Custom Post Type Widget Blocks Calendar
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

class Custom_Post_Type_Widget_Blocks_Categories {
	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );
	}

	public function register_block_type() {
		register_block_type(
			'custom-post-type-widget-blocks/categories',
			[
				'attributes'      => [
					'taxonomy'          => [
						'type'    => 'string',
						'default' => 'category',
					],
					'align'             => [
						'type' => 'string',
						'enum' => [ 'left', 'center', 'right', 'wide', 'full' ],
					],
					'className'         => [
						'type' => 'string',
					],
					'displayAsDropdown' => [
						'type'    => 'boolean',
						'default' => false,
					],
					'showHierarchy'     => [
						'type'    => 'boolean',
						'default' => false,
					],
					'showPostCounts'    => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
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
			var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
			function onCatChange() {
				if ( dropdown.options[dropdown.selectedIndex].value ) {
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
		static $block_id = 0;
		$block_id++;

		$args = [
			'echo'         => false,
			'taxonomy'     => $attributes['taxonomy'],
			'hierarchical' => ! empty( $attributes['showHierarchy'] ),
			'orderby'      => 'name',
			'show_count'   => ! empty( $attributes['showPostCounts'] ),
			'title_li'     => '',
		];

		if ( ! empty( $attributes['displayAsDropdown'] ) ) {
			$id                       = 'wp-block-custom-post-type-widget-blocks-categories-' . $block_id;
			$args['id']               = $id;
			$args['show_option_none'] = __( 'Select Category', 'custom-post-type-widget-blocks' );
			$args['name']             = 'category' === $attributes['taxonomy'] ? 'category_name' : $attributes['taxonomy'];
			$args['value_field']      = 'slug';
			$wrapper_markup           = '<div class="%1$s">%2$s</div>';
			$items_markup             = '<form action="' . esc_url( home_url() ) . '" method="get">';
			$items_markup            .= wp_dropdown_categories( $args );
			$items_markup            .= '</form>';
			$type                     = 'dropdown';

			if ( ! is_admin() ) {
				$wrapper_markup .= $this->build_dropdown_script( $id );
			}
		} else {
			$wrapper_markup = '<ul class="%1$s">%2$s</ul>';
			$items_markup   = wp_list_categories( $args );
			$type           = 'list';
		}

		$class = "wp-block-custom-post-type-widget-blocks-categories wp-block-custom-post-type-widget-blocks-categories-{$type}";

		if ( isset( $attributes['align'] ) ) {
			$class .= " align{$attributes['align']}";
		}

		if ( isset( $attributes['className'] ) ) {
			$class .= " {$attributes['className']}";
		}

		return sprintf(
			$wrapper_markup,
			esc_attr( $class ),
			$items_markup
		);
	}
}
