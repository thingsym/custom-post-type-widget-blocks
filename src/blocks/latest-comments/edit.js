'use strict';

/**
 * External dependencies
 */
import {
	map,
	filter,
	remove,
} from 'lodash';

/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	RangeControl,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Minimum number of comments a user can show using this block.
 *
 * @type {number}
 */
const MIN_COMMENTS = 1;
/**
 * Maximum number of comments a user can show using this block.
 *
 * @type {number}
 */
const MAX_COMMENTS = 100;

export default function LatestComments( { attributes, setAttributes } ) {
	const {
		postType,
		commentsToShow,
		displayAvatar,
		displayDate,
		displayExcerpt,
	} = attributes;

	const { postTypes } = useSelect( ( select ) => {
		return {
			postTypes: select( coreStore ).getPostTypes({
				per_page: -1
			}),
		};
	}, [] );

	const getPostTypeOptions = () => {
		const selectOption = {
			label: __( 'All', 'custom-post-type-widget-blocks' ),
			value: 'any',
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

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Latest comments settings', 'custom-post-type-widget-blocks' ) } >
					<SelectControl
						label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
						options={ getPostTypeOptions() }
						value={ postType }
						onChange={ ( selectedPostType ) =>
							setAttributes( { postType: selectedPostType } )
						}
					/>
					<ToggleControl
						label={ __( 'Display Avatar', 'custom-post-type-widget-blocks' ) }
						checked={ displayAvatar }
						onChange={ () =>
							setAttributes( { displayAvatar: ! displayAvatar } )
						}
					/>
					<ToggleControl
						label={ __( 'Display Date',  'custom-post-type-widget-blocks' ) }
						checked={ displayDate }
						onChange={ () =>
							setAttributes( { displayDate: ! displayDate } )
						}
					/>
					<ToggleControl
						label={ __( 'Display Excerpt', 'custom-post-type-widget-blocks' ) }
						checked={ displayExcerpt }
						onChange={ () =>
							setAttributes( {
								displayExcerpt: ! displayExcerpt,
							} )
						}
					/>
					<RangeControl
						label={ __( 'Number of Comments', 'custom-post-type-widget-blocks' ) }
						value={ commentsToShow }
						onChange={ ( value ) =>
							setAttributes( { commentsToShow: value } )
						}
						min={ MIN_COMMENTS }
						max={ MAX_COMMENTS }
						required
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="custom-post-type-widget-blocks/latest-comments"
					attributes={ attributes }
				/>
			</Disabled>
		</div>
	);
}
