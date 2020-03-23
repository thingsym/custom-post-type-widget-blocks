/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { search as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'custom-post-type-widget-blocks/search';

export const settings = {
	title: __( 'Search (Custom Post Type)', 'custom-post-type-widget-blocks' ),
	description: __( 'Help visitors find your content.', 'custom-post-type-widget-blocks' ),
	icon,
	category: 'custom-post-type-widget-blocks',
	keywords: [ __( 'find', 'custom-post-type-widget-blocks' ) ],
	supports: {
		align: true,
	},
	example: {},
	edit,
};
