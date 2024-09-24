<?php
/**
 * Class Test_Custom_Post_Type_Widget_Blocks_Search
 *
 * @package Custom_Post_Type_Widget_Blocks
 */

class Test_Custom_Post_Type_Widget_Blocks_Search extends WP_UnitTestCase {

	public $custom_post_type_widget_blocks_search;

	public function setUp(): void {
		parent::setUp();
		$this->custom_post_type_widget_blocks_search = new \Custom_Post_Type_Widget_Blocks\Blocks\Custom_Post_Type_Widget_Blocks_Search();
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function constructor() {
		$this->assertSame( 10, has_action( 'init', [ $this->custom_post_type_widget_blocks_search, 'register_block_type' ] ) );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function register_block_type() {
		$block_name = 'custom-post-type-widget-blocks/search';

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
	 * @group custom_post_type_widget_blocks_search
	 */
	function render_callback() {
		$attributes = [
			'postType'    => 'post',
			'label'       => 'Search',
			'placeholder' => '',
			'buttonText'  => 'Search',
			'buttonPosition' => '',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#name="post_type" value="post"#', $render );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	function render_callback_case_custom_post_type() {
		$attributes = [
			'postType'    => 'test',
			'label'       => 'Search',
			'placeholder' => '',
			'buttonText'  => 'Search',
			'buttonPosition' => '',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#name="post_type" value="test"#', $render );

		$attributes = [
			'postType'    => 'test',
			'label'       => '',
			'placeholder' => '',
			'buttonText'  => '',
			'buttonPosition' => '',
		];

		$render = $this->custom_post_type_widget_blocks_search->render_callback( $attributes );

		$this->assertIsString( $render );
		$this->assertMatchesRegularExpression( '#name="post_type" value="test"#', $render );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function classnames_for_block_core_search() {
		$attributes = [];
		$classnames = $this->custom_post_type_widget_blocks_search->classnames_for_block_core_search( $attributes );
		$this->assertSame( 'wp-block-search', $classnames );

		$attributes = [
			'postType'    => 'post',
			'label'       => 'Search',
			'placeholder' => '',
			'buttonText'  => 'Search',
			'buttonPosition' => '',
			'align'       => 'wide',
			'className'   => 'insertedclass',
		];
		$classnames = $this->custom_post_type_widget_blocks_search->classnames_for_block_core_search( $attributes );
		$this->assertSame( 'wp-block-search', $classnames );

	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function apply_block_core_search_border_style() {
		$wrapper_styles = '';
		$button_styles = '';
		$input_styles = '';
		$this->custom_post_type_widget_blocks_search->apply_block_core_search_border_style( '', '', '', $wrapper_styles, $button_styles, $input_styles );
		$this->assertEmpty( $wrapper_styles );
		$this->assertEmpty( $button_styles );
		$this->assertEmpty( $input_styles );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function apply_block_core_search_border_styles() {
		$wrapper_styles = '';
		$button_styles = '';
		$input_styles = '';
		$this->custom_post_type_widget_blocks_search->apply_block_core_search_border_styles( '', '', $wrapper_styles, $button_styles, $input_styles );
		$this->assertEmpty( $wrapper_styles );
		$this->assertEmpty( $button_styles );
		$this->assertEmpty( $input_styles );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function styles_for_block_core_search() {
		$styles = $this->custom_post_type_widget_blocks_search->styles_for_block_core_search( '' );
		$this->assertIsArray( $styles );
		$this->assertEmpty( $styles[ 'input' ] );
		$this->assertEmpty( $styles[ 'button' ] );
		$this->assertEmpty( $styles[ 'wrapper' ] );
		$this->assertEmpty( $styles[ 'label' ] );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function get_typography_classes_for_block_core_search() {
		$typography_classes = $this->custom_post_type_widget_blocks_search->get_typography_classes_for_block_core_search( '' );
		$this->assertEmpty( $typography_classes );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function get_typography_styles_for_block_core_search() {
		$typography_styles = $this->custom_post_type_widget_blocks_search->get_typography_styles_for_block_core_search( '' );
		$this->assertEmpty( $typography_styles );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function get_border_color_classes_for_block_core_search() {
		$border_color_classes = $this->custom_post_type_widget_blocks_search->get_border_color_classes_for_block_core_search( '' );
		$this->assertEmpty( $border_color_classes );
	}

	/**
	 * @test
	 * @group custom_post_type_widget_blocks_search
	 */
	public function get_color_classes_for_block_core_search() {
		$classnames = $this->custom_post_type_widget_blocks_search->get_color_classes_for_block_core_search( '' );
		$this->assertEmpty( $classnames );
	}
}
