<?php
/**
 * Custom Post Type Widget Blocks Latest Posts
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks\Blocks;

class Custom_Post_Type_Widget_Blocks_Latest_Posts {
	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );
	}

	public function register_block_type() {
		register_block_type(
			'custom-post-type-widget-blocks/latest-posts',
			[
				'attributes'      => [
					'postType'                => [
						'type'    => 'string',
						'default' => 'post',
					],
					'taxonomy'                => [
						'type'    => 'string',
						'default' => 'category',
					],
					'categories'              => [
						'type' => 'string',
					],
					'align'                   => [
						'type' => 'string',
						'enum' => [ 'left', 'center', 'right', 'wide', 'full' ],
					],
					'className'               => [
						'type' => 'string',
					],
					'postsToShow'             => [
						'type'    => 'number',
						'default' => 5,
					],
					'displayPostContent'      => [
						'type'    => 'boolean',
						'default' => false,
					],
					'displayPostContentRadio' => [
						'type'    => 'string',
						'default' => 'excerpt',
					],
					'excerptLength'           => [
						'type'    => 'number',
						'default' => 55,
					],
					'displayPostDate'         => [
						'type'    => 'boolean',
						'default' => false,
					],
					'postLayout'              => [
						'type'    => 'string',
						'default' => 'list',
					],
					'columns'                 => [
						'type'    => 'number',
						'default' => 3,
					],
					'order'                   => [
						'type'    => 'string',
						'default' => 'desc',
					],
					'orderBy'                 => [
						'type'    => 'string',
						'default' => 'date',
					],
					'displayFeaturedImage'    => [
						'type'    => 'boolean',
						'default' => false,
					],
					'featuredImageAlign'      => [
						'type' => 'string',
						'enum' => [ 'left', 'center', 'right' ],
					],
					'featuredImageSizeSlug'   => [
						'type'    => 'string',
						'default' => 'thumbnail',
					],
					'featuredImageSizeWidth'  => [
						'type'    => 'number',
						'default' => null,
					],
					'featuredImageSizeHeight' => [
						'type'    => 'number',
						'default' => null,
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

		$recent_posts = get_posts( $args );

		$list_items_markup = '';

		foreach ( $recent_posts as $post ) {
			$list_items_markup .= '<li>';

			if ( $attributes['displayFeaturedImage'] && has_post_thumbnail( $post ) ) {
				$image_style = '';
				if ( isset( $attributes['featuredImageSizeWidth'] ) ) {
					$image_style .= sprintf( 'max-width:%spx;', $attributes['featuredImageSizeWidth'] );
				}
				if ( isset( $attributes['featuredImageSizeHeight'] ) ) {
					$image_style .= sprintf( 'max-height:%spx;', $attributes['featuredImageSizeHeight'] );
				}

				$image_classes = 'wp-block-custom-post-type-widget-blocks-latest-posts__featured-image';
				if ( isset( $attributes['featuredImageAlign'] ) ) {
					$image_classes .= ' align' . $attributes['featuredImageAlign'];
				}

				$list_items_markup .= sprintf(
					'<div class="%1$s">%2$s</div>',
					$image_classes,
					get_the_post_thumbnail(
						$post,
						$attributes['featuredImageSizeSlug'],
						[
							'style' => $image_style,
						]
					)
				);
			}

			$title = get_the_title( $post );
			if ( ! $title ) {
				$title = __( '(no title)', 'custom-post-type-widget-blocks' );
			}
			$list_items_markup .= sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( get_permalink( $post ) ),
				$title
			);

			if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
				$list_items_markup .= sprintf(
					'<time datetime="%1$s" class="wp-block-custom-post-type-widget-blocks-latest-posts__post-date">%2$s</time>',
					esc_attr( get_the_date( 'c', $post ) ),
					esc_html( get_the_date( '', $post ) )
				);
			}

			if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
				&& isset( $attributes['displayPostContentRadio'] ) && 'excerpt' === $attributes['displayPostContentRadio'] ) {

				$trimmed_excerpt = get_the_excerpt( $post );

				$list_items_markup .= sprintf(
					'<div class="wp-block-custom-post-type-widget-blocks-latest-posts__post-excerpt">%1$s',
					$trimmed_excerpt
				);

				if ( strpos( $trimmed_excerpt, ' &hellip; ' ) !== false ) {
					$list_items_markup .= sprintf(
						'<a href="%1$s">%2$s</a></div>',
						esc_url( get_permalink( $post ) ),
						__( 'Read more', 'custom-post-type-widget-blocks' )
					);
				} else {
					$list_items_markup .= sprintf(
						'</div>'
					);
				}
			}

			if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
				&& isset( $attributes['displayPostContentRadio'] ) && 'full_post' === $attributes['displayPostContentRadio'] ) {
				$list_items_markup .= sprintf(
					'<div class="wp-block-custom-post-type-widget-blocks-latest-posts__post-full-content">%1$s</div>',
					wp_kses_post( html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) ) )
				);
			}

			$list_items_markup .= "</li>\n";
		}

		$class = 'wp-block-custom-post-type-widget-blocks-latest-posts wp-block-custom-post-type-widget-blocks-latest-posts__list';
		if ( isset( $attributes['align'] ) ) {
			$class .= ' align' . $attributes['align'];
		}

		if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
			$class .= ' is-grid';
		}

		if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
			$class .= ' columns-' . $attributes['columns'];
		}

		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$class .= ' has-dates';
		}

		if ( isset( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}

		return sprintf(
			'<ul class="%1$s">%2$s</ul>',
			esc_attr( $class ),
			$list_items_markup
		);
	}
}
