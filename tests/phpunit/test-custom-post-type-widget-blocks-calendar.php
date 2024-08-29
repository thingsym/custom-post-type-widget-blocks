<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Calendar
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Calendar extends WP_UnitTestCase {

	public $custom_post_type_widget_blocks_calendar;

	public function setUp(): void {
		parent::setUp();
		$this->custom_post_type_widget_blocks_calendar = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Calendar();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function constructor() {
		$this->assertSame( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_calendar, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function register_block_type() {
		$block_name = 'custom-post-type-widget-blocks/calendar';

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
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function render_callback() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'postType'  => 'post',
			'month'     => null,
			'year'      => null,
		];

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$render = $this->custom_post_type_widget_blocks_calendar->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#post_type=post#', $render );
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
			'month'     => null,
			'year'      => null,
		];

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$render = $this->custom_post_type_widget_blocks_calendar->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#post_type=test#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function get_day_link_custom_post_type() {
		$this->_register_post_type();

		$posts = $this->factory()->post->create_many(
			5,
			[
				'post_type' => 'test',
			]
		);

		$attributes = [
			'postType'  => 'test',
			'month'     => null,
			'year'      => null,
		];

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/archives/%post_id%' );

		$render = $this->custom_post_type_widget_blocks_calendar->render_callback( $attributes );

		$expected = 'http://example.org/archives/test/date/2019/08/13';

		$url = 'http://example.org/archives/date/2019/08/13';
		$actual = $this->custom_post_type_widget_blocks_calendar->get_day_link_custom_post_type( $url, '2019', '08', '13' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_calendar
	 */
	function get_month_link_custom_post_type() {
		$this->_register_post_type();

		$posts = $this->factory()->post->create_many(
			5,
			[
				'post_type' => 'test',
			]
		);

		$attributes = [
			'postType'  => 'test',
			'month'     => null,
			'year'      => null,
		];

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/archives/%post_id%' );

		$render = $this->custom_post_type_widget_blocks_calendar->render_callback( $attributes );

		$expected = 'http://example.org/archives/test/date/2019/08';

		$url = 'http://example.org/archives/date/2020/02';
		$actual = $this->custom_post_type_widget_blocks_calendar->get_month_link_custom_post_type( $url, '2019', '08' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Utility
	 */
	function _register_post_type() {
		$labels = [
			"name" => "test",
			"singular_name" => "test",
		];

		$args = [
			"label" => "test",
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"delete_with_user" => false,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => [ "slug" => "test", "with_front" => true ],
			"query_var" => true,
			"supports" => [ "title", "editor", "thumbnail", "comments" ],
		];

		register_post_type( "test", $args );
	}

}
