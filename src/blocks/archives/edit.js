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
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	Disabled,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { store as coreStore } from '@wordpress/core-data';

export default function ArchivesEdit( { attributes, setAttributes } ) {
	const { postType, archiveType, showPostCounts, displayAsDropdown, order } = attributes;

	const { postTypes } = useSelect( ( select ) => {
		return {
			postTypes: select( coreStore ).getPostTypes(),
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
				<PanelBody title={ __( 'Archives settings', 'custom-post-type-widget-blocks' ) } >
					<SelectControl
						label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
						options={ getPostTypeOptions() }
						value={ postType }
						onChange={ ( selectedPostType ) =>
							setAttributes( { postType: selectedPostType } )
						}
					/>
					<SelectControl
						label={ __( 'Archive Type', 'custom-post-type-widget-blocks' ) }
						options={ [
							{
								value: 'yearly',
								label: __( 'Yearly', 'custom-post-type-widget-blocks' ),
							},
							{
								value: 'monthly',
								label: __( 'Monthly', 'custom-post-type-widget-blocks' ),
							},
							{
								value: 'weekly',
								label: __( 'Weekly', 'custom-post-type-widget-blocks' ),
							},
							{
								value: 'daily',
								label: __( 'Daily', 'custom-post-type-widget-blocks' ),
							},
						] }
						value={ archiveType }
						onChange={ ( selectedArchiveType ) =>
							setAttributes( { archiveType: selectedArchiveType } )
						}
					/>
					<ToggleControl
						label={ __( 'Display as Dropdown', 'custom-post-type-widget-blocks' ) }
						checked={ displayAsDropdown }
						onChange={ () =>
							setAttributes( {
								displayAsDropdown: ! displayAsDropdown,
							} )
						}
					/>
					<ToggleControl
						label={ __( 'Show Post Counts', 'custom-post-type-widget-blocks' ) }
						checked={ showPostCounts }
						onChange={ () =>
							setAttributes( {
								showPostCounts: ! showPostCounts,
							} )
						}
					/>
					<SelectControl
						label={ __( 'Order', 'custom-post-type-widget-blocks' ) }
						options={ [
							{
								value: 'DESC',
								label: __( 'DESC', 'custom-post-type-widget-blocks' ),
							},
							{
								value: 'ASC',
								label: __( 'ASC', 'custom-post-type-widget-blocks' ),
							}
						] }
						value={ order }
						onChange={ ( selectedOrder ) =>
							setAttributes( { order: selectedOrder } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender
					block="custom-post-type-widget-blocks/archives"
					attributes={ attributes }
				/>
			</Disabled>
		</div>
	);
}
