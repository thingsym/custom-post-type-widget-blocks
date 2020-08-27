'use strict';

/**
 * External dependencies
 */
import { map, filter, remove } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { withSelect } from '@wordpress/data';
import { PanelBody, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

class SearchEdit extends Component {
	constructor() {
		super(...arguments);

		this.setPostType = this.setPostType.bind(this);
	}

	getPostTypeOptions() {
		const postTypes = this.props.postTypes;

		const selectOption = {
			label: __('All', 'custom-post-type-widget-blocks'),
			value: 'any',
		};

		const postTypeOptions = map(postTypes, (postType) => {
			return {
				value: postType.slug,
				label: postType.name,
			};
		});

		return [selectOption, ...postTypeOptions];
	}

	setPostType(postType) {
		const { setAttributes } = this.props;

		setAttributes({ postType });
	}

	render() {
		const { className, setAttributes } = this.props;
		const {
			postType,
			label,
			placeholder,
			buttonText,
		} = this.props.attributes;
		const postTypeOptions = this.getPostTypeOptions();

		const inspectorControls = (
			<InspectorControls>
				<PanelBody
					title={__(
						'Search settings',
						'custom-post-type-widget-blocks'
					)}
				>
					<SelectControl
						label={__(
							'Post Type',
							'custom-post-type-widget-blocks'
						)}
						options={postTypeOptions}
						value={postType}
						onChange={this.setPostType}
					/>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<>
				{inspectorControls}
				<div className={className}>
					<RichText
						className="wp-block-custom-post-type-widget-blocks-search__label"
						aria-label={__(
							'Label text',
							'custom-post-type-widget-blocks'
						)}
						placeholder={__(
							'Add label…',
							'custom-post-type-widget-blocks'
						)}
						withoutInteractiveFormatting
						value={label}
						onChange={(html) => setAttributes({ label: html })}
					/>
					<input
						className="wp-block-custom-post-type-widget-blocks-search__input"
						aria-label={__(
							'Optional placeholder text',
							'custom-post-type-widget-blocks'
						)}
						// We hide the placeholder field's placeholder when there is a value. This
						// stops screen readers from reading the placeholder field's placeholder
						// which is confusing.
						placeholder={
							placeholder
								? undefined
								: __(
										'Optional placeholder…',
										'custom-post-type-widget-blocks'
								  )
						}
						value={placeholder}
						onChange={(event) =>
							setAttributes({ placeholder: event.target.value })
						}
					/>
					<RichText
						className="wp-block-custom-post-type-widget-blocks-search__button"
						aria-label={__(
							'Button text',
							'custom-post-type-widget-blocks'
						)}
						placeholder={__(
							'Add button text…',
							'custom-post-type-widget-blocks'
						)}
						withoutInteractiveFormatting
						value={buttonText}
						onChange={(html) => setAttributes({ buttonText: html })}
					/>
				</div>
			</>
		);
	}
}

export default withSelect((select) => {
	const { getPostTypes } = select('core');

	const postTypes = filter(getPostTypes(), {
		viewable: true,
		hierarchical: false,
	});
	remove(postTypes, { slug: 'attachment' });

	return {
		postTypes: postTypes,
	};
})(SearchEdit);
