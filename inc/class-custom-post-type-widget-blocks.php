<?php
/**
 * Custom_Post_Type_Widget_Blocks class
 *
 * @package Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */

namespace Custom_Post_Type_Widget_Blocks;

/**
 * Core class Custom_Post_Type_Widget_Blocks
 *
 * @since 1.0.0
 */
class Custom_Post_Type_Widget_Blocks {
	public function __construct() {
		add_action( 'init', [ $this, 'register_styles' ] );
		add_action( 'init', [ $this, 'register_block_editor_scripts' ] );
		add_action( 'init', [ $this, 'register_block_editor_styles' ] );

		add_action( 'plugins_loaded', [ $this, 'init' ] );

		add_filter( 'block_categories', [ $this, 'add_block_categories' ], 10, 2 );
	}

	/**
	 * Load textdomain
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'custom-post-type-widget-blocks',
			false,
			CUSTOM_POST_TYPE_WIDGET_BLOCKS_PATH . '/languages'
		);
	}

	/**
	 * Set block editor translations
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function set_block_editor_translations() {
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'custom-post-type-widget-blocks-editor-script',
				'custom-post-type-widget-blocks',
				CUSTOM_POST_TYPE_WIDGET_BLOCKS_PATH . '/languages'
			);
		}
	}

	/**
	 * Add block categories
	 *
	 * @access public
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function add_block_categories( $categories ) {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'custom-post-type-widget-blocks',
					'title' => __( 'Custom Post Type Widget Blocks', 'custom-post-type-widget-blocks' ),
				],
			]
		);
	}

	public function register_block_editor_scripts() {
		$asset_file = include( CUSTOM_POST_TYPE_WIDGET_BLOCKS_PATH . 'dist/js/blocks.asset.php' );

		wp_register_script(
			'custom-post-type-widget-blocks-editor-script',
			plugins_url( 'dist/js/blocks.js', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}

	public function register_block_editor_styles() {
		wp_register_style(
			'custom-post-type-widget-blocks-editor-style',
			plugins_url( 'dist/css/block-editor-style.min.css', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			[],
			'20200408',
			'all'
		);
	}

	public function register_styles() {
		wp_register_style(
			'custom-post-type-widget-blocks-style',
			plugins_url( 'dist/css/blocks.min.css', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			[],
			'20200408',
			'all'
		);
	}

	public function init() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'set_block_editor_translations' ] );

		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Archives();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Calendar();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Categories();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Comments();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Tag_Cloud();
	}

}
