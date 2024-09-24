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
 * Core class Custom_Post_Type_Widget_Blocks_Calendar
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks_Calendar {

	/**
	 * Public value.
	 *
	 * @access public
	 *
	 * @var string $posttype
	 */
	public $posttype;

	public function __construct() {
		add_action( 'init', [ $this, 'register_block_type' ] );

		// We only want to register these functions and actions when
		// we are on single sites. On multi sites we use `post_count` option.
		if ( ! is_multisite() ) {
			add_action( 'delete_post', [ $this, 'update_has_published_post_on_delete' ] );
			add_action( 'transition_post_status', [ $this, 'update_has_published_post_on_transition_post_status' ], 10, 3 );
		}
	}

	/**
	 * Uninstall.
	 *
	 * Hooks to uninstall_hook
	 *
	 * @access public static
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 */
	public static function uninstall() {
		delete_option( 'custom_post_type_widget_blocks_calendar_has_published_posts' );
	}

	/**
	 * register block_type from metadata
	 *
	 * @since 1.3.0
	 */
	public function register_block_type() {
		register_block_type_from_metadata(
			plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . '/dist/blocks/calendar',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the calendar block on server.
	 *
	 * @global int $monthnum.
	 * @global int $year.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the block content.
	 */
	public function render_callback( $attributes ) {
		$disable_get_links = 0;
		if ( defined( 'CUSTOM_POST_TYPE_WIDGET_BLOCKS_DISABLE_LINKS_CALENDAR' ) ) {
			if ( CUSTOM_POST_TYPE_WIDGET_BLOCKS_DISABLE_LINKS_CALENDAR ) {
				$disable_get_links = 1;
			}
		}

		global $monthnum, $year;
		$this->posttype = $attributes['postType'];

		// Calendar shouldn't be rendered
		// when there are no published posts on the site.
		if ( ! $this->has_published_posts( $this->posttype ) ) {
			if ( is_user_logged_in() ) {
				return '<div>' . __( 'The calendar block is hidden because there are no published posts.', 'custom-post-type-widget-blocks' ) . '</div>';
			}
			return '';
		}

		$previous_monthnum = $monthnum;
		$previous_year     = $year;

		if ( isset( $attributes['month'] ) && isset( $attributes['year'] ) ) {
			$permalink_structure = get_option( 'permalink_structure' );
			if (
				strpos( $permalink_structure, '%monthnum%' ) !== false &&
				strpos( $permalink_structure, '%year%' ) !== false
			) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$monthnum = $attributes['month'];
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$year = $attributes['year'];
			}
		}

		if ( ! $disable_get_links ) {
			add_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ], 10, 3 );
			add_filter( 'day_link', [ $this, 'get_day_link_custom_post_type' ], 10, 4 );
		}

		$color_block_styles = [];

		// Text color.
		$preset_text_color          = array_key_exists( 'textColor', $attributes ) ? "var:preset|color|{$attributes['textColor']}" : null;
		$custom_text_color          = $attributes['style']['color']['text'] ?? null;
		$color_block_styles['text'] = $preset_text_color ? $preset_text_color : $custom_text_color;

		// Background Color.
		$preset_background_color          = array_key_exists( 'backgroundColor', $attributes ) ? "var:preset|color|{$attributes['backgroundColor']}" : null;
		$custom_background_color          = $attributes['style']['color']['background'] ?? null;
		$color_block_styles['background'] = $preset_background_color ? $preset_background_color : $custom_background_color;

		// Generate color styles and classes.
		$styles        = wp_style_engine_get_styles( [ 'color' => $color_block_styles ], [ 'convert_vars_to_classnames' => true ] );
		$inline_styles = empty( $styles['css'] ) ? '' : sprintf( ' style="%s"', esc_attr( $styles['css'] ) );
		$classnames    = empty( $styles['classnames'] ) ? '' : ' ' . esc_attr( $styles['classnames'] );
		if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
			$classnames .= ' has-link-color';
		}
		// Apply color classes and styles to the calendar.
		$calendar = str_replace( '<table', '<table' . $inline_styles, $this->get_custom_post_type_calendar( true, false ) );
		$calendar = str_replace( 'class="wp-calendar-table', 'class="wp-calendar-table' . $classnames, $calendar );

		$wrapper_attributes = get_block_wrapper_attributes();

		$output = sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			$calendar
		);

		if ( ! $disable_get_links ) {
			remove_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ] );
			remove_filter( 'day_link', [ $this, 'get_day_link_custom_post_type' ] );
		}

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$monthnum = $previous_monthnum;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$year = $previous_year;

		return $output;
	}

	/**
	 * Returns whether or not there are any published posts.
	 *
	 * Used to hide the calendar block when there are no published posts.
	 * This compensates for a known Core bug: https://core.trac.wordpress.org/ticket/12016
	 *
	 * @since 5.9.0
	 *
	 * @return bool Has any published posts or not.
	 */
	public function has_published_posts( $posttype ) {
		// Multisite already has an option that stores the count of the published posts.
		// Let's use that for multisites.
		if ( is_multisite() ) {
			return 0 < (int) get_option( 'post_count' );
		}

		// On single sites we try our own cached option first.
		$option_has_published_posts = get_option( 'custom_post_type_widget_blocks_calendar_has_published_posts', null );

		if ( is_null( $option_has_published_posts ) || ! isset( $option_has_published_posts[ $posttype ] ) ) {
			return;
		}

		$has_published_posts = $option_has_published_posts[ $posttype ];

		if ( null !== $has_published_posts ) {
			return (bool) $has_published_posts;
		}

		// No cache hit, let's update the cache and return the cached value.
		return $this->update_has_published_posts( $posttype );
	}

	/**
	 * Queries the database for any published post and saves
	 * a flag whether any published post exists or not.
	 *
	 * @since 5.9.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return bool Has any published posts or not.
	 */
	public function update_has_published_posts( $posttype ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$has_published_posts = (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 as test FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' LIMIT 1",
				[ $posttype ]
			)
		);

		$option_has_published_posts = get_option( 'custom_post_type_widget_blocks_calendar_has_published_posts', null );
		$option_has_published_posts[ $posttype ] = $has_published_posts;

		update_option( 'custom_post_type_widget_blocks_calendar_has_published_posts', $option_has_published_posts );

		return $has_published_posts;
	}

	/**
	 * Handler for updating the has published posts flag when a post is deleted.
	 *
	 * @since 5.9.0
	 *
	 * @param int $post_id Deleted post ID.
	 */
	public function update_has_published_post_on_delete( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}

		$posttype = get_post_type( $post );

		return $this->update_has_published_posts( $posttype );
	}

	/**
	 * Handler for updating the has published posts flag when a post status changes.
	 *
	 * @since 5.9.0
	 *
	 * @param string  $new_status The status the post is changing to.
	 * @param string  $old_status The status the post is changing from.
	 * @param WP_Post $post       Post object.
	 */
	public function update_has_published_post_on_transition_post_status( $new_status, $old_status, $post ) {
		if ( $new_status === $old_status ) {
			return;
		}

		if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
			return;
		}

		$posttype = get_post_type( $post );

		return $this->update_has_published_posts( $posttype );
	}

	/**
	 * Extend the get_calendar for custom post type.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $initial
	 * @param boolean $echo
	 */
	public function get_custom_post_type_calendar( $initial = true, $echo = true ) {
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

		$posttype = $this->posttype;

		$key   = md5( $posttype . $m . $monthnum . $year );
		$cache = wp_cache_get( 'get_custom_post_type_calendar', 'calendar' );

		if ( $cache && is_array( $cache ) && isset( $cache[ $key ] ) ) {
			/**
			* Filters the HTML calendar output.
			*
			* @since 1.1.0
			*
			* @param string $calendar_output HTML output of the calendar.
			*/
			$output = apply_filters( 'custom_post_type_widget_blocks/calendar/get_custom_post_type_calendar', $cache[ $key ] );

			if ( $echo ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $output;
				return;
			}

			return $output;
		}

		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		// Quick check. If we have no posts at all, abort!
		if ( ! $posts ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$gotsome = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT 1 as test FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' LIMIT 1",
					[ $posttype ]
				)
			);
			if ( ! $gotsome ) {
				$cache[ $key ] = '';
				wp_cache_set( 'get_custom_post_type_calendar', $cache, 'calendar' );
				return;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['w'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$w = (int) $_GET['w'];
		}

		// week_begins = 0 stands for Sunday.
		$week_begins = (int) get_option( 'start_of_week' );

		// Let's figure out when we are.
		if ( ! empty( $monthnum ) && ! empty( $year ) ) {
			$thismonth = zeroise( intval( $monthnum ), 2 );
			$thisyear  = (int) $year;
		}
		elseif ( ! empty( $w ) ) {
			// We need to get the month from MySQL.
			$thisyear = (int) substr( $m, 0, 4 );
			// it seems MySQL's weeks disagree with PHP's.
			$d = ( ( $w - 1 ) * 7 ) + 6;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$thismonth = $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')"
			);
		}
		elseif ( ! empty( $m ) ) {
			$thisyear = (int) substr( $m, 0, 4 );
			if ( strlen( $m ) < 6 ) {
				$thismonth = '01';
			}
			else {
				$thismonth = zeroise( (int) substr( $m, 4, 2 ), 2 );
			}
		}
		else {
			$thisyear  = current_time( 'Y' );
			$thismonth = current_time( 'm' );
		}

		$unixmonth = mktime( 0, 0, 0, $thismonth, 1, $thisyear );
		$last_day  = date( 't', $unixmonth );

		// Get the next and previous month and year with at least one post.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$previous = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
				FROM $wpdb->posts
				WHERE post_date < %s
				AND post_type = %s AND post_status = 'publish'
					ORDER BY post_date DESC
					LIMIT 1",
				[
					"$thisyear-$thismonth-01",
					$posttype,
				]
			)
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$next = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
				FROM $wpdb->posts
				WHERE post_date > %s
				AND post_type = %s AND post_status = 'publish'
					ORDER BY post_date ASC
					LIMIT 1",
				[
					"$thisyear-$thismonth-{$last_day} 23:59:59",
					$posttype,
				]
			)
		);

		/* translators: Calendar caption: 1: month name, 2: 4-digit year */
		$calendar_caption = _x( '%1$s %2$s', 'calendar caption', 'custom-post-type-widget-blocks' );
		$calendar_output  = '<table class="wp-calendar wp-calendar-table">
		<caption>' . sprintf(
			$calendar_caption,
			$wp_locale->get_month( $thismonth ),
			date( 'Y', $unixmonth )
		) . '</caption>
		<thead>
		<tr>';

		$myweek = [];

		for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
		}

		foreach ( $myweek as $wd ) {
			$day_name         = $initial ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
			$wd               = esc_attr( $wd );
			$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
		}

		$calendar_output .= '
		</tr>
		</thead>
		<tbody>
		<tr>';

		$daywithpost = [];

		// Get days with posts.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$dayswithposts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT DAYOFMONTH(post_date)
				FROM $wpdb->posts WHERE post_date >= %s
				AND post_type = %s AND post_status = 'publish'
				AND post_date <= %s",
				[
					"{$thisyear}-{$thismonth}-01 00:00:00",
					$posttype,
					"{$thisyear}-{$thismonth}-{$last_day} 23:59:59",
				]
			),
			ARRAY_N
		);
		if ( $dayswithposts ) {
			foreach ( (array) $dayswithposts as $daywith ) {
				$daywithpost[] = $daywith[0];
			}
		}

		// See how much we should pad in the beginning.
		/* @phpstan-ignore-next-line */
		$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
		if ( (float) 0 !== $pad ) {
			/* @phpstan-ignore-next-line */
			$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';
		}

		$newrow      = false;
		$daysinmonth = (int) date( 't', $unixmonth );

		for ( $day = 1; $day <= $daysinmonth; ++$day ) {
			if ( $newrow ) {
				$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
			}
			$newrow = false;

			if ( current_time( 'j' ) === (string) $day &&
				current_time( 'm' ) === (string) $thismonth &&
				current_time( 'Y' ) === (string) $thisyear ) {
				$calendar_output .= '<td class="today">';
			}
			else {
				$calendar_output .= '<td>';
			}

			if ( in_array( (string) $day, $daywithpost, true ) ) {
				// any posts today?
				$date_format = date( _x( 'F j, Y', 'daily archives date format', 'custom-post-type-widget-blocks' ), strtotime( "{$thisyear}-{$thismonth}-{$day}" ) );
				/* translators: label: 1: date format */
				$label            = sprintf( __( 'Posts published on %s', 'custom-post-type-widget-blocks' ), $date_format );
				$calendar_output .= sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					get_day_link( $thisyear, $thismonth, $day ),
					esc_attr( $label ),
					$day
				);
			}
			else {
				$calendar_output .= $day;
			}
			$calendar_output .= '</td>';

			/* @phpstan-ignore-next-line */
			if ( (float) 6 === calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
				$newrow = true;
			}
		}

		/* @phpstan-ignore-next-line */
		$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins );
		if ( (float) 0 !== $pad && (float) 7 !== $pad ) {
			/* @phpstan-ignore-next-line */
			$calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';
		}

		$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

		$calendar_output .= '<nav aria-label="' . __( 'Previous and next months', 'custom-post-type-widget-blocks' ) . '" class="wp-calendar-nav">';

		if ( $previous ) {
			$calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-prev"><a href="' . get_month_link( $previous->year, $previous->month ) . '">&laquo; ' .
				$wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) .
			'</a></span>';
		} else {
			$calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-prev">&nbsp;</span>';
		}

		$calendar_output .= "\n\t\t" . '<span class="pad">&nbsp;</span>';

		if ( $next ) {
			$calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-next"><a href="' . get_month_link( $next->year, $next->month ) . '">' .
				$wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) .
			' &raquo;</a></span>';
		} else {
			$calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-next">&nbsp;</span>';
		}

		$calendar_output .= '
		</nav>';

		$cache[ $key ] = $calendar_output;
		wp_cache_set( 'get_custom_post_type_calendar', $cache, 'calendar' );

		$output = apply_filters( 'custom_post_type_widget_blocks/calendar/get_custom_post_type_calendar', $calendar_output );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $calendar_output;
			return;
		}
		else {
			return $calendar_output;
		}
	}

	/**
	 * Gets the day link for custom post type.
	 *
	 * Hooks to day_link
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string $old_daylink
	 * @param string $year
	 * @param string $month
	 * @param string $day
	 *
	 * @return string $new_daylink
	 */
	public function get_day_link_custom_post_type( $old_daylink, $year, $month, $day ) {
		$posttype = $this->posttype;

		if ( ! $year ) {
			$year = current_time( 'Y' );
		}
		if ( ! $month ) {
			$month = current_time( 'm' );
		}
		if ( ! $day ) {
			$day = current_time( 'j' );
		}

		global $wp_rewrite;
		$new_daylink = $wp_rewrite->get_day_permastruct();

		if ( ! empty( $new_daylink ) ) {
			$front = preg_replace( '/\/$/', '', $wp_rewrite->front );

			$new_daylink = str_replace( '%year%', $year, $new_daylink );
			$new_daylink = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $new_daylink );
			$new_daylink = str_replace( '%day%', zeroise( intval( $day ), 2 ), $new_daylink );

			if ( 'post' === $posttype ) {
				$new_daylink = home_url( user_trailingslashit( $new_daylink, 'day' ) );
			}
			else {
				$type_obj = get_post_type_object( $posttype );

				// The priority of the rewrite rule: has_archive < rewrite
				// See https://developer.wordpress.org/reference/functions/register_post_type/
				$archive_name = $posttype;
				if ( is_string( $type_obj->has_archive ) ) {
					$archive_name = $type_obj->has_archive;
				}
				if ( is_bool( $type_obj->rewrite ) && $type_obj->rewrite === true ) {
					$archive_name = $posttype;
				}
				elseif ( is_array( $type_obj->rewrite ) ) {
					if ( ! empty( $type_obj->rewrite['slug'] ) ) {
						$archive_name = $type_obj->rewrite['slug'];
					}
				}

				if ( $front ) {
					$new_front   = $type_obj->rewrite['with_front'] ? $front : '';
					$new_daylink = str_replace( $front, $new_front . '/' . $archive_name, $new_daylink );
					$new_daylink = home_url( user_trailingslashit( $new_daylink, 'day' ) );
				}
				else {
					$new_daylink = home_url( user_trailingslashit( $archive_name . $new_daylink, 'day' ) );
				}
			}
		}
		else {
			$new_daylink = home_url( '?post_type=' . $posttype . '&m=' . $year . zeroise( $month, 2 ) . zeroise( $day, 2 ) );
		}

		/**
		 * Filter a daylink.
		 *
		 * @since 1.4.0
		 *
		 * @param string $new_daylink
		 * @param string $year
		 * @param string $month
		 * @param string $day
		 * @param string $old_daylink
		 */
		return apply_filters( 'custom_post_type_widget_blocks/calendar/get_day_link_custom_post_type', $new_daylink, $year, $month, $day, $old_daylink );
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
				$type_obj = get_post_type_object( $posttype );

				// The priority of the rewrite rule: has_archive < rewrite
				// See https://developer.wordpress.org/reference/functions/register_post_type/
				$archive_name = $posttype;
				if ( is_string( $type_obj->has_archive ) ) {
					$archive_name = $type_obj->has_archive;
				}
				if ( is_bool( $type_obj->rewrite ) && $type_obj->rewrite === true ) {
					$archive_name = $posttype;
				}
				elseif ( is_array( $type_obj->rewrite ) ) {
					if ( ! empty( $type_obj->rewrite['slug'] ) ) {
						$archive_name = $type_obj->rewrite['slug'];
					}
				}

				if ( $front ) {
					$new_front     = $type_obj->rewrite['with_front'] ? $front : '';
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

}
