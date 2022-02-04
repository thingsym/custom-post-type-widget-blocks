/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const variations = [
	{
		name: 'default',
		isDefault: true,
		attributes: {
			buttonText: __( 'Search', 'custom-post-type-widget-blocks' ),
			label: __( 'Search', 'custom-post-type-widget-blocks' )
		},
	},
];

export default variations;
