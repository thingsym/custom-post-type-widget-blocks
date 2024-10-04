/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( '@initial sample', () => {
	test( 'has title', async ({ page } ) => {
		await page.goto( '/' );

		await expect( page ).toHaveTitle( /test for e2e/ );
	} );

	test( 'get started link', async ({ page } ) => {
		await page.goto( '/' );
		await page.getByRole( 'link', { name: 'Sample Page', exact: true }).click();

		await expect( page ).toHaveURL( /.*page_id=2/ );
	} );

	test( 'site works', async ({ page } ) => {
		await page.goto( '/wp-login.php' );

		await expect( page.getByRole( 'heading', { name: 'Powered by WordPress' } ) ).toBeVisible();
	} );
} );
