'use strict';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { category as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'custom-post-type-widget-blocks/categories';

export const settings = {
	title: __(
		'Categories (Custom Post Type)',
		'custom-post-type-widget-blocks'
	),
	description: __(
		'Display a list of all categories.',
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
