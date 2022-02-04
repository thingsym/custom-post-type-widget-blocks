'use strict';

/**
 * External dependencies
 */
import {
	map,
	filter,
	times,
	unescape,
} from 'lodash';

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
} from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { pin } from '@wordpress/icons';
import { store as coreStore } from '@wordpress/core-data';

export default function CategoriesEdit( {
	attributes: {
		taxonomy,
		displayAsDropdown,
		showHierarchy,
		showPostCounts,
		showOnlyTopLevel,
	},
	setAttributes
} ) {
	const selectId = useInstanceId( CategoriesEdit, 'wp-block-custom-post-type-widget-blocks-category-select' );

	const { taxonomies, categories, isRequesting } = useSelect( ( select ) => {
		const { getEntityRecords, getTaxonomies, isResolving } = select( coreStore );
		const query = { per_page: -1, hide_empty: true };
		if ( showOnlyTopLevel ) {
			query.parent = 0;
		}
		return {
			taxonomies: getTaxonomies( { per_page: -1 } ),
			categories: getEntityRecords( 'taxonomy', taxonomy, query ),
			isRequesting: isResolving( 'getEntityRecords', [
				'taxonomy',
				taxonomy,
				query,
			] ),
		};
	}, [
		taxonomy,
		showOnlyTopLevel,
	] );

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

	const getCategoryListClassName = ( level ) => {
		return `wp-block-custom-post-type-widget-blocks-categories__list wp-block-custom-post-type-widget-blocks-categories__list-level-${ level }`;
	};

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	const renderCategoryName = ( name ) =>
		! name ?  __( '(Untitled)', 'custom-post-type-widget-blocks' ) : unescape( name ).trim();

	const getTaxonomyOptions = () => {
		const selectOption = {
			label: __('- Select -', 'custom-post-type-widget-blocks'),
			value: '',
			disabled: true,
		};

		const taxonomyOptions = map(
			filter( taxonomies, {
				show_cloud: true,
				hierarchical: true,
			} ),
			( item ) => {
				return {
					value: item.slug,
					label: item.name + ' (' + item.slug + ')',
				};
			}
		);

		return [ selectOption, ...taxonomyOptions ];
	};

	const renderCategoryList = () => {
		const parentId = showHierarchy ? 0 : null;
		const categoriesList = getCategoriesList( parentId );
		return (
			<ul className={ getCategoryListClassName( 0 ) }>
				{ categoriesList.map( ( category ) =>
					renderCategoryListItem( category, 0 )
				) }
			</ul>
		);
	};

	const renderCategoryListItem = ( category, level ) => {
		const childCategories = getCategoriesList( category.id );
		const { id, link, count, name } = category;
		return (
			<li key={ id }>
				<a href={ link } target="_blank" rel="noreferrer noopener">
					{ renderCategoryName( name ) }
				</a>
				{ showPostCounts && (
					<span className="wp-block-custom-post-type-widget-blocks-categories__post-count">
						{ ` (${ count })` }
					</span>
				) }
				{ showHierarchy && !! childCategories.length && (
					<ul className={ getCategoryListClassName( level + 1 ) }>
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
					{ 'Categories', 'custom-post-type-widget-blocks' }
				</VisuallyHidden>
				<select
					id={ selectId }
					className="wp-block-custom-post-type-widget-blocks-categories__dropdown"
				>
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
			<option key={ id }>
				{ times( level * 3, () => '\xa0' ) }
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

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Categories settings', 'custom-post-type-widget-blocks' ) } >
					<SelectControl
						label={ __( 'Taxonomy (slug)', 'custom-post-type-widget-blocks' ) }
						options={ getTaxonomyOptions() }
						value={ taxonomy }
						onChange={ toggleAttribute( 'taxonomy' ) }
					/>
					<ToggleControl
						label={ __( 'Display as Dropdown', 'custom-post-type-widget-blocks' ) }
						checked={ displayAsDropdown }
						onChange={ toggleAttribute( 'displayAsDropdown' ) }
					/>
					<ToggleControl
						label={ __( 'Show Post Counts', 'custom-post-type-widget-blocks' ) }
						checked={ showPostCounts }
						onChange={ toggleAttribute( 'showPostCounts' ) }
					/>
					<ToggleControl
						label={ __( 'Show only top level categories', 'custom-post-type-widget-blocks' ) }
						checked={ showOnlyTopLevel }
						onChange={ toggleAttribute( 'showOnlyTopLevel' ) }
					/>
					{ ! showOnlyTopLevel && (
						<ToggleControl
							label={ __( 'Show hierarchy', 'custom-post-type-widget-blocks' ) }
							checked={ showHierarchy }
							onChange={ toggleAttribute( 'showHierarchy' ) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			{ isRequesting && (
				<Placeholder icon={ pin } label={ __( 'Categories', 'custom-post-type-widget-blocks' ) }>
					<Spinner />
				</Placeholder>
			) }
			{ ! isRequesting && ( categories === null || categories.length === 0 ) && (
				<p>
					{ __(
						'Your site does not have any posts, so there is nothing to display here at the moment.',
						'custom-post-type-widget-blocks'
					) }
				</p>
			) }
			{ ! isRequesting &&
				categories != null &&
				categories.length > 0 &&
				( displayAsDropdown
					? renderCategoryDropdown()
					: renderCategoryList() ) }
		</div>
	);
}
