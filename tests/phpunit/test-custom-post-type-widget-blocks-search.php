<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Search
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Sample test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Search extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_search = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_search, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function register_block_type() {
		$this->assertContains( 'custom-post-type-widget-blocks/search', get_dynamic_block_names() );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function render_callback() {
		$attributes = [
			'postType'    => 'post',
			'align'       => 'left',
			'className'   => '',
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
			'align'       => 'left',
			'className'   => '',
			'label'       => 'Search',
			'placeholder' => '',
			'buttonText'  => 'Search',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#name="post_type" value="test"#', $render );

	}

}
