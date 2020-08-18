<?php
/**
 * Custom Post Type Widget Blocks Archives
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

class Custom_Post_Type_Widget_Blocks_Archives {
	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );
	}

	public function register_block_type() {
		register_block_type(
			'custom-post-type-widget-blocks/archives',
			[
				'attributes'      => [
					'postType'          => [
						'type'    => 'string',
						'default' => 'post',
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
					'showPostCounts'    => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
				'render_callback' => [ $this, 'render_callback' ],
				'editor_script' => 'custom-post-type-widget-blocks-editor-script',
				'editor_style'  => 'custom-post-type-widget-blocks-editor-style',
				'style'         => 'custom-post-type-widget-blocks-style',
			]
		);
	}

	public function render_callback( $attributes ) {
		$show_post_count = ! empty( $attributes['showPostCounts'] );
		$this->posttype  = $attributes['postType'];

		$class = 'wp-block-custom-post-type-widget-blocks-archives';

		if ( isset( $attributes['align'] ) ) {
			$class .= " align{$attributes['align']}";
		}

		if ( isset( $attributes['className'] ) ) {
			$class .= " {$attributes['className']}";
		}

		if ( ! empty( $attributes['displayAsDropdown'] ) ) {

			$class .= ' wp-block-custom-post-type-widget-blocks-archives-dropdown';

			$dropdown_id = esc_attr( uniqid( 'wp-block-custom-post-type-widget-blocks-archives-' ) );
			$title       = __( 'Archives', 'custom-post-type-widget-blocks' );

			/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
			$dropdown_args = apply_filters(
				'widget_archives_dropdown_args',
				[
					'post_type'       => $attributes['postType'],
					'type'            => 'monthly',
					'format'          => 'option',
					'show_post_count' => $show_post_count,
				]
			);

			$dropdown_args['echo'] = 0;

			add_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ], 10, 3 );
			add_filter( 'get_archives_link', [ $this, 'trim_post_type' ], 10, 1 );

			$archives = wp_get_archives( $dropdown_args );

			remove_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ] );
			remove_filter( 'get_archives_link', [ $this, 'trim_post_type' ] );

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

			return sprintf(
				'<div class="%1$s">%2$s</div>',
				esc_attr( $class ),
				$block_content
			);
		}

		$class .= ' wp-block-custom-post-type-widget-blocks-archives-list';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
		$archives_args = apply_filters(
			'widget_archives_args',
			[
				'post_type'       => $attributes['postType'],
				'type'            => 'monthly',
				'show_post_count' => $show_post_count,
			]
		);

		$archives_args['echo'] = 0;

		add_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ], 10, 3 );
		add_filter( 'get_archives_link', [ $this, 'trim_post_type' ], 10, 1 );

		$archives = wp_get_archives( $archives_args );

		remove_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ] );
		remove_filter( 'get_archives_link', [ $this, 'trim_post_type' ] );

		$classnames = esc_attr( $class );

		if ( empty( $archives ) ) {

			return sprintf(
				'<div class="%1$s">%2$s</div>',
				$classnames,
				__( 'No archives to show.', 'custom-post-type-widget-blocks' )
			);
		}

		return sprintf(
			'<ul class="%1$s">%2$s</ul>',
			$classnames,
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
	 * @param string $monthlink
	 * @param string $year
	 * @param string $month
	 *
	 * @return string $monthlink
	 */
	public function get_month_link_custom_post_type( $monthlink, $year, $month ) {
		global $wp_rewrite;

		$posttype = $this->posttype;

		if ( ! $year ) {
			$year = current_time( 'Y' );
		}
		if ( ! $month ) {
			$month = current_time( 'm' );
		}

		$monthlink = $wp_rewrite->get_month_permastruct();

		if ( ! empty( $monthlink ) ) {
			$front = preg_replace( '/\/$/', '', $wp_rewrite->front );

			$monthlink = str_replace( '%year%', $year, $monthlink );
			$monthlink = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $monthlink );

			if ( 'post' === $posttype ) {
				$monthlink = home_url( user_trailingslashit( $monthlink, 'month' ) );
			}
			else {
				$type_obj     = get_post_type_object( $posttype );
				$archive_name = ! empty( $type_obj->rewrite['slug'] ) ? $type_obj->rewrite['slug'] : $posttype;
				if ( $front ) {
					$new_front = $type_obj->rewrite['with_front'] ? $front : '';
					$monthlink = str_replace( $front, $new_front . '/' . $archive_name, $monthlink );
					$monthlink = home_url( user_trailingslashit( $monthlink, 'month' ) );
				}
				else {
					$monthlink = home_url( user_trailingslashit( $archive_name . $monthlink, 'month' ) );
				}
			}
		}
		else {
			$monthlink = home_url( '?post_type=' . $posttype . '&m=' . $year . zeroise( $month, 2 ) );
		}

		return $monthlink;
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
	 * @param string $link_html
	 *
	 * @return string $link_html
	 */
	public function trim_post_type( $link_html ) {
		global $wp_rewrite;

		if ( ! $wp_rewrite->permalink_structure ) {
			return $link_html;
		}

		$posttype = $this->posttype;

		$link_html = str_replace( '?post_type=' . $posttype, '', $link_html );

		return $link_html;
	}
}
