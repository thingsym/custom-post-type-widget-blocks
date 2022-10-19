<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Search
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Search extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->custom_post_type_widget_blocks_search = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function constructor() {
		$this->assertSame( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_search, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function register_block_type() {
		$block_name = 'custom-post-type-widget-blocks/search';

		$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
		$this->assertArrayHasKey( $block_name, $block_types );

		$block_type = $block_types[ $block_name ];
		$this->assertTrue( $block_type->is_dynamic() );

		$this->assertContains( $block_name, get_dynamic_block_names() );

		$this->assertSame( 'custom-post-type-widget-blocks-editor-script', $block_type->editor_script );
		$this->assertSame( 'custom-post-type-widget-blocks-editor-style', $block_type->editor_style );
		$this->assertSame( 'custom-post-type-widget-blocks-style', $block_type->style );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function render_callback() {
		$attributes = [
			'postType'    => 'post',
			'label'       => 'Search',
			'placeholder' => '',
			'buttonText'  => 'Search',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#name="post_type" value="post"#', $render );

	}
	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function render_callback_case_custom_post_type() {
		$attributes = [
			'postType'    => 'test',
			'label'       => 'Search',
			'placeholder' => '',
			'buttonText'  => 'Search',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#name="post_type" value="test"#', $render );

		$attributes = [
			'postType'    => 'test',
			'label'       => '',
			'placeholder' => '',
			'buttonText'  => '',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#name="post_type" value="test"#', $render );
	}

}
