/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import {
	PanelBody,
	Placeholder,
	QueryControls,
	RadioControl,
	RangeControl,
	Spinner,
	ToggleControl,
	ToolbarGroup,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon,
	BaseControl,
	SelectControl,
	Disabled,
	Button,
	ResponsiveWrapper,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n, format, getSettings } from '@wordpress/date';
import {
	InspectorControls,
	BlockControls,
	__experimentalImageSizeControl as ImageSizeControl,
	useBlockProps,
	store as blockEditorStore,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	pin,
	list,
	grid,
	alignNone,
	positionLeft,
	positionCenter,
	positionRight,
} from '@wordpress/icons';
import { store as coreStore } from '@wordpress/core-data';
import { store as noticeStore } from '@wordpress/notices';
import { useInstanceId } from '@wordpress/compose';
import { createInterpolateElement } from '@wordpress/element';

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

function getFeaturedImageDetails( post, size ) {
	const image = post._embedded?.[ 'wp:featuredmedia' ]?.[ '0' ];

	return {
		url:
			image?.media_details?.sizes?.[ size ]?.source_url ??
			image?.source_url,
		alt: image?.alt_text,
	};
}

export default function LatestPostsEdit( { attributes, setAttributes } ) {
	const instanceId = useInstanceId( LatestPostsEdit );
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
		featuredImageId,
	} = attributes;

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

	const {
		imageSizes,
		latestPosts,
		defaultImageWidth,
		defaultImageHeight,
		categoriesList,
		authorList,
		postTypes,
		featuredImageObj,
	} = useSelect(
		( select ) => {
			const { getEntityRecords, getUsers, getMedia } = select( coreStore );
			const settings = select( blockEditorStore ).getSettings();
			const catIds =
				categories && categories.length > 0
					? categories.map( ( cat ) => cat.id )
					: [];

			const latestPostsParam = {
				author: selectedAuthor,
				order,
				orderby: orderBy,
				per_page: postsToShow,
				_embed: 'wp:featuredmedia',
			};

			let taxonomy;
			taxonomy = undefined;

			if ( postType === 'post' ) {
				latestPostsParam[ categories ] = catIds;
				taxonomy = 'category';
			} else {
				latestPostsParam[ taxonomy ] = categories;
				taxonomy = 'category';
			}

			const latestPostsQuery = Object.fromEntries(
				Object.entries( latestPostsParam )
					.filter( ( [ , value ] ) => typeof value !== 'undefined' )
			);

			return {
				defaultImageWidth:
					settings.imageDimensions?.[ featuredImageSizeSlug ]
						?.width ?? 0,
				defaultImageHeight:
					settings.imageDimensions?.[ featuredImageSizeSlug ]
						?.height ?? 0,
				imageSizes: settings.imageSizes,
				latestPosts: getEntityRecords(
					'postType',
					postType,
					latestPostsQuery,
				),
				categoriesList: getEntityRecords(
					'taxonomy',
					taxonomy,
					CATEGORIES_LIST_QUERY,
				),
				authorList: getUsers( USERS_LIST_QUERY ),
				postTypes: select( coreStore ).getPostTypes( { per_page: -1 } ),
				featuredImageObj: featuredImageId ? getMedia( featuredImageId, { context: 'view' } ) : null,
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
			featuredImageId,
		],
	);

	// If a user clicks to a link prevent redirection and show a warning.
	const { createWarningNotice, removeNotice } = useDispatch( noticeStore );
	let noticeId;
	const showRedirectionPreventedNotice = ( event ) => {
		event.preventDefault();
		// Remove previous warning if any, to show one at a time per block.
		removeNotice( noticeId );
		noticeId = `block-library/core/latest-posts/redirection-prevented/${ instanceId }`;
		createWarningNotice( __( 'Links are disabled in the editor.', 'custom-post-type-widget-blocks' ), {
			id: noticeId,
			type: 'snackbar',
		} );
	};

	const imageSizeOptions = imageSizes
		.filter( ( { slug } ) => slug !== 'full' )
		.map( ( { name, slug } ) => ( {
			value: slug,
			label: name,
		} ) );
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
		if ( allCategories.includes( null ) ) {
			return false;
		}
		setAttributes( { categories: allCategories } );
	};

	const imageAlignmentOptions = [
		{
			value: 'none',
			icon: alignNone,
			label: __( 'None' ),
		},
		{
			value: 'left',
			icon: positionLeft,
			label: __( 'Left' ),
		},
		{
			value: 'center',
			icon: positionCenter,
			label: __( 'Center' ),
		},
		{
			value: 'right',
			icon: positionRight,
			label: __( 'Right' ),
		},
	];

	const IMAGE_BACKGROUND_TYPE = 'image';
	const VIDEO_BACKGROUND_TYPE = 'video';
	const ALLOWED_MEDIA_TYPES = [ 'image' ];

	const onSelectMedia = ( media ) => {
		if ( ! media || ! media.url ) {
			setAttributes( {
				featuredImageUrl: undefined,
				featuredImageId: undefined,
			} );
			return;
		}
		let mediaType;
		// for media selections originated from a file upload.
		if ( media.media_type ) {
			if ( media.media_type === IMAGE_BACKGROUND_TYPE ) {
				mediaType = IMAGE_BACKGROUND_TYPE;
			} else {
				// only images and videos are accepted so if the media_type is not an image we can assume it is a video.
				// Videos contain the media type of 'file' in the object returned from the rest api.
				mediaType = VIDEO_BACKGROUND_TYPE;
			}
		} else { // for media selections originated from existing files in the media library.
			if (
				media.type !== IMAGE_BACKGROUND_TYPE &&
				media.type !== VIDEO_BACKGROUND_TYPE
			) {
				return;
			}
			mediaType = media.type;
		}

		setAttributes( {
			featuredImageId: media.id,
		} );
	};

	const onRemoveImage = () => {
		setAttributes( {
			featuredImageId: undefined,
		} );
	};

	const hasPosts = !! latestPosts?.length;
	const inspectorControls = (
		<InspectorControls>
			<PanelBody title={ __( 'Post content settings', 'custom-post-type-widget-blocks' ) }>
				<ToggleControl
					__nextHasNoMarginBottom
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
						__nextHasNoMarginBottom
						__next40pxDefaultSize
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
					__nextHasNoMarginBottom
					label={ __( 'Display author name', 'custom-post-type-widget-blocks' ) }
					checked={ displayAuthor }
					onChange={ ( value ) =>
						setAttributes( { displayAuthor: value } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Display post date', 'custom-post-type-widget-blocks' ) }
					checked={ displayPostDate }
					onChange={ ( value ) =>
						setAttributes( { displayPostDate: value } )
					}
				/>
			</PanelBody>

			<PanelBody title={ __( 'Featured image settings', 'custom-post-type-widget-blocks' ) }>
				<ToggleControl
					__nextHasNoMarginBottom
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
							imageSizeHelp={ __(
								'Select the size of the source image.', 'custom-post-type-widget-blocks'
							) }
							onChangeImage={ ( value ) =>
								setAttributes( {
									featuredImageSizeSlug: value,
									featuredImageSizeWidth: undefined,
									featuredImageSizeHeight: undefined,
								} )
							}
						/>
						<ToggleGroupControl
							className="editor-latest-posts-image-alignment-control"
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Image alignment', 'custom-post-type-widget-blocks' ) }
							value={ featuredImageAlign || 'none' }
							onChange={ ( value ) =>
								setAttributes( {
									featuredImageAlign:
										value !== 'none' ? value : undefined,
								} )
							}
						>
							{ imageAlignmentOptions.map(
								( { value, icon, label } ) => {
									return (
										<ToggleGroupControlOptionIcon
											key={ value }
											value={ value }
											icon={ icon }
											label={ label }
										/>
									);
								}
							) }
						</ToggleGroupControl>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Add link to featured image', 'custom-post-type-widget-blocks' ) }
							checked={ addLinkToFeaturedImage }
							onChange={ ( value ) =>
								setAttributes( {
									addLinkToFeaturedImage: value,
								} )
							}
						/>

						<BaseControl className="block-editor-post-featured-image-control__row">
							<BaseControl.VisualLabel>
								{ __( 'Featured image', 'custom-post-type-widget-blocks' ) }
							</BaseControl.VisualLabel>

							<MediaUploadCheck>
								<MediaUpload
									title={ __( 'Featured image', 'custom-post-type-widget-blocks' ) }
									onSelect={ onSelectMedia }
									allowedTypes={ ALLOWED_MEDIA_TYPES }
									modalClass="editor-post-featured-image__media-modal"
									value={ featuredImageId }
									render={ ( { open } ) => (
										<div className="editor-post-featured-image__container">
											<Button
												className={
													! featuredImageId
														? 'editor-post-featured-image__toggle'
														: 'editor-post-featured-image__preview'
												}
												onClick={ open }
											>
												{ !! featuredImageId && featuredImageObj && (
													<ResponsiveWrapper
														naturalWidth={ featuredImageObj.media_details.sizes.thumbnail.width }
														naturalHeight={ featuredImageObj.media_details.sizes.thumbnail.height }
														isInline
													>
														<img
															src={ featuredImageObj.media_details.sizes.thumbnail.source_url }
															alt=""
														/>
													</ResponsiveWrapper>
												) }
												{ ! featuredImageId &&
													( __( 'Set featured image', 'custom-post-type-widget-blocks' ) )
												}
											</Button>
										</div>
									) }
								/>
							</MediaUploadCheck>
							{ !! featuredImageId && (
								<MediaUploadCheck>
									<Button
										onClick={ onRemoveImage }
										variant="link"
										isDestructive
									>
										{ __( 'Remove image', 'custom-post-type-widget-blocks' ) }
									</Button>
								</MediaUploadCheck>
							) }
						</BaseControl>
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
						__nextHasNoMarginBottom
						__next40pxDefaultSize
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
									latestPosts.length,
								)
						}
						required
					/>
				) }
			</PanelBody>
		</InspectorControls>
	);

	const blockProps = useBlockProps( {
		className: clsx( {
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
		latestPosts && latestPosts.length > postsToShow
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

	const dateFormat = getSettings().formats.date;

	if ( postType === 'any' ) {
		return (
			<div { ...blockProps }>
				{ inspectorControls }
				<BlockControls>
					<ToolbarGroup controls={ layoutControls } />
				</BlockControls>
				<Placeholder icon={ pin } label={ __( 'Latest Posts (Custom Post Type)', 'custom-post-type-widget-blocks' ) }>
					{ __( 'Not displayed if postType is All.', 'custom-post-type-widget-blocks' ) }
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
					const titleTrimmed = post.title.rendered.trim();
					let excerpt = post.excerpt.rendered;
					const currentAuthor = authorList?.find(
						( author ) => author.id === post.author,
					);

					const excerptElement = document.createElement( 'div' );
					excerptElement.innerHTML = excerpt;

					excerpt =
						excerptElement.textContent ||
						excerptElement.innerText ||
						'';

					const { url: imageSourceUrl, alt: featuredImageAlt } =
						getFeaturedImageDetails( post, featuredImageSizeSlug );
					const imageClasses = clsx( {
						'wp-block-custom-post-type-widget-blocks-latest-posts__featured-image': true,
						[ `align${ featuredImageAlign }` ]:
							!! featuredImageAlign,
					} );
					const renderFeaturedImage =
						displayFeaturedImage && ( imageSourceUrl || featuredImageObj );

					const featuredImage = renderFeaturedImage && (
						<img
							src={
								imageSourceUrl ? imageSourceUrl
									: featuredImageObj?.media_details?.sizes?.[ featuredImageSizeSlug ]?.source_url ? featuredImageObj.media_details.sizes[ featuredImageSizeSlug ].source_url
										: featuredImageObj?.media_details?.sizes?.full?.source_url ? featuredImageObj.media_details.sizes.full.source_url
											: ''
							}
							alt={ featuredImageAlt }
							style={ {
								width: ( featuredImageSizeWidth && featuredImageSizeHeight ) ? '100%' : '',
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
							{ createInterpolateElement(
								sprintf(
									/* translators: 1: Hidden accessibility text: Post title */
									__(
										'… <a>Read more<span>: %1$s</span></a>', 'custom-post-type-widget-blocks'
									),
									titleTrimmed || __( '(no title)', 'custom-post-type-widget-blocks' )
								),
								{
									a: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<a
											className="wp-block-latest-posts__read-more"
											href={ post.link }
											rel="noopener noreferrer"
											onClick={
												showRedirectionPreventedNotice
											}
										/>
									),
									span: (
										<span className="screen-reader-text" />
									),
								}
							) }
						</>
					) : (
						excerpt
					);

					return (
						<li key={ post.id }>
							<Disabled>
								{ renderFeaturedImage && (
									<div className={ imageClasses }>
										{ addLinkToFeaturedImage ? (
											<a
												href={ post.link }
												rel="noreferrer noopener"
												onClick={
													showRedirectionPreventedNotice
												}
											>
												{ featuredImage }
											</a>
										) : (
											featuredImage
										) }
									</div>
								) }
								<a
									className="wp-block-latest-posts__post-title"
									href={ post.link }
									rel="noreferrer noopener"
									dangerouslySetInnerHTML={
										!! titleTrimmed
											? {
												__html: titleTrimmed,
											}
											: undefined
									}
									onClick={ showRedirectionPreventedNotice }
								>
									{ ! titleTrimmed ? __( '(no title)', 'custom-post-type-widget-blocks' ) : null }
								</a>
								{ displayAuthor && currentAuthor && (
									<div className="wp-block-latest-posts__post-author">
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
									<div
										className="wp-block-custom-post-type-widget-blocks-latest-posts__post-full-content"
										dangerouslySetInnerHTML={ {
											__html: post.content.raw.trim(),
										} }
									/>
								) }
							</Disabled>
						</li>
					);
				} ) }
			</ul>
		</div>
	);
}
