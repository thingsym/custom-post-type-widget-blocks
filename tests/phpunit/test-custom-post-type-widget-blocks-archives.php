<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Archives
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Archives extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->custom_post_type_widget_blocks_archives = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Archives();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function constructor() {
		$this->assertSame( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_archives, 'register_block_type' ] ) );
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

		$this->assertSame( 'custom-post-type-widget-blocks-editor-script', $block_type->editor_script );
		$this->assertSame( 'custom-post-type-widget-blocks-editor-style', $block_type->editor_style );
		$this->assertSame( 'custom-post-type-widget-blocks-style', $block_type->style );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function render_callback() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'postType'          => 'post',
			'archiveType'       => 'monthly',
			'displayAsDropdown' => false,
			'showPostCounts'    => false,
			'order'             => 'DESC',
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
			'archiveType'       => 'monthly',
			'displayAsDropdown' => false,
			'showPostCounts'    => false,
			'order'             => 'DESC',
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
			'archiveType'       => 'monthly',
			'displayAsDropdown' => false,
			'showPostCounts'    => false,
			'order'             => 'DESC',
		];

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=test#', $render );

		$attributes = [
			'postType'          => 'test',
			'archiveType'       => 'monthly',
			'displayAsDropdown' => true,
			'showPostCounts'    => true,
			'order'             => 'DESC',
		];

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#post_type=test#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function get_year_link_custom_post_type() {
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

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/archives/%post_id%' );

		$expected = 'http://example.org/archives/test/date/2019';

		$url = 'http://example.org/archives/date/2019';
		$actual = $this->custom_post_type_widget_blocks_archives->get_year_link_custom_post_type( $url, '2019' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
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

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$expected = 'http://example.org/archives/test/date/2019/08/13';

		$url = 'http://example.org/archives/date/2019/08/13';
		$actual = $this->custom_post_type_widget_blocks_archives->get_day_link_custom_post_type( $url, '2019', '08', '13' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
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

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$expected = 'http://example.org/archives/test/date/2019/08';

		$url = 'http://example.org/archives/date/2020/02';
		$actual = $this->custom_post_type_widget_blocks_archives->get_month_link_custom_post_type( $url, '2019', '08' );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_archives
	 */
	function trim_post_type() {
		$attributes = [
			'postType'  => 'test',
			'month'     => null,
			'year'      => null,
		];

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/archives/%post_id%' );

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		$expected = 'http://example.org/archives/test/date/2019/08';

		$url = 'http://example.org/archives/test/date/2019/08?post_type=test';
		$actual = $this->custom_post_type_widget_blocks_archives->trim_post_type( $url );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * @test
	 * @group wp_custom_post_type_widgets_archives
	 */
	function trim_post_type_filter() {
		$attributes = [
			'postType'  => 'test',
			'month'     => null,
			'year'      => null,
		];

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/archives/%post_id%' );

		$render = $this->custom_post_type_widget_blocks_archives->render_callback( $attributes );

		add_filter( 'custom_post_type_widget_blocks/archive/trim_post_type', array( $this, '_filter_trim_post_type' ), 10, 3 );

		$expected = 'http://example.org/archives/test/date/2019/08?post_type=abc';

		$url = 'http://example.org/archives/test/date/2019/08?post_type=test';
		$actual = $this->custom_post_type_widget_blocks_archives->trim_post_type( $url );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * hook test
	 */
	function _filter_trim_post_type( $new_link_html, $link_html, $posttype ) {
		$this->assertSame( $new_link_html, 'http://example.org/archives/test/date/2019/08' );
		$this->assertSame( $link_html, 'http://example.org/archives/test/date/2019/08?post_type=test' );
		$this->assertSame( $posttype, 'test' );

		$new_link_html = $new_link_html . '?post_type=abc';

		return $new_link_html;
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
