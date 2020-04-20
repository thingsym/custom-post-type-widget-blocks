<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Categories
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Sample test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Categories extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_categories = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Categories();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_categories
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_categories, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_categories
	 */
	function register_block_type() {
		$this->assertContains( 'custom-post-type-widget-blocks/categories', get_dynamic_block_names() );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_categories
	 */
	function render_callback() {
		$cat_1 = $this->factory->category->create_and_get( [ 'name' => 'Sample Category 1' ] );
		$this->factory->post->create( [ 'post_category' => [ $cat_1->term_id ] ] );
		$cat_2 = $this->factory->category->create_and_get( [ 'name' => 'Sample Category 2' ] );
		$this->factory->post->create( [ 'post_category' => [ $cat_2->term_id ] ] );
		$cat_3 = $this->factory->category->create_and_get( [ 'name' => 'Sample Category 3' ] );
		$this->factory->post->create( [ 'post_category' => [ $cat_3->term_id ] ] );

		$attributes = [
			'align'             => 'left',
			'className'         => '',
			'taxonomy'          => 'category',
			'displayAsDropdown' => false,
			'showHierarchy'     => false,
			'showPostCounts'    => false,
		];

		$render = $this->custom_post_type_widget_blocks_categories->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#Sample Category 1#', $render );
		$this->assertRegExp( '#Sample Category 2#', $render );
		$this->assertRegExp( '#Sample Category 3#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_categories
	 */
	function render_callback_none_category() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'align'             => 'left',
			'className'         => '',
			'taxonomy'          => 'category',
			'displayAsDropdown' => false,
			'showHierarchy'     => false,
			'showPostCounts'    => false,
		];

		$render = $this->custom_post_type_widget_blocks_categories->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#Uncategorized#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_categories
	 */
	function render_callback_custom_taxonomy() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
