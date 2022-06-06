<?php
/**
 * Custom Post Type Widget Blocks Archives
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

/**
 * Core class Custom_Post_Type_Widget_Blocks_Archives
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Archives {
	/**
	 * Posttype
	 *
	 * @since 1.0.2
	 *
	 * @access public
	 * @var string
	 */
	public $posttype;

	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );
	}

	/**
	 * register block_type from metadata
	 *
	 * @since 1.3.0
	 */
	public function register_block_type() {
		register_block_type_from_metadata(
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/archives',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function render_callback( $attributes ) {
		$show_post_count = ! empty( $attributes['showPostCounts'] );
		$this->posttype  = $attributes['postType'];

		$disable_get_links = 0;
		if ( defined( 'CUSTOM_POST_TYPE_WIDGET_BLOCKS_DISABLE_LINKS_ARCHIVE' ) ) {
			if ( CUSTOM_POST_TYPE_WIDGET_BLOCKS_DISABLE_LINKS_ARCHIVE ) {
				$disable_get_links = 1;
			}
		}

		$archive_type = ! empty( $attributes['archiveType'] ) ? $attributes['archiveType'] : 'monthly';
		if ( ! empty( $attributes['displayAsDropdown'] ) ) {

			$classnames[] = 'wp-block-custom-post-type-widget-blocks-archives-dropdown';

			$dropdown_id = esc_attr( uniqid( 'wp-block-custom-post-type-widget-blocks-archives-' ) );
			$title       = __( 'Archives', 'custom-post-type-widget-blocks' );

			/**
			 * Filters the arguments for the Archives widget drop-down.
			 *
			 * Filter hook: custom_post_type_widget_blocks/archive/widget_archives_dropdown_args
			 *
			 * @since 2.8.0
			 * @since 4.9.0 Added the `$instance` parameter.
			 *
			 * @see wp_get_archives()
			 *
			 * @param array  $args     An array of Archives widget drop-down arguments.
			 */
			$dropdown_args = apply_filters(
				'custom_post_type_widget_blocks/archives/widget_archives_dropdown_args',
				[
					'post_type'       => $attributes['postType'],
					'type'            => $archive_type,
					'format'          => 'option',
					'show_post_count' => $show_post_count,
				]
			);

			$dropdown_args['echo'] = 0;

			if ( ! $disable_get_links ) {
				add_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ], 10, 3 );
				add_filter( 'get_archives_link', [ $this, 'trim_post_type' ], 10, 1 );
			}

			$archives = wp_get_archives( $dropdown_args );

			if ( ! $disable_get_links ) {
				remove_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ] );
				remove_filter( 'get_archives_link', [ $this, 'trim_post_type' ] );
			}

			switch ( $dropdown_args['type'] ) {
				case 'yearly':
					$label = __( 'Select Year', 'custom-post-type-widget-blocks' );
					break;
				case 'monthly':
					$label = __( 'Select Month', 'custom-post-type-widget-blocks' );
					break;
				case 'daily':
					$label = __( 'Select Day', 'custom-post-type-widget-blocks' );
					break;
				case 'weekly':
					$label = __( 'Select Week', 'custom-post-type-widget-blocks' );
					break;
				default:
					$label = __( 'Select Post', 'custom-post-type-widget-blocks' );
					break;
			}

			$label = esc_attr( $label );

			$block_content = '<label class="screen-reader-text" for="' . $dropdown_id . '">' . $title . '</label>
			<select id="' . $dropdown_id . '" name="archive-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
			<option value="">' . $label . '</option>' . $archives . '</select>';

			$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

			return sprintf(
				'<div %1$s>%2$s</div>',
				$wrapper_attributes,
				$block_content
			);
		}

		$classnames[] = 'wp-block-custom-post-type-widget-blocks-archives-list';

		/**
		 * Filters the arguments for the Archives widget.
		 *
		 * Filter hook: custom_post_type_widget_blocks/archive/widget_archives_args
		 *
		 * @since 2.8.0
		 * @since 4.9.0 Added the `$instance` parameter.
		 *
		 * @see wp_get_archives()
		 *
		 * @param array  $args     An array of Archives option arguments.
		 */
		$archives_args = apply_filters(
			'custom_post_type_widget_blocks/archives/widget_archives_args',
			[
				'post_type'       => $attributes['postType'],
				'type'            => $archive_type,
				'show_post_count' => $show_post_count,
			]
		);

		$archives_args['echo'] = 0;

		if ( ! $disable_get_links ) {
			add_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ], 10, 3 );
			add_filter( 'get_archives_link', [ $this, 'trim_post_type' ], 10, 1 );
		}

		$archives = wp_get_archives( $archives_args );

		if ( ! $disable_get_links ) {
			remove_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ] );
			remove_filter( 'get_archives_link', [ $this, 'trim_post_type' ] );
		}

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

		if ( empty( $archives ) ) {
			return sprintf(
				'<div %1$s>%2$s</div>',
				$wrapper_attributes,
				__( 'No archives to show.', 'custom-post-type-widget-blocks' )
			);
		}

		return sprintf(
			'<ul %1$s>%2$s</ul>',
			$wrapper_attributes,
			$archives
		);
	}

	/**
	 * Gets the month link for custom post type.
	 *
	 * Hooks to month_link
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string $old_monthlink
	 * @param string $year
	 * @param string $month
	 *
	 * @return string $new_monthlink
	 */
	public function get_month_link_custom_post_type( $old_monthlink, $year, $month ) {
		$posttype = $this->posttype;

		if ( ! $year ) {
			$year = current_time( 'Y' );
		}
		if ( ! $month ) {
			$month = current_time( 'm' );
		}

		global $wp_rewrite;
		$new_monthlink = $wp_rewrite->get_month_permastruct();

		if ( ! empty( $new_monthlink ) ) {
			$front = preg_replace( '/\/$/', '', $wp_rewrite->front );

			$new_monthlink = str_replace( '%year%', $year, $new_monthlink );
			$new_monthlink = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $new_monthlink );

			if ( 'post' === $posttype ) {
				$new_monthlink = home_url( user_trailingslashit( $new_monthlink, 'month' ) );
			}
			else {
				$type_obj     = get_post_type_object( $posttype );

				# The priority of the rewrite rule: has_archive < rewrite
				# See https://developer.wordpress.org/reference/functions/register_post_type/
				$archive_name = $posttype;
				if ( is_string( $type_obj->has_archive ) ) {
					$archive_name = $type_obj->has_archive;
				}
				if ( is_bool( $type_obj->rewrite ) && $type_obj->rewrite === true ) {
					$archive_name = $posttype;
				}
				else if ( is_array( $type_obj->rewrite ) ) {
					if ( ! empty( $type_obj->rewrite['slug'] ) ) {
						$archive_name = $type_obj->rewrite['slug'];
					}
				}

				if ( $front ) {
					$new_front = $type_obj->rewrite['with_front'] ? $front : '';
					$new_monthlink = str_replace( $front, $new_front . '/' . $archive_name, $new_monthlink );
					$new_monthlink = home_url( user_trailingslashit( $new_monthlink, 'month' ) );
				}
				else {
					$new_monthlink = home_url( user_trailingslashit( $archive_name . $new_monthlink, 'month' ) );
				}
			}
		}
		else {
			$new_monthlink = home_url( '?post_type=' . $posttype . '&m=' . $year . zeroise( $month, 2 ) );
		}

		/**
		 * Filter a monthlink.
		 *
		 * @since 1.4.0
		 *
		 * @param string $new_monthlink
		 * @param string $year
		 * @param string $month
		 * @param string $old_monthlink
		 */

		return apply_filters( 'custom_post_type_widget_blocks/archive/get_month_link_custom_post_type', $new_monthlink, $year, $month, $old_monthlink );
	}

	/**
	 * Trim the post_type url query from get_archives_link.
	 *
	 * Hooks to get_archives_link
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string $old_link_html
	 *
	 * @return string $link_html
	 */
	public function trim_post_type( $old_link_html ) {
		global $wp_rewrite;

		if ( ! $wp_rewrite->permalink_structure ) {
			return $old_link_html;
		}

		$posttype = $this->posttype;

		$new_link_html = str_replace( '?post_type=' . $posttype, '', $old_link_html );

		/**
		 * Filter a trimed link_html.
		 *
		 * @since 1.4.0
		 *
		 * @param string $new_link_html  trimed link_html
		 * @param string $old_link_html  original link_html
		 * @param string $posttype
		 */
		return apply_filters( 'custom_post_type_widget_blocks/archive/trim_post_type', $new_link_html, $old_link_html, $posttype );
	}
}
