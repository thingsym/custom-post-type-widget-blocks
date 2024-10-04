/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( '@initial login', () => {
	test( 'login', async ({ page } ) => {
		await page.goto( '/wp-login.php' );
		await page.getByLabel( 'Username or Email Address' ).click();
		await page.getByLabel( 'Username or Email Address' ).fill( `${process.env.WP_USERNAME}` );
		await page.getByLabel( 'Username or Email Address' ).press( 'Tab' );
		await page.getByLabel( 'Password', { exact: true } ).fill( `${process.env.WP_PASSWORD}` );
		await page.getByRole( 'button', { name: 'Log In' } ).click();
		await page.goto( '/wp-admin/' );

		const BASE_URL = process.env.WP_BASE_URL;

		await expect( page.url() ).toBe( `${BASE_URL}/wp-admin/` );
		await expect( page ).toHaveURL( '/wp-admin/' );
		await expect( page.url() ).toContain( '/wp-admin/' );
	} );
} );
