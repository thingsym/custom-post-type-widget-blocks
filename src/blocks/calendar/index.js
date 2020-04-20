/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { calendar as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'custom-post-type-widget-blocks/calendar';

export const settings = {
	title: __('Calendar (Custom Post Type)', 'custom-post-type-widget-blocks'),
	description: __(
		'A calendar of your site’s posts.',
		'custom-post-type-widget-blocks'
	),
	icon,
	category: 'custom-post-type-widget-blocks',
	keywords: [
		__('posts', 'custom-post-type-widget-blocks'),
		__('archive', 'custom-post-type-widget-blocks'),
	],
	supports: {
		align: true,
	},
	example: {},
	edit,
};
