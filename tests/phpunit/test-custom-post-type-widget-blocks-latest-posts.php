<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Latest_Posts
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Latest_Posts extends WP_UnitTestCase {

	public $custom_post_type_widget_blocks_latest_posts;

	public function setUp(): void {
		parent::setUp();
		$this->custom_post_type_widget_blocks_latest_posts = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Latest_Posts();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_posts
	 */
	function constructor() {
		$this->assertSame( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_latest_posts, 'register_block_type' ] ) );
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

		$this->assertSame( 'custom-post-type-widget-blocks-editor-script', $block_type->editor_script );
		$this->assertSame( 'custom-post-type-widget-blocks-editor-style', $block_type->editor_style );
		$this->assertSame( 'custom-post-type-widget-blocks-style', $block_type->style );
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
			'align'                   => 'left',
			'className'               => '',
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#http://example\.org/\?p=#', $render );
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
			'align'                   => 'left',
			'className'               => '',
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertEmpty( $render );
		$this->assertSame( '', $render );
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
			'align'                   => 'left',
			'className'               => '',
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#http://example\.org/\?test=#', $render );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_latest_posts
	 */
	function render_callback_case_options() {
		$posts = $this->factory->post->create_many( 5 );

		$attributes = [
			'postType'                => 'post',
			'taxonomy'                => 'category',
			'categories'              => '',
			'postsToShow'             => 5,
			'displayPostContent'      => false,
			'displayPostContentRadio' => 'excerpt',
			'excerptLength'           => 55,
			'displayPostDate'         => false,
			'postLayout'              => 'grid',
			'columns'                 => 3,
			'order'                   => 'desc',
			'orderBy'                 => 'date',
			'displayFeaturedImage'    => false,
			'featuredImageAlign'      => 'left',
			'featuredImageSizeSlug'   => 'thumbnail',
			'featuredImageSizeWidth'  => null,
			'featuredImageSizeHeight' => null,
			'align'                   => 'wide',
			'className'               => 'insertedclass',
		];

		$render = $this->custom_post_type_widget_blocks_latest_posts->render_callback( $attributes );

		$this->assertIsString( $render );

		// $this->assertMatchesRegularExpression( '/insertedclass/', $render );
		// $this->assertMatchesRegularExpression( '/alignwide/', $render );
		$this->assertMatchesRegularExpression( '/is\-grid/', $render );
	}

}
