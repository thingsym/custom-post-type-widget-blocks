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
		add_action( 'plugins_loaded', [ $this, 'init' ] );

		add_filter( 'block_categories', [ $this, 'add_block_categories' ], 10, 2 );

		add_action( 'enqueue_block_assets', [ $this, 'enqueue_styles' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_blocks_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_styles' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'load_block_editor_translations' ] );
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
	 * Load block editor translations
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function load_block_editor_translations() {
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'custom-post-type-widget-blocks-script',
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
	 * @return void
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

	public function enqueue_blocks_scripts() {
		$asset_file = include( CUSTOM_POST_TYPE_WIDGET_BLOCKS_PATH . 'dist/js/blocks.asset.php' );

		wp_enqueue_script(
			'custom-post-type-widget-blocks-script',
			plugins_url( 'dist/js/blocks.js', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}

	public function enqueue_block_editor_styles() {
		wp_enqueue_style(
			'custom-post-type-widget-blocks-editor-style',
			plugins_url( 'dist/css/block-editor-style.min.css', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			false,
			'20200408',
			'all'
		);
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			'custom-post-type-widget-blocks-style',
			plugins_url( 'dist/css/blocks.min.css', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			false,
			'20200408',
			'all'
		);
	}

	public function init() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'load_textdomain' ] );

		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Archives();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Calendar();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Categories();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Comments();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Tag_Cloud();
	}

}
