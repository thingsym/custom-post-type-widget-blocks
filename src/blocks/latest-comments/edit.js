/**
 * External dependencies
 */
import { map, filter, remove } from 'lodash';

/**
 * WordPress dependencies
 */
import { InspectorControls } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	RangeControl,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

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

class LatestComments extends Component {
	constructor() {
		super(...arguments);

		this.setPostType = this.setPostType.bind(this);
		this.setCommentsToShow = this.setCommentsToShow.bind(this);

		// Create toggles for each attribute; we create them here rather than
		// passing `this.createToggleAttribute( 'displayAvatar' )` directly to
		// `onChange` to avoid re-renders.
		this.toggleDisplayAvatar = this.createToggleAttribute('displayAvatar');
		this.toggleDisplayDate = this.createToggleAttribute('displayDate');
		this.toggleDisplayExcerpt = this.createToggleAttribute(
			'displayExcerpt'
		);
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

	createToggleAttribute(propName) {
		return () => {
			const value = this.props.attributes[propName];
			const { setAttributes } = this.props;

			setAttributes({ [propName]: !value });
		};
	}

	setCommentsToShow(commentsToShow) {
		this.props.setAttributes({ commentsToShow });
	}

	render() {
		const {
			postType,
			commentsToShow,
			displayAvatar,
			displayDate,
			displayExcerpt,
		} = this.props.attributes;
		const postTypeOptions = this.getPostTypeOptions();

		return (
			<>
				<InspectorControls>
					<PanelBody
						title={__(
							'Latest comments settings',
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
						<ToggleControl
							label={__(
								'Display Avatar',
								'custom-post-type-widget-blocks'
							)}
							checked={displayAvatar}
							onChange={this.toggleDisplayAvatar}
						/>
						<ToggleControl
							label={__(
								'Display Date',
								'custom-post-type-widget-blocks'
							)}
							checked={displayDate}
							onChange={this.toggleDisplayDate}
						/>
						<ToggleControl
							label={__(
								'Display Excerpt',
								'custom-post-type-widget-blocks'
							)}
							checked={displayExcerpt}
							onChange={this.toggleDisplayExcerpt}
						/>
						<RangeControl
							label={__(
								'Number of Comments',
								'custom-post-type-widget-blocks'
							)}
							value={commentsToShow}
							onChange={this.setCommentsToShow}
							min={MIN_COMMENTS}
							max={MAX_COMMENTS}
							required
						/>
					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						block="custom-post-type-widget-blocks/latest-comments"
						attributes={this.props.attributes}
					/>
				</Disabled>
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
})(LatestComments);
