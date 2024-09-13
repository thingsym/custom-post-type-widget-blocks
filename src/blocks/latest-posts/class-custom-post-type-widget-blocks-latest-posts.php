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

		add_filter( 'render_block_data', [ $this, 'block_core_latest_posts_migrate_categories' ] );

		/**
		 * The excerpt length set by the Latest Posts core block
		 * set at render time and used by the block itself.
		 *
		 * @var int
		 */
		global $block_core_latest_posts_excerpt_length;
		$block_core_latest_posts_excerpt_length = 0;
	}

	/**
	 * Callback for the excerpt_length filter used by
	 * the Latest Posts block at render time.
	 *
	 * @since 5.4.0
	 *
	 * @return int Returns the global $block_core_latest_posts_excerpt_length variable
	 *             to allow the excerpt_length filter respect the Latest Block setting.
	 */
	public function block_core_latest_posts_get_excerpt_length() {
		global $block_core_latest_posts_excerpt_length;
		return $block_core_latest_posts_excerpt_length;
	}

	/**
	 * Renders the latest-posts block on server.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with latest posts added.
	 */
	public function register_block_type() {
		register_block_type_from_metadata(
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/latest-posts',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function render_callback( $attributes ) {
		global $post, $block_core_latest_posts_excerpt_length;

		$args = [
			'post_type'           => $attributes['postType'],
			'posts_per_page'      => $attributes['postsToShow'],
			'post_status'         => 'publish',
			'order'               => $attributes['order'],
			'orderby'             => $attributes['orderBy'],
			'suppress_filters'    => false,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];

		$block_core_latest_posts_excerpt_length = $attributes['excerptLength'];
		add_filter( 'excerpt_length', [ $this, 'block_core_latest_posts_get_excerpt_length' ], 20 );
		if ( ! empty( $attributes['categories'] ) ) {
				if ( 'post' === $attributes['postType'] && 'category' === $attributes['taxonomy'] ) {
				$args['category__in'] = array_column( $attributes['categories'], 'id' );
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
		$query        = new \WP_Query();
		$recent_posts = $query->query( apply_filters( 'custom_post_type_widget_blocks/latest_posts/widget_posts_args', $args ) );

		if ( empty( $recent_posts ) ) {
			return '';
		}

		if ( isset( $attributes['displayFeaturedImage'] ) && $attributes['displayFeaturedImage'] ) {
			update_post_thumbnail_cache( $query );
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

					/*
					* Adds a "Read more" link with screen reader text.
					* [&hellip;] is the default excerpt ending from wp_trim_excerpt() in Core.
					*/
					if ( str_ends_with( $trimmed_excerpt, ' [&hellip;]' ) ) {
						/** This filter is documented in wp-includes/formatting.php */
						$excerpt_length = (int) apply_filters( 'excerpt_length', $block_core_latest_posts_excerpt_length );
						if ( $excerpt_length <= $block_core_latest_posts_excerpt_length ) {
							$trimmed_excerpt  = substr( $trimmed_excerpt, 0, -11 );
							$trimmed_excerpt .= sprintf(
								/* translators: 1: A URL to a post, 2: Hidden accessibility text: Post title */
								__( '… <a class="wp-block-latest-posts__read-more" href="%1$s" rel="noopener noreferrer">Read more<span class="screen-reader-text">: %2$s</span></a>', 'custom-post-type-widget-blocks' ),
								esc_url( $post_link ),
								esc_html( $title )
							);
						}
					}
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

		$classes[] = 'wp-block-custom-post-type-widget-blocks-latest-posts__list';
		if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
			$classes[] = 'is-grid';
		}
		if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
			$classes[] = 'columns-' . $attributes['columns'];
		}
		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$classes[] = 'has-dates';
		}
		if ( isset( $attributes['displayAuthor'] ) && $attributes['displayAuthor'] ) {
			$classes[] = 'has-author';
		}
		if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
			$classes[] = 'has-link-color';
		}

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classes ) ] );

		return sprintf(
			'<ul %1$s>%2$s</ul>',
			$wrapper_attributes,
			$list_items_markup
		);
	}

	/**
	 * Handles outdated versions of the `custom-post-type-widget-blocks/latest-posts` block by converting
	 * attribute `categories` from a numeric string to an array with key `id`.
	 *
	 * This is done to accommodate the changes introduced in #20781 that sought to
	 * add support for multiple categories to the block. However, given that this
	 * block is dynamic, the usual provisions for block migration are insufficient,
	 * as they only act when a block is loaded in the editor.
	 *
	 * TODO: Remove when and if the bottom client-side deprecation for this block
	 * is removed.
	 *
	 * @since 5.5.0
	 *
	 * @param array $block A single parsed block object.
	 *
	 * @return array The migrated block object.
	 */
	public function block_core_latest_posts_migrate_categories( $block ) {
		if (
			'custom-post-type-widget-blocks/latest-posts' === $block['blockName'] &&
			! empty( $block['attrs']['categories'] ) &&
			is_string( $block['attrs']['categories'] )
		) {
			$block['attrs']['categories'] = [
				[ 'id' => absint( $block['attrs']['categories'] ) ],
			];
		}

		return $block;
	}
}
