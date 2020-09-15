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
	}

	public function register_block_type() {
		register_block_type(
			'custom-post-type-widget-blocks/calendar',
			[
				'attributes'      => [
					'postType'  => [
						'type'    => 'string',
						'default' => 'post',
					],
					'align'     => [
						'type' => 'string',
						'enum' => [ 'left', 'center', 'right', 'wide', 'full' ],
					],
					'className' => [
						'type' => 'string',
					],
					'month'     => [
						'type' => 'integer',
					],
					'year'      => [
						'type' => 'integer',
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
		global $monthnum, $year;
		$this->posttype = $attributes['postType'];

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

		$custom_class_name = empty( $attributes['className'] ) ? '' : ' ' . $attributes['className'];
		$align_class_name  = empty( $attributes['align'] ) ? '' : ' ' . "align{$attributes['align']}";

		add_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ], 10, 3 );
		add_filter( 'day_link', [ $this, 'get_day_link_custom_post_type' ], 10, 4 );

		$output = sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( 'wp-block-custom-post-type-widget-blocks-calendar' . $custom_class_name . $align_class_name ),
			$this->get_custom_post_type_calendar( true, false )
		);

		remove_filter( 'month_link', [ $this, 'get_month_link_custom_post_type' ] );
		remove_filter( 'day_link', [ $this, 'get_day_link_custom_post_type' ] );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$monthnum = $previous_monthnum;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$year = $previous_year;

		return $output;
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
			$gotsome = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT 1 as test FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' LIMIT 1",
					array( $posttype )
				)
			);
			if ( ! $gotsome ) {
				$cache[ $key ] = '';
				wp_cache_set( 'get_custom_post_type_calendar', $cache, 'calendar' );
				return;
			}
		}

		if ( isset( $_GET['w'] ) ) {
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
			$d         = ( ( $w - 1 ) * 7 ) + 6;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$thismonth = $wpdb->get_var( "SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')" );
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
		$previous = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
				FROM $wpdb->posts
				WHERE post_date < %s
				AND post_type = %s AND post_status = 'publish'
					ORDER BY post_date DESC
					LIMIT 1",
				array(
					"$thisyear-$thismonth-01",
					$posttype,
				)
			)
		);
		$next     = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
				FROM $wpdb->posts
				WHERE post_date > %s
				AND post_type = %s AND post_status = 'publish'
					ORDER BY post_date ASC
					LIMIT 1",
				array(
					"$thisyear-$thismonth-{$last_day} 23:59:59",
					$posttype,
				)
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
		$dayswithposts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT DAYOFMONTH(post_date)
				FROM $wpdb->posts WHERE post_date >= %s
				AND post_type = %s AND post_status = 'publish'
				AND post_date <= %s",
				array(
					"{$thisyear}-{$thismonth}-01 00:00:00",
					$posttype,
					"{$thisyear}-{$thismonth}-{$last_day} 23:59:59",
				)
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
		if ( 0 != $pad ) {
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

			if ( current_time( 'j' ) == $day &&
				current_time( 'm' ) == $thismonth &&
				current_time( 'Y' ) == $thisyear ) {
				$calendar_output .= '<td class="today">';
			}
			else {
				$calendar_output .= '<td>';
			}

			if ( in_array( $day, $daywithpost ) ) {
				// any posts today?
				$date_format      = date( _x( 'F j, Y', 'daily archives date format', 'custom-post-type-widget-blocks' ), strtotime( "{$thisyear}-{$thismonth}-{$day}" ) );
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
			if ( 6 == calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
				$newrow = true;
			}
		}

		/* @phpstan-ignore-next-line */
		$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins );
		if ( 0 != $pad && 7 != $pad ) {
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
	 * @param string $daylink
	 * @param string $year
	 * @param string $month
	 * @param string $day
	 *
	 * @return string $daylink
	 */
	public function get_day_link_custom_post_type( $daylink, $year, $month, $day ) {
		global $wp_rewrite;

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

		$daylink = $wp_rewrite->get_day_permastruct();

		if ( ! empty( $daylink ) ) {
			$front = preg_replace( '/\/$/', '', $wp_rewrite->front );

			$daylink = str_replace( '%year%', $year, $daylink );
			$daylink = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $daylink );
			$daylink = str_replace( '%day%', zeroise( intval( $day ), 2 ), $daylink );

			if ( 'post' === $posttype ) {
				$daylink = home_url( user_trailingslashit( $daylink, 'day' ) );
			}
			else {
				$type_obj     = get_post_type_object( $posttype );
				$archive_name = ! empty( $type_obj->rewrite['slug'] ) ? $type_obj->rewrite['slug'] : $posttype;
				if ( $front ) {
					$new_front = $type_obj->rewrite['with_front'] ? $front : '';
					$daylink   = str_replace( $front, $new_front . '/' . $archive_name, $daylink );
					$daylink   = home_url( user_trailingslashit( $daylink, 'day' ) );
				}
				else {
					$daylink = home_url( user_trailingslashit( $archive_name . $daylink, 'day' ) );
				}
			}
		}
		else {
			$daylink = home_url( '?post_type=' . $posttype . '&m=' . $year . zeroise( $month, 2 ) . zeroise( $day, 2 ) );
		}

		return $daylink;
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

}
