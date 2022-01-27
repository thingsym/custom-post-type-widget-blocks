/**
 * Internal dependencies
 */
import { metadata, name, settings } from '../../index';

describe( 'archives', () => {
	test( 'basic', () => {
		expect( name ).toBe( 'custom-post-type-widget-blocks/archives' );

		expect( metadata ).toBeTruthy();
		expect( metadata.category ).toBe( 'custom-post-type-widget-blocks' );
		expect( metadata.textdomain ).toBe( 'custom-post-type-widget-blocks' );

		expect( settings ).toBeTruthy();

		expect( settings.icon ).toBeTruthy();
		expect( settings.edit ).toBeTruthy();
	} );

} );
