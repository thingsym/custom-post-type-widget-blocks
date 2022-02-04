/**
 * External dependencies
 */
import { get, includes, invoke, isUndefined, pickBy, map, filter, remove } from 'lodash';
import classnames from 'classnames';

/**
* WordPress dependencies
*/
import { RawHTML } from '@wordpress/element';
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
	Disabled,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n, format, __experimentalGetSettings } from '@wordpress/date';
import {
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls,
	__experimentalImageSizeControl as ImageSizeControl,
	useBlockProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { pin, list, grid } from '@wordpress/icons';
import { store as coreStore } from '@wordpress/core-data';

/**
* Internal dependencies
*/
import {
	MIN_EXCERPT_LENGTH,
	MAX_EXCERPT_LENGTH,
	MAX_POSTS_COLUMNS,
} from './constants';

/**
* Module Constants
*/
const CATEGORIES_LIST_QUERY = {
	per_page: -1,
	context: 'view',
};
const USERS_LIST_QUERY = {
	per_page: -1,
	has_published_posts: [ 'post' ],
	context: 'view',
};

export default function LatestPostsEdit( { attributes, setAttributes } ) {
	const {
		postType,
		postsToShow,
		order,
		orderBy,
		categories,
		selectedAuthor,
		displayFeaturedImage,
		displayPostContentRadio,
		displayPostContent,
		displayPostDate,
		displayAuthor,
		postLayout,
		columns,
		excerptLength,
		featuredImageAlign,
		featuredImageSizeSlug,
		featuredImageSizeWidth,
		featuredImageSizeHeight,
		addLinkToFeaturedImage,
	} = attributes;

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

	const {
		imageSizeOptions,
		latestPosts,
		defaultImageWidth,
		defaultImageHeight,
		categoriesList,
		authorList,
		postTypes,
	} = useSelect(
		( select ) => {
			const { getEntityRecords, getMedia, getUsers } = select(
				coreStore
			);
			const { getSettings } = select( blockEditorStore );
			const { imageSizes, imageDimensions } = getSettings();
			const catIds =
				categories && categories.length > 0
					? categories.map( ( cat ) => cat.id )
					: [];

			let latestPostsParam = {
				author: selectedAuthor,
				order,
				orderby: orderBy,
				per_page: postsToShow,
			};

			let taxonomy = undefined;

			if ( postType === 'post' ) {
				latestPostsParam[categories] = catIds;
				taxonomy = 'category';
			} else {
				latestPostsParam[taxonomy] = categories;
				taxonomy = 'category';
			}

			const latestPostsQuery = pickBy(
				latestPostsParam,
				( value ) => !isUndefined( value )
			);

			const posts = getEntityRecords(
				'postType',
				postType,
				latestPostsQuery
			);

			return {
				defaultImageWidth: get(
					imageDimensions,
					[ featuredImageSizeSlug, 'width' ],
					0
				),
				defaultImageHeight: get(
					imageDimensions,
					[ featuredImageSizeSlug, 'height' ],
					0
				),
				imageSizeOptions: imageSizes
					.filter( ( { slug } ) => slug !== 'full' )
					.map( ( { name, slug } ) => ( {
						value: slug,
						label: name,
					} ) ),
				latestPosts: ! Array.isArray( posts )
					? posts
					: posts.map( ( post ) => {
							if ( ! post.featured_media ) return post;

							const image = getMedia( post.featured_media );
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
							if ( ! url ) {
								url = get( image, 'source_url', null );
							}
							const featuredImageInfo = {
								url,
								// eslint-disable-next-line camelcase
								alt: image?.alt_text,
							};
							return { ...post, featuredImageInfo };
						} ),
				categoriesList: getEntityRecords(
					'taxonomy',
					taxonomy,
					CATEGORIES_LIST_QUERY
				),
				authorList: getUsers( USERS_LIST_QUERY ),
				postTypes: select( coreStore ).getPostTypes(),
			};
		},
		[
			featuredImageSizeSlug,
			postsToShow,
			order,
			orderBy,
			categories,
			selectedAuthor,
			postType,
		]
	);

	const categorySuggestions =
		categoriesList?.reduce(
			( accumulator, category ) => ( {
				...accumulator,
				[ category.name ]: category,
			} ),
			{}
		) ?? {};

	const selectCategories = ( tokens ) => {
		const hasNoSuggestion = tokens.some(
			( token ) =>
				typeof token === 'string' && ! categorySuggestions[ token ]
		);
		if ( hasNoSuggestion ) {
			return;
		}
		// Categories that are already will be objects, while new additions will be strings (the name).
		// allCategories nomalizes the array so that they are all objects.
		const allCategories = tokens.map( ( token ) => {
			return typeof token === 'string'
				? categorySuggestions[ token ]
				: token;
		} );
		// We do nothing if the category is not selected
		// from suggestions.
		if ( includes( allCategories, null ) ) {
			return false;
		}
		setAttributes( { categories: allCategories } );
	};

	const hasPosts = !! latestPosts?.length;
	const inspectorControls = (
		<InspectorControls>
			<PanelBody title={ __( 'Post content settings', 'custom-post-type-widget-blocks' ) }>
				<ToggleControl
					label={ __( 'Post content', 'custom-post-type-widget-blocks' ) }
					checked={ displayPostContent }
					onChange={ ( value ) =>
						setAttributes( { displayPostContent: value } )
					}
				/>
				{ displayPostContent && (
					<RadioControl
						label={ __( 'Show:', 'custom-post-type-widget-blocks' ) }
						selected={ displayPostContentRadio }
						options={ [
							{ label: __( 'Excerpt', 'custom-post-type-widget-blocks' ), value: 'excerpt' },
							{
								label: __( 'Full post', 'custom-post-type-widget-blocks' ),
								value: 'full_post',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( {
								displayPostContentRadio: value,
							} )
						}
					/>
				) }
				{ displayPostContent &&
					displayPostContentRadio === 'excerpt' && (
						<RangeControl
							label={ __( 'Max number of words in excerpt', 'custom-post-type-widget-blocks' ) }
							value={ excerptLength }
							onChange={ ( value ) =>
								setAttributes( { excerptLength: value } )
							}
							min={ MIN_EXCERPT_LENGTH }
							max={ MAX_EXCERPT_LENGTH }
						/>
					) }
			</PanelBody>

			<PanelBody title={ __( 'Post meta settings', 'custom-post-type-widget-blocks' ) }>
				<ToggleControl
					label={ __( 'Display author name', 'custom-post-type-widget-blocks' ) }
					checked={ displayAuthor }
					onChange={ ( value ) =>
						setAttributes( { displayAuthor: value } )
					}
				/>
				<ToggleControl
					label={ __( 'Display post date', 'custom-post-type-widget-blocks' ) }
					checked={ displayPostDate }
					onChange={ ( value ) =>
						setAttributes( { displayPostDate: value } )
					}
				/>
			</PanelBody>

			<PanelBody title={ __( 'Featured image settings', 'custom-post-type-widget-blocks' ) }>
				<ToggleControl
					label={ __( 'Display featured image', 'custom-post-type-widget-blocks' ) }
					checked={ displayFeaturedImage }
					onChange={ ( value ) =>
						setAttributes( { displayFeaturedImage: value } )
					}
				/>
				{ displayFeaturedImage && (
					<>
						<ImageSizeControl
							onChange={ ( value ) => {
								const newAttrs = {};
								if ( value.hasOwnProperty( 'width' ) ) {
									newAttrs.featuredImageSizeWidth =
										value.width;
								}
								if ( value.hasOwnProperty( 'height' ) ) {
									newAttrs.featuredImageSizeHeight =
										value.height;
								}
								setAttributes( newAttrs );
							} }
							slug={ featuredImageSizeSlug }
							width={ featuredImageSizeWidth }
							height={ featuredImageSizeHeight }
							imageWidth={ defaultImageWidth }
							imageHeight={ defaultImageHeight }
							imageSizeOptions={ imageSizeOptions }
							onChangeImage={ ( value ) =>
								setAttributes( {
									featuredImageSizeSlug: value,
									featuredImageSizeWidth: undefined,
									featuredImageSizeHeight: undefined,
								} )
							}
						/>
						<BaseControl className="block-editor-image-alignment-control__row">
							<BaseControl.VisualLabel>
								{ __( 'Image alignment', 'custom-post-type-widget-blocks' ) }
							</BaseControl.VisualLabel>
							<BlockAlignmentToolbar
								value={ featuredImageAlign }
								onChange={ ( value ) =>
									setAttributes( {
										featuredImageAlign: value,
									} )
								}
								controls={ [ 'left', 'center', 'right' ] }
								isCollapsed={ false }
							/>
						</BaseControl>
						<ToggleControl
							label={ __( 'Add link to featured image', 'custom-post-type-widget-blocks' ) }
							checked={ addLinkToFeaturedImage }
							onChange={ ( value ) =>
								setAttributes( {
									addLinkToFeaturedImage: value,
								} )
							}
						/>
					</>
				) }
			</PanelBody>

			<PanelBody title={ __( 'Sorting and filtering', 'custom-post-type-widget-blocks' ) }>
				<SelectControl
					label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
					options={ getPostTypeOptions() }
					value={ postType }
					onChange={ ( selectedPostType ) =>
						setAttributes( { postType: selectedPostType } )
					}
				/>
				<QueryControls
					{ ...{ order, orderBy } }
					numberOfItems={ postsToShow }
					onOrderChange={ ( value ) =>
						setAttributes( { order: value } )
					}
					onOrderByChange={ ( value ) =>
						setAttributes( { orderBy: value } )
					}
					onNumberOfItemsChange={ ( value ) =>
						setAttributes( { postsToShow: value } )
					}
					categorySuggestions={ categorySuggestions }
					onCategoryChange={ selectCategories }
					selectedCategories={ categories }
					onAuthorChange={ ( value ) =>
						setAttributes( {
							selectedAuthor:
								'' !== value ? Number( value ) : undefined,
						} )
					}
					authorList={ authorList ?? [] }
					selectedAuthorId={ selectedAuthor }
				/>

				{ postLayout === 'grid' && (
					<RangeControl
						label={ __( 'Columns', 'custom-post-type-widget-blocks' ) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value } )
						}
						min={ 2 }
						max={
							! hasPosts
								? MAX_POSTS_COLUMNS
								: Math.min(
										MAX_POSTS_COLUMNS,
										latestPosts.length
									)
						}
						required
					/>
				) }
			</PanelBody>
		</InspectorControls>
	);

	const blockProps = useBlockProps( {
		className: classnames( {
			'wp-block-custom-post-type-widget-blocks-latest-posts__list': true,
			'is-grid': postLayout === 'grid',
			'has-dates': displayPostDate,
			'has-author': displayAuthor,
			[ `columns-${ columns }` ]: postLayout === 'grid',
		} ),
	} );

	if ( ! hasPosts && postType !== 'any' ) {
		return (
			<div { ...blockProps }>
				{ inspectorControls }
				<Placeholder icon={ pin } label={ __( 'Latest Posts (Custom Post Type)', 'custom-post-type-widget-blocks' ) }>
					{ ! Array.isArray( latestPosts ) ? (
						<Spinner />
					) : (
						__( 'No posts found.', 'custom-post-type-widget-blocks' )
					) }
				</Placeholder>
			</div>
		);
	}

	// Removing posts from display should be instant.
	const displayPosts =
		latestPosts.length > postsToShow
			? latestPosts.slice( 0, postsToShow )
			: latestPosts;

	const layoutControls = [
		{
			icon: list,
			title: __( 'List view', 'custom-post-type-widget-blocks' ),
			onClick: () => setAttributes( { postLayout: 'list' } ),
			isActive: postLayout === 'list',
		},
		{
			icon: grid,
			title: __( 'Grid view', 'custom-post-type-widget-blocks' ),
			onClick: () => setAttributes( { postLayout: 'grid' } ),
			isActive: postLayout === 'grid',
		},
	];

	const dateFormat = __experimentalGetSettings().formats.date;

	if ( postType === 'any' ) {
		return (
			<div { ...blockProps }>
				{ inspectorControls }
				<BlockControls>
					<ToolbarGroup controls={ layoutControls } />
				</BlockControls>
				<Placeholder icon={ pin } label={ __( 'Latest Posts (Custom Post Type)', 'custom-post-type-widget-blocks' ) }>
					{ ! Array.isArray( latestPosts ) ? (
						<Spinner />
					) : (
						__( 'Not displayed if postType is All.', 'custom-post-type-widget-blocks' )
					) }
				</Placeholder>
			</div>
		);
	}

	return (
		<div>
			{ inspectorControls }
			<BlockControls>
				<ToolbarGroup controls={ layoutControls } />
			</BlockControls>
			<ul { ...blockProps }>
				{ displayPosts.map( ( post, i ) => {
					const titleTrimmed = invoke( post, [
						'title',
						'rendered',
						'trim',
					] );
					let excerpt = post.excerpt.rendered;
					const currentAuthor = authorList?.find(
						( author ) => author.id === post.author
					);

					const excerptElement = document.createElement( 'div' );
					excerptElement.innerHTML = excerpt;

					excerpt =
						excerptElement.textContent ||
						excerptElement.innerText ||
						'';

					const {
						featuredImageInfo: {
							url: imageSourceUrl,
							alt: featuredImageAlt,
						} = {},
					} = post;
					const imageClasses = classnames( {
						'wp-block-custom-post-type-widget-blocks-latest-posts__featured-image': true,
						[ `align${ featuredImageAlign }` ]: !! featuredImageAlign,
					} );
					const renderFeaturedImage =
						displayFeaturedImage && imageSourceUrl;
					const featuredImage = renderFeaturedImage && (
						<img
							src={ imageSourceUrl }
							alt={ featuredImageAlt }
							style={ {
								maxWidth: featuredImageSizeWidth,
								maxHeight: featuredImageSizeHeight,
							} }
						/>
					);

					const needsReadMore =
						excerptLength < excerpt.trim().split( ' ' ).length &&
						post.excerpt.raw === '';

					const postExcerpt = needsReadMore ? (
						<>
							{ excerpt
								.trim()
								.split( ' ', excerptLength )
								.join( ' ' ) }
							{ /* translators: excerpt truncation character, default …  */ }
							{ __( ' … ', 'custom-post-type-widget-blocks' ) }
							<a href={ post.link } rel="noopener noreferrer">
								{ __( 'Read more', 'custom-post-type-widget-blocks' ) }
							</a>
						</>
					) : (
						excerpt
					);

					return (
						<li key={ i }>
							{ renderFeaturedImage && (
								<div className={ imageClasses }>
									{ addLinkToFeaturedImage ? (
										<a
											href={ post.link }
											rel="noreferrer noopener"
										>
											{ featuredImage }
										</a>
									) : (
										featuredImage
									) }
								</div>
							) }
							<a href={ post.link } rel="noreferrer noopener">
								{ titleTrimmed ? (
									<RawHTML>{ titleTrimmed }</RawHTML>
								) : (
									__( '(no title)', 'custom-post-type-widget-blocks' )
								) }
							</a>
							{ displayAuthor && currentAuthor && (
								<div className="wp-block-custom-post-type-widget-blocks-latest-posts__post-author">
									{ sprintf(
										/* translators: byline. %s: current author. */
										__( 'by %s', 'custom-post-type-widget-blocks' ),
										currentAuthor.name
									) }
								</div>
							) }
							{ displayPostDate && post.date_gmt && (
								<time
									dateTime={ format( 'c', post.date_gmt ) }
									className="wp-block-custom-post-type-widget-blocks-latest-posts__post-date"
								>
									{ dateI18n( dateFormat, post.date_gmt ) }
								</time>
							) }
							{ displayPostContent &&
								displayPostContentRadio === 'excerpt' && (
									<div className="wp-block-custom-post-type-widget-blocks-latest-posts__post-excerpt">
										{ postExcerpt }
									</div>
								) }
							{ displayPostContent &&
								displayPostContentRadio === 'full_post' && (
									<div className="wp-block-custom-post-type-widget-blocks-latest-posts__post-full-content">
										<RawHTML key="html">
											{ post.content.raw.trim() }
										</RawHTML>
									</div>
								) }
						</li>
					);
				} ) }
			</ul>
		</div>
	);
}
