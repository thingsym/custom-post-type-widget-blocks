<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Latest_Posts
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

/**
 * Sample test case.
 */
class Test_Custom_Post_Type_Widget_Blocks_Latest_Posts extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->custom_post_type_widget_blocks_latest_posts = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_posts
	 */
	function constructor() {
		$this->assertEquals( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_latest_posts, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_posts
	 */
	function register_block_type() {
		$block_name = 'custom-post-type-widget-blocks/latest-posts';

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
	 * @group custom_post_type_widget_blocks_latest_posts
	 */
	function render_callback() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'postType'                => 'post',
			'taxonomy'                => 'category',
			'categories'              => '',
			'align'                   => 'left',
			'className'               => '',
			'postsToShow'             => 5,
			'displayPostContent'      => false,
			'displayPostContentRadio' => 'excerpt',
			'excerptLength'           => 55,
			'displayPostDate'         => false,
			'postLayout'              => 'list',
			'columns'                 => 3,
			'order'                   => 'desc',
			'orderBy'                 => 'date',
			'displayFeaturedImage'    => false,
			'featuredImageAlign'      => 'left',
			'featuredImageSizeSlug'   => 'thumbnail',
			'featuredImageSizeWidth'  => null,
			'featuredImageSizeHeight' => null,
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#http://example\.org/\?p=#', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_posts
	 */
	function render_callback_no_post() {
		$attributes = [
			'postType'                => 'post',
			'taxonomy'                => 'category',
			'categories'              => '',
			'align'                   => 'left',
			'className'               => '',
			'postsToShow'             => 5,
			'displayPostContent'      => false,
			'displayPostContentRadio' => 'excerpt',
			'excerptLength'           => 55,
			'displayPostDate'         => false,
			'postLayout'              => 'list',
			'columns'                 => 3,
			'order'                   => 'desc',
			'orderBy'                 => 'date',
			'displayFeaturedImage'    => false,
			'featuredImageAlign'      => 'left',
			'featuredImageSizeSlug'   => 'thumbnail',
			'featuredImageSizeWidth'  => null,
			'featuredImageSizeHeight' => null,
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertEquals( '<ul class="wp-block-custom-post-type-widget-blocks-latest-posts wp-block-custom-post-type-widget-blocks-latest-posts__list alignleft "></ul>', $render );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_posts
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
			'postType'                => 'test',
			'taxonomy'                => '',
			'categories'              => '',
			'align'                   => 'left',
			'className'               => '',
			'postsToShow'             => 5,
			'displayPostContent'      => false,
			'displayPostContentRadio' => 'excerpt',
			'excerptLength'           => 55,
			'displayPostDate'         => false,
			'postLayout'              => 'list',
			'columns'                 => 3,
			'order'                   => 'desc',
			'orderBy'                 => 'date',
			'displayFeaturedImage'    => false,
			'featuredImageAlign'      => 'left',
			'featuredImageSizeSlug'   => 'thumbnail',
			'featuredImageSizeWidth'  => null,
			'featuredImageSizeHeight' => null,
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertRegExp( '#http://example\.org/\?test=#', $render );

	}

}
