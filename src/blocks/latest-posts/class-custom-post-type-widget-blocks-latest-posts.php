<?php
/**
 * Custom Post Type Widget Blocks Latest Posts
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

/**
 * Core class Custom_Post_Type_Widget_Blocks_Latest_Posts
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Latest_Posts {
	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );
	}

	public function register_block_type() {
		register_block_type(
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/latest-posts',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function render_callback( $attributes ) {
		global $post;

		$args = [
			'post_type'        => $attributes['postType'],
			'posts_per_page'   => $attributes['postsToShow'],
			'post_status'      => 'publish',
			'order'            => $attributes['order'],
			'orderby'          => $attributes['orderBy'],
			'suppress_filters' => false,
		];

		if ( isset( $attributes['categories'] ) ) {
			if ( 'post' === $attributes['postType'] && 'category' === $attributes['taxonomy'] ) {
				$args['category'] = $attributes['categories'];
			}
			else {
				if ( $attributes['taxonomy'] ) {
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$args['tax_query'] = [
						[
							'taxonomy' => $attributes['taxonomy'],
							'field'    => 'term_id',
							'terms'    => [
								$attributes['categories'],
							],
						],
					];
				}
			}
		}

		if ( isset( $attributes['selectedAuthor'] ) ) {
			$args['author'] = $attributes['selectedAuthor'];
		}

		/**
		 * Filters the arguments for the Recent Posts widget.
		 *
		 * Filter hook: custom_post_type_widget_blocks/latest_posts/widget_posts_args
		 *
		 * @since 3.4.0
		 * @since 4.9.0 Added the `$instance` parameter.
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array  $args     An array of arguments used to retrieve the recent posts.
		 */
		$recent_posts = get_posts( apply_filters( 'custom_post_type_widget_blocks/latest_posts/widget_posts_args', $args ) );

		if ( empty( $recent_posts ) ) {
			return '';
		}

		$list_items_markup = '';

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		foreach ( $recent_posts as $post ) {
			$post_link = esc_url( get_permalink( $post ) );
			$title     = get_the_title( $post );

			if ( ! $title ) {
				$title = __( '(no title)', 'custom-post-type-widget-blocks' );
			}

			$list_items_markup .= '<li>';

			if ( $attributes['displayFeaturedImage'] && ( has_post_thumbnail( $post ) || isset( $attributes['featuredImageId'] ) ) ) {
				$image_style = '';
				if ( isset( $attributes['featuredImageSizeWidth'] ) && isset( $attributes['featuredImageSizeHeight'] ) ) {
					$image_style = 'width:100%;';
				}
				if ( isset( $attributes['featuredImageSizeWidth'] ) ) {
					$image_style .= sprintf( 'max-width:%spx;', $attributes['featuredImageSizeWidth'] );
				}
				if ( isset( $attributes['featuredImageSizeHeight'] ) ) {
					$image_style .= sprintf( 'max-height:%spx;', $attributes['featuredImageSizeHeight'] );
				}

				$image_classnames   = [];
				$image_classnames[] = 'wp-block-custom-post-type-widget-blocks-latest-posts__featured-image';
				if ( isset( $attributes['featuredImageAlign'] ) ) {
					$image_classnames[] = 'align' . $attributes['featuredImageAlign'];
				}

				if ( has_post_thumbnail( $post ) ) {
					$featured_image = get_the_post_thumbnail(
						$post,
						$attributes['featuredImageSizeSlug'],
						[
							'style' => esc_attr( $image_style ),
						]
					);
				}
				elseif ( isset( $attributes['featuredImageId'] ) ) {
					$featured_image = wp_get_attachment_image(
						$attributes['featuredImageId'],
						$attributes['featuredImageSizeSlug'],
						false,
						[
							'style' => esc_attr( $image_style ),
							'class' => 'attachment-' . $attributes['featuredImageSizeSlug'] . ' size-' . $attributes['featuredImageSizeSlug'] . ' wp-post-image',
						]
					);
				}

				if ( $attributes['addLinkToFeaturedImage'] ) {
					$featured_image = sprintf(
						'<a href="%1$s" aria-label="%2$s">%3$s</a>',
						esc_url( $post_link ),
						esc_attr( $title ),
						$featured_image
					);
				}
				$list_items_markup .= sprintf(
					'<div class="%1$s">%2$s</div>',
					esc_attr( implode( ' ', $image_classnames ) ),
					$featured_image
				);
			}

			$list_items_markup .= sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $post_link ),
				esc_html( $title )
			);

			if ( isset( $attributes['displayAuthor'] ) && $attributes['displayAuthor'] ) {
				$author_display_name = get_the_author_meta( 'display_name', $post->post_author );

				/* translators: byline. %s: current author. */
				$byline = sprintf( __( 'by %s', 'custom-post-type-widget-blocks' ), $author_display_name );

				if ( ! empty( $author_display_name ) ) {
					$list_items_markup .= sprintf(
						'<div class="wp-block-latest-posts__post-author">%1$s</div>',
						esc_html( $byline )
					);
				}
			}

			if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
				$list_items_markup .= sprintf(
					'<time datetime="%1$s" class="wp-block-custom-post-type-widget-blocks-latest-posts__post-date">%2$s</time>',
					esc_attr( get_the_date( 'c', $post ) ),
					esc_html( get_the_date( '', $post ) )
				);
			}

			if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
				&& isset( $attributes['displayPostContentRadio'] ) && 'excerpt' === $attributes['displayPostContentRadio'] ) {

				if ( post_password_required( $post ) ) {
					$trimmed_excerpt = __( 'This content is password protected.', 'custom-post-type-widget-blocks' );
				}
				else {
					$trimmed_excerpt = get_the_excerpt( $post );
				}

				$list_items_markup .= sprintf(
					'<div class="wp-block-custom-post-type-widget-blocks-latest-posts__post-excerpt">%1$s</div>',
					$trimmed_excerpt
				);
			}

			if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
				&& isset( $attributes['displayPostContentRadio'] ) && 'full_post' === $attributes['displayPostContentRadio'] ) {

				if ( post_password_required( $post ) ) {
					$post_content = __( 'This content is password protected.', 'custom-post-type-widget-blocks' );
				}
				else {
					$post_content = html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) );
				}

				$list_items_markup .= sprintf(
					'<div class="wp-block-custom-post-type-widget-blocks-latest-posts__post-full-content">%1$s</div>',
					wp_kses_post( $post_content )
				);
			}

			$list_items_markup .= "</li>\n";
		}

		wp_reset_postdata();

		$classnames[] = 'wp-block-custom-post-type-widget-blocks-latest-posts__list';

		if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
			$classnames[] = 'is-grid';
		}

		if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
			$classnames[] = 'columns-' . $attributes['columns'];
		}

		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$classnames[] = 'has-dates';
		}

		if ( isset( $attributes['displayAuthor'] ) && $attributes['displayAuthor'] ) {
			$classnames[] = 'has-author';
		}

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

		return sprintf(
			'<ul %1$s>%2$s</ul>',
			$wrapper_attributes,
			$list_items_markup
		);
	}
}
