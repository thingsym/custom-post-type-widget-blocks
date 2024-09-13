'use strict';

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
	Spinner,
	ToggleControl,
	SelectControl,
	VisuallyHidden,
	Disabled,
} from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import {
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { pin } from '@wordpress/icons';
import { useSelect } from '@wordpress/data';
import {
	useEntityRecords,
	store as coreStore,
} from '@wordpress/core-data';

export default function CategoriesEdit( {
	attributes: {
		taxonomy,
		displayAsDropdown,
		showHierarchy,
		showPostCounts,
		showOnlyTopLevel,
		showEmpty,
	},
	setAttributes,
	className,
} ) {
	const selectId = useInstanceId( CategoriesEdit, 'wp-block-custom-post-type-widget-blocks-category-select' );
	const query = { per_page: -1, hide_empty: ! showEmpty, context: 'view' };
	if ( showOnlyTopLevel ) {
		query.parent = 0;
	}

	const { records: categories, isResolving } = useEntityRecords(
		'taxonomy',
		taxonomy,
		query,
	);

	const taxonomies = useSelect(
		( select ) => select( coreStore ).getTaxonomies( { per_page: -1 } ),
		[]
	);

	const getCategoriesList = ( parentId ) => {
		if ( categories === null ) {
			return [];
		}
		if ( ! categories?.length ) {
			return [];
		}
		if ( parentId === null ) {
			return categories;
		}
		return categories.filter( ( { parent } ) => parent === parentId );
	};

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	const renderCategoryName = ( name ) =>
		! name ? __( '(Untitled)', 'custom-post-type-widget-blocks' ) : decodeEntities( name ).trim();

	const getTaxonomyOptions = () => {
		const selectOption = {
			label: __( '- Select -', 'custom-post-type-widget-blocks' ),
			value: '',
			disabled: true,
		};

		const taxonomyOptions = ( taxonomies ?? [] )
			.filter( ( tax ) => !! tax.show_cloud && !! tax.hierarchical )
			.map( ( item ) => {
				return {
					value: item.slug,
					label: item.name + ' (' + item.slug + ')',
				};
			} );

		return [ selectOption, ...taxonomyOptions ];
	};

	const renderCategoryList = () => {
		const parentId = showHierarchy ? 0 : null;
		const categoriesList = getCategoriesList( parentId );
		return categoriesList.map( ( category ) =>
			renderCategoryListItem( category, 0 )
		);
	};

	const renderCategoryListItem = ( category, level ) => {
		const childCategories = getCategoriesList( category.id );
		const { id, link, count, name } = category;
		return (
			<li key={ id } className={ `cat-item cat-item-${ id }` }>
				<a href={ link } target="_blank" rel="noreferrer noopener">
					{ renderCategoryName( name ) }
				</a>
				{ showPostCounts && (
					<span className="wp-block-custom-post-type-widget-blocks-categories__post-count">
						{ ` (${ count })` }
					</span>
				) }
				{ showHierarchy && !! childCategories.length && (
					<ul className={ `children level-${ level + 1 }` }>
						{ childCategories.map( ( childCategory ) =>
							renderCategoryListItem( childCategory, level + 1 )
						) }
					</ul>
				) }
			</li>
		);
	};

	const renderCategoryDropdown = () => {
		const parentId = showHierarchy ? 0 : null;
		const categoriesList = getCategoriesList( parentId );
		return (
			<>
				<VisuallyHidden as="label" htmlFor={ selectId }>
					{ __( 'Categories', 'custom-post-type-widget-blocks' ) }
				</VisuallyHidden>
				<select
					id={ selectId }
					className="wp-block-custom-post-type-widget-blocks-categories__dropdown"
				>
					<option>{ __( 'Select Category', 'custom-post-type-widget-blocks' ) }</option>
					{ categoriesList.map( ( category ) =>
						renderCategoryDropdownItem( category, 0 )
					) }
				</select>
			</>
		);
	};

	const renderCategoryDropdownItem = ( category, level ) => {
		const { id, count, name } = category;
		const childCategories = getCategoriesList( id );
		return [
			<option key={ id } className={ `level-${ level }` }>
				{ Array.from( { length: level * 3 } ).map( () => '\xa0' ) }
				{ renderCategoryName( name ) }
				{ showPostCounts && ` (${ count })` }
			</option>,
			showHierarchy &&
				!! childCategories.length &&
				childCategories.map( ( childCategory ) =>
					renderCategoryDropdownItem( childCategory, level + 1 )
				),
		];
	};

	const TagName =
		!! categories?.length && ! displayAsDropdown && ! isResolving
			? 'ul'
			: 'div';

	const classes = clsx( className, {
		'wp-block-custom-post-type-widget-blocks-categories': true,
		'wp-block-custom-post-type-widget-blocks-categories-list':
			!! categories?.length && ! displayAsDropdown && ! isResolving,
		'wp-block-custom-post-type-widget-blocks-categories-dropdown':
			!! categories?.length && displayAsDropdown && ! isResolving,
	} );

	const blockProps = useBlockProps( {
		className: classes,
	} );

	return (
		<TagName { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Categories settings', 'custom-post-type-widget-blocks' ) } >
					<SelectControl
						__nextHasNoMarginBottom
						label={ __( 'Taxonomy (slug)', 'custom-post-type-widget-blocks' ) }
						options={ getTaxonomyOptions() }
						value={ taxonomy }
						onChange={ toggleAttribute( 'taxonomy' ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Display as Dropdown', 'custom-post-type-widget-blocks' ) }
						checked={ displayAsDropdown }
						onChange={ toggleAttribute( 'displayAsDropdown' ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show Post Counts', 'custom-post-type-widget-blocks' ) }
						checked={ showPostCounts }
						onChange={ toggleAttribute( 'showPostCounts' ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show only top level categories', 'custom-post-type-widget-blocks' ) }
						checked={ showOnlyTopLevel }
						onChange={ toggleAttribute( 'showOnlyTopLevel' ) }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show empty categories', 'custom-post-type-widget-blocks' ) }
						checked={ showEmpty }
						onChange={ toggleAttribute( 'showEmpty' ) }
					/>
					{ ! showOnlyTopLevel && (
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Show hierarchy', 'custom-post-type-widget-blocks' ) }
							checked={ showHierarchy }
							onChange={ toggleAttribute( 'showHierarchy' ) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			{ isResolving && (
				<Placeholder icon={ pin } label={ __( 'Categories (Custom Post Type)', 'custom-post-type-widget-blocks' ) }>
					<Spinner />
				</Placeholder>
			) }
			{ ! isResolving && ( categories === null || categories.length === 0 ) && (
				<p>
					{ __(
						'Your site does not have any posts, so there is nothing to display here at the moment.',
						'custom-post-type-widget-blocks'
					) }
				</p>
			) }
			<Disabled>
				{ ! isResolving &&
					categories !== null &&
					categories.length > 0 &&
					( displayAsDropdown
						? renderCategoryDropdown()
						: renderCategoryList() ) }
			</Disabled>
		</TagName>
	);
}
