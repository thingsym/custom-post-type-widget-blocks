<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Calendar
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Sample test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Calendar extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_calendar = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Calendar();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_calendar, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function register_block_type() {
		$this->assertContains( 'custom-post-type-widget-blocks/calendar', get_dynamic_block_names() );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function render_callback() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'postType'  => 'post',
			'align'     => 'left',
			'className' => '',
			'month'     => null,
			'year'      => null,
		];

		$render = $this->custom_post_type_widget_blocks_calendar->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=post#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
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
			'postType'  => 'test',
			'align'     => 'left',
			'className' => '',
			'month'     => null,
			'year'      => null,
		];

		$render = $this->custom_post_type_widget_blocks_calendar->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=test#', $render );

	}

}
