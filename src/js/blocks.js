'use strict';

/**
 * WordPress dependencies
 */
import {
	registerBlockType,
} from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import * as custom_post_type_widget_blocks_archives from '../blocks/archives/index.js';
import * as custom_post_type_widget_blocks_calendar from '../blocks/calendar/index.js';
import * as custom_post_type_widget_blocks_categories from '../blocks/categories/index.js';
import * as custom_post_type_widget_blocks_latest_comments from '../blocks/latest-comments/index.js';
import * as custom_post_type_widget_blocks_latestPosts from '../blocks/latest-posts/index.js';
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
	const { metadata, settings, name } = block;
	registerBlockType( name, settings );
};

[
	// Common blocks are grouped at the top to prioritize their display
	// in various contexts — like the inserter and auto-complete components.
	custom_post_type_widget_blocks_archives,
	custom_post_type_widget_blocks_calendar,
	custom_post_type_widget_blocks_categories,
	custom_post_type_widget_blocks_latest_comments,
	custom_post_type_widget_blocks_latestPosts,
	custom_post_type_widget_blocks_search,
	custom_post_type_widget_blocks_tag_cloud,
].forEach( registerBlock );
