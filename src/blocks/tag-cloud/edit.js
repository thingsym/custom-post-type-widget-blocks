'use strict';

/**
 * External dependencies
 */
import { map, filter } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	Disabled,
} from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

class TagCloudEdit extends Component {
	constructor() {
		super(...arguments);

		this.state = {
			editing: !this.props.attributes.taxonomy,
		};

		this.setTaxonomy = this.setTaxonomy.bind(this);
		this.toggleShowTagCounts = this.toggleShowTagCounts.bind(this);
	}

	getTaxonomyOptions() {
		const taxonomies = this.props.taxonomies;

		const selectOption = {
			label: __('- Select -', 'custom-post-type-widget-blocks'),
			value: '',
			disabled: true,
		};

		const taxonomyOptions = map(taxonomies, (taxonomy) => {
			return {
				value: taxonomy.slug,
				label: taxonomy.name,
			};
		});

		return [selectOption, ...taxonomyOptions];
	}

	setTaxonomy(taxonomy) {
		const { setAttributes } = this.props;

		setAttributes({ taxonomy });
	}

	toggleShowTagCounts() {
		const { attributes, setAttributes } = this.props;
		const { showTagCounts } = attributes;

		setAttributes({ showTagCounts: !showTagCounts });
	}

	render() {
		const { attributes } = this.props;
		const { taxonomy, showTagCounts } = attributes;
		const taxonomyOptions = this.getTaxonomyOptions();

		const inspectorControls = (
			<InspectorControls>
				<PanelBody
					title={__(
						'Tag Cloud settings',
						'custom-post-type-widget-blocks'
					)}
				>
					<SelectControl
						label={__('Taxonomy', 'custom-post-type-widget-blocks')}
						options={taxonomyOptions}
						value={taxonomy}
						onChange={this.setTaxonomy}
					/>
					<ToggleControl
						label={__(
							'Show post counts',
							'custom-post-type-widget-blocks'
						)}
						checked={showTagCounts}
						onChange={this.toggleShowTagCounts}
					/>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<>
				{inspectorControls}
				<Disabled>
					<ServerSideRender
						key="tag-cloud"
						block="custom-post-type-widget-blocks/tag-cloud"
						attributes={attributes}
					/>
				</Disabled>
			</>
		);
	}
}

export default withSelect((select) => {
	const { getTaxonomies } = select('core');

	const taxonomies = filter(getTaxonomies(), {
		show_cloud: true,
		hierarchical: false,
	});

	return {
		taxonomies: taxonomies,
	};
})(TagCloudEdit);
