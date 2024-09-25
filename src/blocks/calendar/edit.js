'use strict';

/**
 * External dependencies
 */
import memoize from 'memize';

/**
 * WordPress dependencies
 */
import { calendar as icon } from '@wordpress/icons';
import {
	PanelBody,
	SelectControl,
	Disabled,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

/**
 * Returns the year and month of a specified date.
 *
 * @see `WP_REST_Posts_Controller::prepare_date_response()`.
 *
 * @param {string} date Date in `ISO8601/RFC3339` format.
 * @return {Object} Year and date of the specified date.
 */
const getYearMonth = memoize( ( date ) => {
	if ( ! date ) {
		return {};
	}
	const dateObj = new Date( date );
	return {
		year: dateObj.getFullYear(),
		month: dateObj.getMonth() + 1,
	};
} );

export default function CalendarEdit( { attributes, setAttributes } ) {
	const { postType } = attributes;

	const { postTypes } = useSelect( ( select ) => {
		return {
			postTypes: select( coreStore ).getPostTypes( {
				per_page: -1,
			} ),
		};
	}, [] );

	const getPostTypeOptions = () => {
		const selectOption = {
			label: __( '- Select -', 'custom-post-type-widget-blocks' ),
			value: '',
			disabled: true,
		};

		const postTypeOptions = ( postTypes ?? [] )
			.filter( ( pty ) => ( !! pty.viewable && ! pty.hierarchical ) && pty.slug !== 'attachment' )
			.map( ( item ) => {
				return {
					value: item.slug,
					label: item.name,
				};
			} );

		return [ selectOption, ...postTypeOptions ];
	};

	const blockProps = useBlockProps();
	const { date, hasPosts, hasPostsResolved } = useSelect( ( select ) => {
		const { getEntityRecords, hasFinishedResolution } = select( coreStore );

		const singlePublishedPostQuery = {
			status: 'publish',
			per_page: 1,
		};
		const posts = getEntityRecords(
			'postType',
			postType,
			singlePublishedPostQuery
		);
		const postsResolved = hasFinishedResolution( 'getEntityRecords', [
			'postType',
			postType,
			singlePublishedPostQuery,
		] );

		let _date;

		// FIXME: @wordpress/block-library should not depend on @wordpress/editor.
		// Blocks can be loaded into a *non-post* block editor.
		// eslint-disable-next-line @wordpress/data-no-store-string-literals
		const editorSelectors = select( 'core/editor' );
		if ( editorSelectors ) {
			const editedPostType = editorSelectors.getEditedPostAttribute( 'type' );
			// Dates are used to overwrite year and month used on the calendar.
			// This overwrite should only happen for 'post' post types.
			// For other post types the calendar always displays the current month.
			if ( editedPostType === 'post' ) {
				_date = editorSelectors.getEditedPostAttribute( 'date' );
			}
		}

		return {
			date: _date,
			hasPostsResolved: postsResolved,
			hasPosts: postsResolved && posts?.length === 1,
		};
	}, [
		postType,
	] );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Calendar settings', 'custom-post-type-widget-blocks' ) }
				>
					<SelectControl
						label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
						options={ getPostTypeOptions() }
						value={ postType }
						onChange={ ( selectedPostType ) =>
							setAttributes( { postType: selectedPostType } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="custom-post-type-widget-blocks/calendar"
					attributes={ { ...attributes, ...getYearMonth( date ) } }
				/>
			</Disabled>
		</div>
	);
}
