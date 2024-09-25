'use strict';

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
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
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

	const serverSideAttributes = {
		...attributes,
		style: {
			...attributes?.style,
			spacing: undefined,
		},
	};

	const { postTypes } = useSelect( ( select ) => {
		return {
			postTypes: select( coreStore ).getPostTypes( {
				per_page: -1,
			} ),
		};
	}, [] );

	const getPostTypeOptions = () => {
		const selectOption = {
			label: __( 'All', 'custom-post-type-widget-blocks' ),
			value: 'any',
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

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Latest comments settings', 'custom-post-type-widget-blocks' ) } >
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
						options={ getPostTypeOptions() }
						value={ postType }
						onChange={ ( selectedPostType ) =>
							setAttributes( { postType: selectedPostType } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Display Avatar', 'custom-post-type-widget-blocks' ) }
						checked={ displayAvatar }
						onChange={ () =>
							setAttributes( { displayAvatar: ! displayAvatar } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Display Date', 'custom-post-type-widget-blocks' ) }
						checked={ displayDate }
						onChange={ () =>
							setAttributes( { displayDate: ! displayDate } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Display Excerpt', 'custom-post-type-widget-blocks' ) }
						checked={ displayExcerpt }
						onChange={ () =>
							setAttributes( {
								displayExcerpt: ! displayExcerpt,
							} )
						}
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
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
					attributes={ serverSideAttributes }
					// The preview uses the site's locale to make it more true to how
					// the block appears on the frontend. Setting the locale
					// explicitly prevents any middleware from setting it to 'user'.
					urlQueryArgs={ { _locale: 'site' } }
				/>
			</Disabled>
		</div>
	);
}
