'use strict';

/**
 * External dependencies
 */
import {
	map,
	filter,
	remove,
} from 'lodash';
import moment from 'moment';
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
	Spinner
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { store as coreStore } from '@wordpress/core-data';

const getYearMonth = memoize( ( date ) => {
	if ( ! date ) {
		return {};
	}
	const momentDate = moment( date );
	return {
		year: momentDate.year(),
		month: momentDate.month() + 1,
	};
} );

export default function CalendarEdit( { attributes, setAttributes } ) {
	const { postType } = attributes;

	const { postTypes } = useSelect( ( select ) => {
		return {
			postTypes: select( coreStore ).getPostTypes(),
		};
	}, [] );

	const getPostTypeOptions = () => {
		const selectOption = {
			label: __('- Select -', 'custom-post-type-widget-blocks'),
			value: '',
			disabled: true,
		};

		const postTypeOptions = map(
			filter( postTypes, {
				viewable: true,
				hierarchical: false,
			} ),
			( postType ) => {
				return {
					value: postType.slug,
					label: postType.name,
				};
			}
		);

		remove( postTypeOptions, { value: 'attachment' } );

		return [ selectOption, ...postTypeOptions ];
	}

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
			const postType = editorSelectors.getEditedPostAttribute( 'type' );
			// Dates are used to overwrite year and month used on the calendar.
			// This overwrite should only happen for 'post' post types.
			// For other post types the calendar always displays the current month.
			if ( postType === 'post' ) {
				_date = editorSelectors.getEditedPostAttribute( 'date' );
			}
		}

		return {
			date: _date,
			hasPostsResolved: postsResolved,
			hasPosts: postsResolved && posts?.length === 1,
		};
	}, [
		postType
	] );


	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __(
						'Calendar settings',
						'custom-post-type-widget-blocks'
					) }
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
			<div { ...blockProps }>
				{ ! hasPosts && (
						<Placeholder icon={ icon } label={ __( 'Calendar' ) }>
							{ ! hasPostsResolved ? (
								<Spinner />
							) : (
								__( 'No published posts found.' )
							) }
						</Placeholder>
				) }
				{ hasPosts && (
						<Disabled>
							<ServerSideRender
								block="custom-post-type-widget-blocks/calendar"
								attributes={ { ...attributes, ...getYearMonth( date ) } }
							/>
						</Disabled>
				) }
			</div>
		</>
	);
}
