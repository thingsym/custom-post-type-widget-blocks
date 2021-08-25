<?php
/**
 * Custom Post Type Widget Blocks Tag Cloud
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

/**
 * Core class Custom_Post_Type_Widget_Blocks_Tag_Cloud
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Tag_Cloud {
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
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/tag-cloud',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function render_callback( $attributes ) {
		$args = [
			'echo'       => false,
			'taxonomy'   => $attributes['taxonomy'],
			'show_count' => $attributes['showTagCounts'],
		];

		/**
		 * Filters the taxonomy used in the Tag Cloud widget.
		 *
		 * Filter hook: custom_post_type_widget_blocks/tag_cloud/widget_tag_cloud_args
		 *
		 * @since 2.8.0
		 * @since 3.0.0 Added taxonomy drop-down.
		 * @since 4.9.0 Added the `$instance` parameter.
		 *
		 * @see wp_tag_cloud()
		 *
		 * @param array $args     Args used for the tag cloud widget.
		 */
		$tag_cloud = wp_tag_cloud( apply_filters( 'custom_post_type_widget_blocks/tag_cloud/widget_tag_cloud_args', $args ) );

		if ( ! $tag_cloud ) {
			$labels    = get_taxonomy_labels( get_taxonomy( $attributes['taxonomy'] ) );
			$tag_cloud = esc_html(
				sprintf(
					/* translators: %s: taxonomy name */
					__( 'Your site doesn&#8217;t have any %s, so there&#8217;s nothing to display here at the moment.', 'custom-post-type-widget-blocks' ),
					strtolower( $labels->name )
				)
			);
		}

		$wrapper_attributes = get_block_wrapper_attributes();

		return sprintf(
			'<p %1$s>%2$s</p>',
			$wrapper_attributes,
			$tag_cloud
		);
	}
}
