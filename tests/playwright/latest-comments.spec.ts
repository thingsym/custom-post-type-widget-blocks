/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { sliderRectOffSet, dummyText } from './utils/utils';

test.describe( '@custom-post-type-widget-blocks latest-comments', () => {
	test.beforeEach( async ({ page } ) => {
		await page.goto( '/wp-login.php' );
		await page.getByLabel( 'Username or Email Address' ).click();
		await page.getByLabel( 'Username or Email Address' ).fill( `${process.env.WP_USERNAME}` );
		await page.getByLabel( 'Username or Email Address' ).press( 'Tab' );
		await page.getByLabel( 'Password', { exact: true } ).fill( `${process.env.WP_PASSWORD}` );
		await page.getByRole( 'button', { name: 'Log In' } ).click();

		await page.goto( '/wp-admin/post-new.php' );
		await page.waitForLoadState();

		const isVisibleModal = await page.locator( '.components-modal__frame[role="Close dialogdialog"][aria-label="Welcome to the block editor"]' ).isVisible();
		if ( isVisibleModal ) {
			await page.locator( 'button[aria-label="Close dialog"]' ).click();
		}

		await expect( page.locator( '.components-modal__frame[role="Close dialogdialog"][aria-label="Welcome to the block editor"]' ) ).not.toBeVisible();
	} );

	test( 'insert block via Toggle block inserter / list select', async ( { editor, page } ) => {
		await expect( page.locator( '.is-mode-visual' ) ).toBeAttached();
		await page.getByRole( 'textbox', { name: 'Add title' } ).fill( 'test' );

		await page.getByLabel( 'Toggle block inserter', { exact: true } ).click();
		await page.waitForTimeout(1000);

		await expect(
			page.locator( '.block-editor-inserter__panel-content' ).getByLabel( 'Custom Post Type Widget Blocks', { exact: true } ).getByRole( 'option', { name: 'Latest Comments (Custom Post Type)' } )
		).toBeVisible();

		await page.locator( '.block-editor-inserter__panel-content' ).getByLabel( 'Custom Post Type Widget Blocks', { exact: true } ).getByRole( 'option', { name: 'Latest Comments (Custom Post Type)' } ).click();
		await page.waitForTimeout(1000);
		await expect(
			page.locator( 'div[data-type="custom-post-type-widget-blocks/latest-comments"]' )
		).toBeVisible();

		const content = await editor.getEditedPostContent();
		expect( content ).toBe(
			`<!-- wp:custom-post-type-widget-blocks/latest-comments /-->`
		);

		// await page.screenshot({ path: 'tests/playwright-screenshot.png' });

	} );

} );
