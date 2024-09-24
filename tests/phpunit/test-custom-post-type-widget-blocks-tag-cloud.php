<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Tag_Cloud
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Tag_Cloud extends WP_UnitTestCase {

	public $custom_post_type_widget_blocks_tag_cloud;

	public function setUp(): void {
		parent::setUp();
		$this->custom_post_type_widget_blocks_tag_cloud = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Tag_Cloud();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function constructor() {
		$this->assertSame( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_tag_cloud, 'register_block_type' ] ) );
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

		$this->assertSame( 'custom-post-type-widget-blocks-editor-script', $block_type->editor_script );
		$this->assertSame( 'custom-post-type-widget-blocks-editor-style', $block_type->editor_style );
		$this->assertSame( 'custom-post-type-widget-blocks-style', $block_type->style );
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
			'taxonomy'         => 'post_tag',
			'showTagCounts'    => false,
			'numberOfTags'     => 45,
			'smallestFontSize' => '8pt',
			'largestFontSize'  => '22pt',
		];

		$render = $this->custom_post_type_widget_blocks_tag_cloud->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#http://example\.org/\?tag=sample\-tag\-1#', $render );
		$this->assertMatchesRegularExpression( '#Sample tag 1#', $render );
		$this->assertMatchesRegularExpression( '#http://example\.org/\?tag=sample\-tag\-2#', $render );
		$this->assertMatchesRegularExpression( '#Sample tag 2#', $render );
		$this->assertMatchesRegularExpression( '#http://example\.org/\?tag=sample\-tag\-3#', $render );
		$this->assertMatchesRegularExpression( '#Sample tag 3#', $render );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function render_callback_no_tag() {
		$attributes = [
			'taxonomy'         => 'post_tag',
			'showTagCounts'    => false,
			'numberOfTags'     => 45,
			'smallestFontSize' => '8pt',
			'largestFontSize'  => '22pt',
		];

		$render = $this->custom_post_type_widget_blocks_tag_cloud->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertEmpty( $render );

		// TODO: wp_is_serving_rest_request
		// $this->assertMatchesRegularExpression( '#There&\#8217;s no content to show here yet.#', $render );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_tag_cloud
	 */
	function render_callback_custom_taxonomy() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
