<?php
/**
 * Plugin Name: Custom Post Type Widget Blocks
 * Plugin URI:  https://github.com/thingsym/custom-post-type-widget-blocks
 * Description: Custom Post Type Widgets for the Block Editor (Gutenberg).
 * Version:     0.1.0
 * Author:      thingsym
 * Author URI:  http://www.thingslabo.com/
 * License:     GPL2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-post-type-widget-blocks
 * Domain Path: /languages/
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( '__CUSTOM_POST_TYPE_WIDGET_BLOCKS__', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'inc/autoload.php';

new \Custom_Post_Type_Widget_Blocks\Custom_Post_Type_Widget_Blocks();
