<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Latest_Comments
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Sample test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Latest_Comments extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_latest_comments = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Comments();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_comments
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_latest_comments, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_comments
	 */
	function register_block_type() {
		$this->assertContains( 'custom-post-type-widget-blocks/latest-comments', get_dynamic_block_names() );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_comments
	 */
	function render_callback() {
		$attributes = [
			'postType'       => 'any',
			'align'          => 'left',
			'className'      => '',
			'commentsToShow' => 5,
			'displayAvatar'  => true,
			'displayDate'    => true,
			'displayExcerpt' => true,
		];

		$render = $this->custom_post_type_widget_blocks_latest_comments->render_callback( $attributes );

		$this->assertIsString( $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_comments
	 */
	function render_callback_no_comment() {
		$attributes = [
			'postType'       => 'any',
			'align'          => 'left',
			'className'      => '',
			'commentsToShow' => 5,
			'displayAvatar'  => true,
			'displayDate'    => true,
			'displayExcerpt' => true,
		];

		$render = $this->custom_post_type_widget_blocks_latest_comments->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#No comments to show.#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_comments
	 */
	function render_callback_custom_post_type() {
		$this->markTestIncomplete( 'This test has not been implemented yet.' );
	}

}
