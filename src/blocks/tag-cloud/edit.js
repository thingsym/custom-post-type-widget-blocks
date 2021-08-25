'use strict';

/**
 * External dependencies
 */
import {
	map,
	filter,
} from 'lodash';

/**
 * WordPress dependencies
 */
import {
	Disabled,
	PanelBody,
	ToggleControl,
	SelectControl
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { store as coreStore } from '@wordpress/core-data';

export default function TagCloudEdit( { attributes, setAttributes } ) {
	const { taxonomy, showTagCounts } = attributes;

	const { taxonomies } = useSelect( ( select ) => {
		return {
			taxonomies: select( coreStore ).getTaxonomies( { per_page: -1 } ),
		};
	}, [] );

	const getTaxonomyOptions = () => {
		const selectOption = {
			label: __('- Select -', 'custom-post-type-widget-blocks'),
			value: '',
			disabled: true,
		};

		const taxonomyOptions = map(
			filter( taxonomies, {
				show_cloud: true,
				hierarchical: false,
			} ),
			( item ) => {
				return {
					value: item.slug,
					label: item.name,
				};
			}
		);

		return [ selectOption, ...taxonomyOptions ];
	};

	const inspectorControls = (
		<InspectorControls>
			<PanelBody title={ __( 'Tag Cloud settings', 'custom-post-type-widget-blocks' ) } >
				<SelectControl
					label={ __( 'Taxonomy', 'custom-post-type-widget-blocks' ) }
					options={ getTaxonomyOptions() }
					value={ taxonomy }
					onChange={ ( selectedTaxonomy ) =>
						setAttributes( { taxonomy: selectedTaxonomy } )
					}
				/>
				<ToggleControl
					label={ __( 'Show post counts', 'custom-post-type-widget-blocks' ) }
					checked={ showTagCounts }
					onChange={ () =>
						setAttributes( { showTagCounts: ! showTagCounts } )
					}
				/>
			</PanelBody>
		</InspectorControls>
	);

	return (
		<>
			{ inspectorControls }
			<div { ...useBlockProps() }>
				<Disabled>
					<ServerSideRender
						key="tag-cloud"
						block="custom-post-type-widget-blocks/tag-cloud"
						attributes={ attributes }
					/>
				</Disabled>
			</div>
		</>
	);
}
