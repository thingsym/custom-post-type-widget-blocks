/**
 * External dependencies
 */
import { get, isUndefined, pickBy, map, filter, remove } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Component, RawHTML } from '@wordpress/element';
import {
	BaseControl,
	PanelBody,
	Placeholder,
	QueryControls,
	RadioControl,
	RangeControl,
	Spinner,
	ToggleControl,
	ToolbarGroup,
	SelectControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import { dateI18n, format, __experimentalGetSettings } from '@wordpress/date';
import {
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls,
	__experimentalImageSizeControl as ImageSizeControl,
} from '@wordpress/block-editor';
import { withSelect } from '@wordpress/data';

/**
 * Module Constants
 */
const CATEGORIES_LIST_QUERY = {
	per_page: -1,
};
const MAX_POSTS_COLUMNS = 6;

class LatestPostsEdit extends Component {
	constructor() {
		super(...arguments);
		this.setPostType = this.setPostType.bind(this);
		this.state = {
			categoriesList: [],
			taxonomyState: undefined,
		};
	}

	getPostTypeOptions() {
		const postTypes = this.props.postTypes;

		const postTypeOptions = map(postTypes, (postType) => {
			return {
				value: postType.slug,
				label: postType.name,
			};
		});

		return [...postTypeOptions];
	}

	setPostType(postType) {
		const { setAttributes } = this.props;

		setAttributes({ postType });
		setAttributes({ taxonomy: undefined });
		setAttributes({ categories: undefined });
	}

	setTaxonomy() {
		const { taxonomies, setAttributes } = this.props;

		setAttributes({ taxonomyState: taxonomies[0].slug });
	}

	componentWillReceiveProps(nextProps) {
		const { taxonomies } = nextProps;
		const { postType } = nextProps.attributes;

		if (postType === 'post') {
			this.fetchRequest = apiFetch({
				path: addQueryArgs(`/wp/v2/categories`, CATEGORIES_LIST_QUERY),
			})
				.then((categoriesList) => {
					this.setState({ categoriesList });
					this.setState({ taxonomyState: 'category' });
				})
				.catch(() => {
					this.setState({ categoriesList: [] });
					this.setState({ taxonomyState: undefined });
				});
		} else if (postType != 'any') {
			if (taxonomies) {
				const rest_base = taxonomies[0].rest_base;
				this.fetchRequest = apiFetch({
					path: addQueryArgs(`/wp/v2/${rest_base}`),
				})
					.then((categoriesList) => {
						this.setState({ categoriesList });
						this.setState({ taxonomyState: taxonomies[0].slug });
					})
					.catch(() => {
						this.setState({ categoriesList: [] });
						this.setState({ taxonomyState: undefined });
					});
			}
		}
	}

	componentWillUnmount() {
		// this.isStillMounted = false;
	}

	render() {
		const {
			attributes,
			setAttributes,
			imageSizeOptions,
			latestPosts,
			defaultImageWidth,
			defaultImageHeight,
		} = this.props;
		const { categoriesList, taxonomyState } = this.state;
		const {
			displayFeaturedImage,
			displayPostContentRadio,
			displayPostContent,
			displayPostDate,
			postLayout,
			columns,
			order,
			orderBy,
			categories,
			postsToShow,
			excerptLength,
			featuredImageAlign,
			featuredImageSizeSlug,
			featuredImageSizeWidth,
			featuredImageSizeHeight,
		} = attributes;
		const postTypeOptions = this.getPostTypeOptions();
		const { postType, taxonomy } = this.props.attributes;

		const inspectorControls = (
			<InspectorControls>
				<PanelBody
					title={__(
						'Post content settings',
						'custom-post-type-widget-blocks'
					)}
				>
					<ToggleControl
						label={__(
							'Post Content',
							'custom-post-type-widget-blocks'
						)}
						checked={displayPostContent}
						onChange={(value) =>
							setAttributes({ displayPostContent: value })
						}
					/>
					{displayPostContent && (
						<RadioControl
							label={__(
								'Show:',
								'custom-post-type-widget-blocks'
							)}
							selected={displayPostContentRadio}
							options={[
								{
									label: __(
										'Excerpt',
										'custom-post-type-widget-blocks'
									),
									value: 'excerpt',
								},
								{
									label: __(
										'Full Post',
										'custom-post-type-widget-blocks'
									),
									value: 'full_post',
								},
							]}
							onChange={(value) =>
								setAttributes({
									displayPostContentRadio: value,
								})
							}
						/>
					)}
					{displayPostContent &&
						displayPostContentRadio === 'excerpt' && (
							<RangeControl
								label={__(
									'Max number of words in excerpt',
									'custom-post-type-widget-blocks'
								)}
								value={excerptLength}
								onChange={(value) =>
									setAttributes({ excerptLength: value })
								}
								min={10}
								max={100}
							/>
						)}
				</PanelBody>

				<PanelBody
					title={__(
						'Post meta settings',
						'custom-post-type-widget-blocks'
					)}
				>
					<ToggleControl
						label={__(
							'Display post date',
							'custom-post-type-widget-blocks'
						)}
						checked={displayPostDate}
						onChange={(value) =>
							setAttributes({ displayPostDate: value })
						}
					/>
				</PanelBody>

				<PanelBody
					title={__(
						'Featured Image Settings',
						'custom-post-type-widget-blocks'
					)}
				>
					<ToggleControl
						label={__(
							'Display featured image',
							'custom-post-type-widget-blocks'
						)}
						checked={displayFeaturedImage}
						onChange={(value) =>
							setAttributes({ displayFeaturedImage: value })
						}
					/>
					{displayFeaturedImage && (
						<>
							<ImageSizeControl
								onChange={(value) => {
									const newAttrs = {};
									if (value.hasOwnProperty('width')) {
										newAttrs.featuredImageSizeWidth =
											value.width;
									}
									if (value.hasOwnProperty('height')) {
										newAttrs.featuredImageSizeHeight =
											value.height;
									}
									setAttributes(newAttrs);
								}}
								slug={featuredImageSizeSlug}
								width={featuredImageSizeWidth}
								height={featuredImageSizeHeight}
								imageWidth={defaultImageWidth}
								imageHeight={defaultImageHeight}
								imageSizeOptions={imageSizeOptions}
								onChangeImage={(value) =>
									setAttributes({
										featuredImageSizeSlug: value,
										featuredImageSizeWidth: undefined,
										featuredImageSizeHeight: undefined,
									})
								}
							/>
							<BaseControl>
								<BaseControl.VisualLabel>
									{__(
										'Image Alignment',
										'custom-post-type-widget-blocks'
									)}
								</BaseControl.VisualLabel>
								<BlockAlignmentToolbar
									value={featuredImageAlign}
									onChange={(value) =>
										setAttributes({
											featuredImageAlign: value,
										})
									}
									controls={['left', 'center', 'right']}
									isCollapsed={false}
								/>
							</BaseControl>
						</>
					)}
				</PanelBody>

				<PanelBody
					title={__(
						'Sorting and filtering',
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
					<QueryControls
						{...{ order, orderBy }}
						numberOfItems={postsToShow}
						categoriesList={categoriesList}
						selectedCategoryId={categories}
						onOrderChange={(value) =>
							setAttributes({ order: value })
						}
						onOrderByChange={(value) =>
							setAttributes({ orderBy: value })
						}
						onCategoryChange={(value) =>
							setAttributes({
								taxonomy: taxonomyState
									? taxonomyState
									: undefined,
								categories: '' !== value ? value : undefined,
							})
						}
						onNumberOfItemsChange={(value) =>
							setAttributes({ postsToShow: value })
						}
					/>
					{postLayout === 'grid' && (
						<RangeControl
							label={__(
								'Columns',
								'custom-post-type-widget-blocks'
							)}
							value={columns}
							onChange={(value) =>
								setAttributes({ columns: value })
							}
							min={2}
							max={
								!hasPosts
									? MAX_POSTS_COLUMNS
									: Math.min(
											MAX_POSTS_COLUMNS,
											latestPosts.length
									  )
							}
							required
						/>
					)}
				</PanelBody>
			</InspectorControls>
		);

		const hasPosts = Array.isArray(latestPosts) && latestPosts.length;
		if (!hasPosts) {
			return (
				<>
					{inspectorControls}
					<Placeholder
						icon="admin-post"
						label={__(
							'Latest Posts',
							'custom-post-type-widget-blocks'
						)}
					>
						{!Array.isArray(latestPosts) ? (
							<Spinner />
						) : (
							__(
								'No posts found.',
								'custom-post-type-widget-blocks'
							)
						)}
					</Placeholder>
				</>
			);
		}

		// Removing posts from display should be instant.
		const displayPosts =
			latestPosts.length > postsToShow
				? latestPosts.slice(0, postsToShow)
				: latestPosts;

		const layoutControls = [
			{
				icon: 'list-view',
				title: __('List view', 'custom-post-type-widget-blocks'),
				onClick: () => setAttributes({ postLayout: 'list' }),
				isActive: postLayout === 'list',
			},
			{
				icon: 'grid-view',
				title: __('Grid view', 'custom-post-type-widget-blocks'),
				onClick: () => setAttributes({ postLayout: 'grid' }),
				isActive: postLayout === 'grid',
			},
		];

		const dateFormat = __experimentalGetSettings().formats.date;

		return (
			<>
				{inspectorControls}
				<BlockControls>
					<ToolbarGroup controls={layoutControls} />
				</BlockControls>
				<ul
					className={classnames(this.props.className, {
						'wp-block-custom-post-type-widget-blocks-latest-posts__list': true,
						'is-grid': postLayout === 'grid',
						'has-dates': displayPostDate,
						[`columns-${columns}`]: postLayout === 'grid',
					})}
				>
					{displayPosts.map((post, i) => {
						const titleTrimmed = post.title.rendered.trim();
						let excerpt = post.hasOwnProperty('excerpt')
							? post.excerpt.rendered
							: '';

						const excerptElement = document.createElement('div');
						excerptElement.innerHTML = excerpt;

						excerpt =
							excerptElement.textContent ||
							excerptElement.innerText ||
							'';

						const imageSourceUrl = post.featuredImageSourceUrl;

						const imageClasses = classnames({
							'wp-block-custom-post-type-widget-blocks-latest-posts__featured-image': true,
							[`align${featuredImageAlign}`]: !!featuredImageAlign,
						});

						return (
							<li key={i}>
								{displayFeaturedImage && (
									<div className={imageClasses}>
										{imageSourceUrl && (
											<img
												src={imageSourceUrl}
												alt=""
												style={{
													maxWidth: featuredImageSizeWidth,
													maxHeight: featuredImageSizeHeight,
												}}
											/>
										)}
									</div>
								)}
								<a
									href={post.link}
									target="_blank"
									rel="noreferrer noopener"
								>
									{titleTrimmed ? (
										<RawHTML>{titleTrimmed}</RawHTML>
									) : (
										__(
											'(no title)',
											'custom-post-type-widget-blocks'
										)
									)}
								</a>
								{displayPostDate && post.date_gmt && (
									<time
										dateTime={format('c', post.date_gmt)}
										className="wp-block-custom-post-type-widget-blocks-latest-posts__post-date"
									>
										{dateI18n(dateFormat, post.date_gmt)}
									</time>
								)}
								{displayPostContent &&
									displayPostContentRadio === 'excerpt' && (
										<div className="wp-block-custom-post-type-widget-blocks-latest-posts__post-excerpt">
											<RawHTML key="html">
												{excerptLength <
												excerpt.trim().split(' ').length
													? excerpt
															.trim()
															.split(
																' ',
																excerptLength
															)
															.join(' ') +
													  ' ... <a href=' +
													  post.link +
													  'target="_blank" rel="noopener noreferrer">' +
													  __(
															'Read more',
															'custom-post-type-widget-blocks'
													  ) +
													  '</a>'
													: excerpt
															.trim()
															.split(
																' ',
																excerptLength
															)
															.join(' ')}
											</RawHTML>
										</div>
									)}
								{displayPostContent &&
									displayPostContentRadio === 'full_post' && (
										<div className="wp-block-custom-post-type-widget-blocks-latest-posts__post-full-content">
											<RawHTML key="html">
												{post.content.raw.trim()}
											</RawHTML>
										</div>
									)}
							</li>
						);
					})}
				</ul>
			</>
		);
	}
}

export default withSelect((select, props) => {
	const {
		postType,
		taxonomy,
		categories,
		featuredImageSizeSlug,
		postsToShow,
		order,
		orderBy,
	} = props.attributes;
	const { getEntityRecords, getPostTypes, getTaxonomies, getMedia } = select(
		'core'
	);
	const { getSettings } = select('core/block-editor');
	const { imageSizes, imageDimensions } = getSettings();

	const postTypes = filter(getPostTypes(), {
		viewable: true,
		hierarchical: false,
	});
	remove(postTypes, { slug: 'attachment' });

	const taxonomies = filter(getTaxonomies(), {
		types: [postType],
		hierarchical: true,
	});

	let latestPostsQuery;

	if (postType === 'post') {
		latestPostsQuery = {
			categories,
			order,
			orderby: orderBy,
			per_page: postsToShow,
		};
	} else {
		latestPostsQuery = {
			order,
			orderby: orderBy,
			per_page: postsToShow,
		};
		latestPostsQuery[taxonomy] = categories;
	}

	latestPostsQuery = pickBy(latestPostsQuery, (value) => !isUndefined(value));

	const posts = getEntityRecords('postType', postType, latestPostsQuery);
	const imageSizeOptions = imageSizes
		.filter(({ slug }) => slug !== 'full')
		.map(({ name, slug }) => ({ value: slug, label: name }));

	return {
		postTypes: postTypes,
		taxonomies: taxonomies,
		defaultImageWidth: imageDimensions
			? imageDimensions[featuredImageSizeSlug].width
			: undefined,
		defaultImageHeight: imageDimensions
			? imageDimensions[featuredImageSizeSlug].height
			: undefined,
		imageSizeOptions,
		latestPosts: !Array.isArray(posts)
			? posts
			: posts.map((post) => {
					if (post.featured_media) {
						const image = getMedia(post.featured_media);
						let url = get(
							image,
							[
								'media_details',
								'sizes',
								featuredImageSizeSlug,
								'source_url',
							],
							null
						);
						if (!url) {
							url = get(image, 'source_url', null);
						}
						return { ...post, featuredImageSourceUrl: url };
					}
					return post;
			  }),
	};
})(LatestPostsEdit);
