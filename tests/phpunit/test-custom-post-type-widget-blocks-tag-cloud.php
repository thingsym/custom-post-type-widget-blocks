<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Tag_Cloud
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Tag_Cloud extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_tag_cloud = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Tag_Cloud();
		$this->custom_post_type_widget_blocks_latest_posts = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_tag_cloud, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function register_block_type() {
		$block_name = 'custom-post-type-widget-blocks/tag-cloud';

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
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function render_callback() {
		$term_1 = $this->factory->term->create_and_get( [ 'name' => 'Sample tag 1' ] );
		$this->factory->post->create( [ 'tags_input' => [ $term_1->term_id ] ] );
		$term_2 = $this->factory->term->create_and_get( [ 'name' => 'Sample tag 2' ] );
		$this->factory->post->create( [ 'tags_input' => [ $term_2->term_id ] ] );
		$term_3 = $this->factory->term->create_and_get( [ 'name' => 'Sample tag 3' ] );
		$this->factory->post->create( [ 'tags_input' => [ $term_3->term_id ] ] );

		$attributes = [
			'taxonomy'      => 'post_tag',
			'align'         => 'left',
			'className'     => '',
			'showTagCounts' => false,
		];

		$render = $this->custom_post_type_widget_blocks_tag_cloud->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#http://example\.org/\?tag=sample\-tag\-1#', $render );
		$this->assertRegExp( '#Sample tag 1#', $render );
		$this->assertRegExp( '#http://example\.org/\?tag=sample\-tag\-2#', $render );
		$this->assertRegExp( '#Sample tag 2#', $render );
		$this->assertRegExp( '#http://example\.org/\?tag=sample\-tag\-3#', $render );
		$this->assertRegExp( '#Sample tag 3#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function render_callback_no_tag() {
		$attributes = [
			'taxonomy'      => 'post_tag',
			'align'         => 'left',
			'className'     => '',
			'showTagCounts' => false,
		];

		$render = $this->custom_post_type_widget_blocks_tag_cloud->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#Your site doesn&\#8217;t have any tags, so there&\#8217;s nothing to display here at the moment.#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function render_callback_custom_taxonomy() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
