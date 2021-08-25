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
	/**
	 * Public value.
	 *
	 * @access public
	 *
	 * @var array|null $plugin_data
	 */
	public $plugin_data;

	/**
	 * Public value.
	 *
	 * @access public
	 *
	 * @var array|null $asset_file
	 */
	public $asset_file;

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_plugin_data' ] );
		add_action( 'plugins_loaded', [ $this, 'load_asset_file' ] );

		add_action( 'plugins_loaded', [ $this, 'init' ] );
		add_action( 'plugins_loaded', [ $this, 'load_dynamic_blocks' ] );
	}

	public function init() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'set_block_editor_translations' ] );

		add_action( 'init', [ $this, 'register_block_editor_scripts' ] );
		add_action( 'init', [ $this, 'register_block_editor_styles' ] );
		add_action( 'init', [ $this, 'register_styles' ] );

		add_filter( 'block_categories_all', [ $this, 'add_block_categories' ], 10, 2 );

		add_filter( 'plugin_row_meta', array( $this, 'plugin_metadata_links' ), 10, 2 );
	}

	/**
	 * Load plugin data
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.1.2
	 */
	public function load_plugin_data() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->plugin_data = get_plugin_data( CUSTOM_POST_TYPE_WIDGET_BLOCKS );
	}

	/**
	 * Load asset file
	 *
	 * @access public
	 *
	 * @return void
	 *
	 * @since 1.1.2
	 */
	public function load_asset_file() {
		$this->asset_file = include plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . 'dist/js/blocks.asset.php';
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
			dirname( plugin_basename( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) ) . '/languages'
		);
	}

	/**
	 * Set links below a plugin on the Plugins page.
	 *
	 * Hooks to plugin_row_meta
	 *
	 * @see https://developer.wordpress.org/reference/hooks/plugin_row_meta/
	 *
	 * @access public
	 *
	 * @param array  $links  An array of the plugin's metadata.
	 * @param string $file   Path to the plugin file relative to the plugins directory.
	 *
	 * @return array $links
	 *
	 * @since 1.2.1
	 */
	public function plugin_metadata_links( $links, $file ) {
		if ( $file == plugin_basename( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) ) {
			$links[] = '<a href="https://github.com/sponsors/thingsym">' . __( 'Become a sponsor', 'custom-post-type-widget-blocks' ) . '</a>';
		}

		return $links;
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
				plugin_dir_path( CUSTOM_POST_TYPE_WIDGET_BLOCKS ) . 'languages'
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
		wp_register_script(
			'custom-post-type-widget-blocks-editor-script',
			plugins_url( 'dist/js/blocks.js', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			$this->asset_file['dependencies'],
			$this->asset_file['version'],
			true
		);
	}

	public function register_block_editor_styles() {
		wp_register_style(
			'custom-post-type-widget-blocks-editor-style',
			plugins_url( 'dist/css/block-editor-style.min.css', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			[],
			$this->plugin_data['Version'],
			'all'
		);
	}

	public function register_styles() {
		wp_register_style(
			'custom-post-type-widget-blocks-style',
			plugins_url( 'dist/css/blocks.min.css', CUSTOM_POST_TYPE_WIDGET_BLOCKS ),
			[],
			$this->plugin_data['Version'],
			'all'
		);
	}

	public function load_dynamic_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Archives();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Calendar();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Categories();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Comments();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search();
		new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Tag_Cloud();
	}

}
