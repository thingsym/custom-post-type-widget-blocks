'use strict';

/**
 * WordPress dependencies
 */
import {
	registerBlockType,
	unstable__bootstrapServerSideBlockDefinitions,
 } from '@wordpress/blocks';
import { __, _x } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as custom_post_type_widget_blocks_archives from '../blocks/archives/index.js';
import * as custom_post_type_widget_blocks_calendar from '../blocks/calendar/index.js';
import * as custom_post_type_widget_blocks_categories from '../blocks/categories/index.js';
import * as custom_post_type_widget_blocks_latest_comments from '../blocks/latest-comments/index.js';
import * as custom_post_type_widget_blocks_latest_posts from '../blocks/latest-posts/index.js';
import * as custom_post_type_widget_blocks_search from '../blocks/search/index.js';
import * as custom_post_type_widget_blocks_tag_cloud from '../blocks/tag-cloud/index.js';

// import * as test_custom_post_type_widget_blocks_latestPosts from '../blocks/test-latest-posts/index.js';

/**
 * Function to register an individual block.
 *
 * @param {Object} block The block to be registered.
 *
 */
const registerBlock = ( block ) => {
	if ( ! block ) {
		return;
	}

	let { metadata, settings, name } = block;

	if ( metadata ) {
		// for ServerSide Blocks
		unstable__bootstrapServerSideBlockDefinitions({ [name]: metadata });
	}

	[ metadata, settings ] = applyTextdomainMetadata( metadata, settings );

	registerBlockType( { name, ...metadata }, settings );
};

const applyTextdomainMetadata = ( metadata, settings ) => {
	if ( metadata ) {
		if ( !! metadata.title ) {
			metadata.title = _x( metadata.title, 'block title', 'custom-post-type-widget-blocks' );
			settings.title = metadata.title;
		}
		if ( !! metadata.description ) {
			metadata.description = _x( metadata.description, 'block description', 'custom-post-type-widget-blocks' );
			settings.description = metadata.description;
		}
		if ( !! metadata.keywords ) {
			metadata.keywords = __( metadata.keywords, 'custom-post-type-widget-blocks' );
			settings.keywords = metadata.keywords;
		}
	}

	return [ metadata, settings ];
}

[
	// Common blocks are grouped at the top to prioritize their display
	// in various contexts — like the inserter and auto-complete components.
	custom_post_type_widget_blocks_archives,
	custom_post_type_widget_blocks_calendar,
	custom_post_type_widget_blocks_categories,
	custom_post_type_widget_blocks_latest_comments,
	custom_post_type_widget_blocks_latest_posts,
	custom_post_type_widget_blocks_search,
	custom_post_type_widget_blocks_tag_cloud,
].forEach( registerBlock );
