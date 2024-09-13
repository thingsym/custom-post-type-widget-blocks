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
 * Core class Custom_Post_Type_Widget_Blocks_Latest_Comments
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Latest_Comments {
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
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/latest-comments',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	public function draft_or_post_title( $post = 0 ) {
		$title = get_the_title( $post );
		if ( empty( $title ) ) {
			$title = __( '(no title)', 'custom-post-type-widget-blocks' );
		}
		return esc_html( $title );
	}

	/**
	 * Renders the latest-comments block on server.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with latest comments added.
	 */
	public function render_callback( $attributes ) {

		/**
		 * Filters the arguments for the Latest Comments widget.
		 *
		 * Filter hook: custom_post_type_widget_blocks/latest-comments/widget_comments_args
		 *
		 * @since 3.4.0
		 * @since 4.9.0 Added the `$instance` parameter.
		 *
		 * @see WP_Comment_Query::query() for information on accepted arguments.
		 *
		 * @param array  $args An array of arguments used to retrieve the recent comments.
		 */
		$comments = get_comments(
			apply_filters(
				'custom_post_type_widget_blocks/latest-comments/widget_comments_args',
				[
					'post_type'   => $attributes['postType'],
					'number'      => $attributes['commentsToShow'],
					'status'      => 'approve',
					'post_status' => 'publish',
				],
				[]
			)
		);

		$classnames[] = 'wp-block-latest-comments';

		if ( empty( $comments ) ) {
			$classnames[] = 'no-comments';

			$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

			return sprintf(
				'<div %1$s>%2$s</div>',
				$wrapper_attributes,
				__( 'No comments to show.', 'custom-post-type-widget-blocks' )
			);
		}

		$list_items_markup = '';
		// Prime the cache for associated posts. This is copied from \WP_Widget_Recent_Comments::widget().
		$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
		_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

		foreach ( $comments as $comment ) {
			$list_items_markup .= '<li class="wp-block-custom-post-type-widget-blocks-latest-comments__comment wp-block-latest-comments__comment">';
			if ( $attributes['displayAvatar'] ) {
				$avatar = get_avatar(
					$comment,
					48,
					'',
					'',
					[
						'class' => 'wp-block-custom-post-type-widget-blocks-latest-comments__comment-avatar',
					]
				);
				if ( $avatar ) {
					$list_items_markup .= $avatar;
				}
			}

			$list_items_markup .= '<article>';
			$list_items_markup .= '<footer class="wp-block-custom-post-type-widget-blocks-latest-comments__comment-meta">';
			$author_url         = get_comment_author_url( $comment );
			if ( empty( $author_url ) && ! empty( $comment->user_id ) ) {
				$author_url = get_author_posts_url( $comment->user_id );
			}

			$author_markup = '';
			if ( $author_url ) {
				$author_markup .= '<a class="wp-block-custom-post-type-widget-blocks-latest-comments__comment-author" href="' . esc_url( $author_url ) . '">' . get_comment_author( $comment ) . '</a>';
			} else {
				$author_markup .= '<span class="wp-block-custom-post-type-widget-blocks-latest-comments__comment-author">' . get_comment_author( $comment ) . '</span>';
			}

			// `_draft_or_post_title` calls `esc_html()` so we don't need to wrap that call in
			// `esc_html`.
			$post_title = '<a class="wp-block-custom-post-type-widget-blocks-latest-comments__comment-link" href="' . esc_url( get_comment_link( $comment ) ) . '">' . $this->draft_or_post_title( $comment->comment_post_ID ) . '</a>';

			$list_items_markup .= sprintf(
				/* translators: 1: author name (inside <a> or <span> tag, based on if they have a URL), 2: post title related to this comment */
				__( '%1$s on %2$s', 'custom-post-type-widget-blocks' ),
				$author_markup,
				$post_title
			);

			if ( $attributes['displayDate'] ) {
				$list_items_markup .= sprintf(
					'<time datetime="%1$s" class="wp-block-custom-post-type-widget-blocks-latest-comments__comment-date">%2$s</time>',
					esc_attr( get_comment_date( 'c', $comment ) ),
					/* @phpstan-ignore-next-line */
					date_i18n( get_option( 'date_format' ), get_comment_date( 'U', $comment ) )
				);
			}
			$list_items_markup .= '</footer>';
			if ( $attributes['displayExcerpt'] ) {
				$list_items_markup .= '<div class="wp-block-custom-post-type-widget-blocks-latest-comments__comment-excerpt">' . wpautop( get_comment_excerpt( $comment ) ) . '</div>';
			}
			$list_items_markup .= '</article></li>';
		}

		if ( $attributes['displayAvatar'] ) {
			$classnames[] = 'has-avatars';
		}
		if ( $attributes['displayDate'] ) {
			$classnames[] = 'has-dates';
		}
		if ( $attributes['displayExcerpt'] ) {
			$classnames[] = 'has-excerpts';
		}

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classnames ) ] );

		return sprintf(
			'<ol %1$s>%2$s</ol>',
			$wrapper_attributes,
			$list_items_markup
		);
	}
}
