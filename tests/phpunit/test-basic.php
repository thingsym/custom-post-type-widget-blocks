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
		$this->assertRegExp( '#/custom-post-type-widget-blocks/custom-post-type-widget-blocks.php$#', CUSTOM_POST_TYPE_WIDGET_BLOCKS );
		$this->assertTrue( class_exists( '\Custom_Post_Type_Widget_Blocks\Custom_Post_Type_Widget_Blocks' ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'plugins_loaded', [ $this->custom_post_type_widget_blocks, 'init' ] ) );
		$this->assertEquals( 10, has_filter( 'block_categories', [ $this->custom_post_type_widget_blocks, 'add_block_categories' ] ) );

		$this->assertEquals( 10, has_filter( 'init', [ $this->custom_post_type_widget_blocks, 'register_styles' ] ) );
		$this->assertEquals( 10, has_filter( 'init', [ $this->custom_post_type_widget_blocks, 'register_block_editor_scripts' ] ) );
		$this->assertEquals( 10, has_filter( 'init', [ $this->custom_post_type_widget_blocks, 'register_block_editor_styles' ] ) );
	}

	/**
	 * @test
	 * @group basic
	 */
	function init() {
		$this->custom_post_type_widget_blocks->init();

		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks, 'load_textdomain' ] ) );
		$this->assertEquals( 10, has_filter( 'enqueue_block_editor_assets', [ $this->custom_post_type_widget_blocks, 'set_block_editor_translations' ] ) );

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
	public function register_block_editor_scripts() {
		$this->custom_post_type_widget_blocks->register_block_editor_scripts();
		$this->assertArrayHasKey( 'custom-post-type-widget-blocks-editor-script', wp_scripts()->registered );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function register_block_editor_styles() {
		$this->custom_post_type_widget_blocks->register_block_editor_styles();
		$this->assertArrayHasKey( 'custom-post-type-widget-blocks-editor-style', wp_styles()->registered );
	}

	/**
	 * @test
	 * @group basic
	 */
	public function register_styles() {
		$this->custom_post_type_widget_blocks->register_styles();
		$this->assertArrayHasKey( 'custom-post-type-widget-blocks-style', wp_styles()->registered );
	}

	/**
	 * @test
	 * @group basic
	 */
	function uninstall() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
