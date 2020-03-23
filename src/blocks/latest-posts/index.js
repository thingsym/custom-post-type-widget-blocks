/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { postList as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'custom-post-type-widget-blocks/latest-posts';

export const settings = {
	title: __( 'Latest Posts (Custom Post Type)', 'custom-post-type-widget-blocks' ),
	description: __( 'Display a list of your most recent posts.', 'custom-post-type-widget-blocks' ),
	icon,
	category: 'custom-post-type-widget-blocks',
	keywords: [ __( 'recent posts', 'custom-post-type-widget-blocks' ) ],
	supports: {
		align: true,
		html: false,
	},
	edit,
};
