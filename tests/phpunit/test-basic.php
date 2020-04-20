<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Basic
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Basic test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Basic extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks = new \Custom_Post_Type_Widget_Blocks\Custom_Post_Type_Widget_Blocks();
	}

	/**
	 * @test
	 * @group basic
	 */
	function basic() {
		$this->assertRegExp( '#/wp-content/plugins/custom-post-type-widget-blocks/custom-post-type-widget-blocks.php$#', __CUSTOM_POST_TYPE_WIDGET_BLOCKS__ );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Custom_Post_Type_Widget_Blocks' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'plugins_loaded', [ $this->custom_post_type_widget_blocks, 'init' ] ) );
		$this->assertEquals( 10, has_filter( 'block_categories', [ $this->custom_post_type_widget_blocks, 'add_block_categories' ] ) );

		$this->assertEquals( 10, has_filter( 'enqueue_block_assets', [ $this->custom_post_type_widget_blocks, 'enqueue_styles' ] ) );
		$this->assertEquals( 10, has_filter( 'enqueue_block_editor_assets', [ $this->custom_post_type_widget_blocks, 'enqueue_blocks_scripts' ] ) );
		$this->assertEquals( 10, has_filter( 'enqueue_block_editor_assets', [ $this->custom_post_type_widget_blocks, 'enqueue_block_editor_styles' ] ) );
		$this->assertEquals( 10, has_filter( 'enqueue_block_editor_assets', [ $this->custom_post_type_widget_blocks, 'load_block_editor_translations' ] ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	function init() {
		$this->custom_post_type_widget_blocks->init();

		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks, 'load_textdomain' ] ) );

		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Archives' ) );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Calendar' ) );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Categories' ) );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Comments' ) );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts' ) );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search' ) );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Tag_Cloud' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function load_textdomain() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function load_block_editor_translations() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function add_block_categories() {
		$expect[] = [
			'slug'  => 'custom-post-type-widget-blocks',
			'title' => __( 'Custom Post Type Widget Blocks', 'custom-post-type-widget-blocks' ),
		];
		$actual = $this->custom_post_type_widget_blocks->add_block_categories( [] );

		$this->assertIsArray( $actual );
		$this->assertEquals( $expect, $actual );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function enqueue_blocks_scripts() {
		$this->custom_post_type_widget_blocks->enqueue_blocks_scripts();
		$this->assertTrue( wp_script_is( 'custom-post-type-widget-blocks-script' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function enqueue_block_editor_styles() {
		$this->custom_post_type_widget_blocks->enqueue_block_editor_styles();
		$this->assertTrue( wp_style_is( 'custom-post-type-widget-blocks-editor-style' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function enqueue_styles() {
		$this->custom_post_type_widget_blocks->enqueue_styles();
		$this->assertTrue( wp_style_is( 'custom-post-type-widget-blocks-style' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	function uninstall() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
