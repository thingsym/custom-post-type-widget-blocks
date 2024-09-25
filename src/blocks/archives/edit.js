'use strict';

/**
 * WordPress dependencies
 */
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	Disabled,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

export default function ArchivesEdit( { attributes, setAttributes } ) {
	const { postType, showPostCounts, displayAsDropdown, archiveType, order } = attributes;

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
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Archives settings', 'custom-post-type-widget-blocks' ) } >
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
						options={ getPostTypeOptions() }
						value={ postType }
						onChange={ ( selectedPostType ) =>
							setAttributes( { postType: selectedPostType } )
						}
					/>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
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
						__nextHasNoMarginBottom
						label={ __( 'Display as Dropdown', 'custom-post-type-widget-blocks' ) }
						checked={ displayAsDropdown }
						onChange={ () =>
							setAttributes( {
								displayAsDropdown: ! displayAsDropdown,
							} )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show Post Counts', 'custom-post-type-widget-blocks' ) }
						checked={ showPostCounts }
						onChange={ () =>
							setAttributes( {
								showPostCounts: ! showPostCounts,
							} )
						}
					/>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Order', 'custom-post-type-widget-blocks' ) }
						options={ [
							{
								value: 'DESC',
								label: __( 'DESC', 'custom-post-type-widget-blocks' ),
							},
							{
								value: 'ASC',
								label: __( 'ASC', 'custom-post-type-widget-blocks' ),
							},
						] }
						value={ order }
						onChange={ ( selectedOrder ) =>
							setAttributes( { order: selectedOrder } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<Disabled>
					<ServerSideRender
						block="custom-post-type-widget-blocks/archives"
						skipBlockSupportAttributes
						attributes={ attributes }
					/>
				</Disabled>
			</div>
		</>
	);
}
