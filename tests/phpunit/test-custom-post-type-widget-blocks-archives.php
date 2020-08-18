<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Archives
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Sample test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Archives extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_archives = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Archives();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_archives, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function register_block_type() {
		$block_name = 'custom-post-type-widget-blocks/archives';

		$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
		$this->assertArrayHasKey( $block_name, $block_types );

		$block_type = $block_types[ $block_name ];
		$this->assertTrue( $block_type->is_dynamic() );

		$this->assertContains( $block_name, get_dynamic_block_names() );

		$this->assertEquals( 'custom-post-type-widget-blocks-editor-script', $block_type->editor_script );
		$this->assertEquals( 'custom-post-type-widget-blocks-editor-style', $block_type->editor_style );
		$this->assertEquals( 'custom-post-type-widget-blocks-style', $block_type->style );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function render_callback() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'postType'          => 'post',
			'align'             => 'left',
			'className'         => '',
			'displayAsDropdown' => false,
			'showPostCounts'    => false,
		];

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=post#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function render_callback_no_post() {
		$attributes = [
			'postType'          => 'post',
			'align'             => 'left',
			'className'         => '',
			'displayAsDropdown' => false,
			'showPostCounts'    => false,
		];

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$this->assertRegExp( '#No archives to show.#', $render );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function render_callback_case_custom_post_type() {
		register_post_type(
			'test',
			[
				'public' => true,
				'has_archive' => true,
			]
		);

		$posts = $this->factory()->post->create_many(
			5,
			[
				'post_type' => 'test',
			]
		);

		$attributes = [
			'postType'          => 'test',
			'align'             => 'left',
			'className'         => '',
			'displayAsDropdown' => false,
			'showPostCounts'    => false,
		];

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=test#', $render );

		$attributes = [
			'postType'          => 'test',
			'align'             => 'left',
			'className'         => '',
			'displayAsDropdown' => true,
			'showPostCounts'    => true,
		];

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=test#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function get_month_link_custom_post_type() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function trim_post_type() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
