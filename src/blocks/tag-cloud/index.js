/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { tag as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'custom-post-type-widget-blocks/tag-cloud';

export const settings = {
	title: __('Tag Cloud (Custom Post Type)', 'custom-post-type-widget-blocks'),
	description: __(
		'A cloud of your most used tags.',
		'custom-post-type-widget-blocks'
	),
	icon,
	category: 'custom-post-type-widget-blocks',
	supports: {
		html: false,
		align: true,
	},
	edit,
};
