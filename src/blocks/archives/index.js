'use strict';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { archive as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'custom-post-type-widget-blocks/archives';

export const settings = {
	title: __('Archives (Custom Post Type)', 'custom-post-type-widget-blocks'),
	description: __(
		'Display a monthly archive of your posts.',
		'custom-post-type-widget-blocks'
	),
	icon,
	category: 'custom-post-type-widget-blocks',
	supports: {
		align: true,
		html: false,
	},
	edit,
};
