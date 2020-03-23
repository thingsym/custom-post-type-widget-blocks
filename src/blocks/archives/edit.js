/**
 * External dependencies
 */
import { map, filter, remove } from 'lodash';

/**
 * WordPress dependencies
 */
import { PanelBody, ToggleControl, SelectControl, Disabled } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { Component } from '@wordpress/element';

class ArchivesEdit extends Component {
	constructor() {
		super( ...arguments );

		this.setPostType = this.setPostType.bind( this );
	}

	getPostTypeOptions() {
		const postTypes = this.props.postTypes;

		const selectOption = {
			label: __( '- Select -', 'custom-post-type-widget-blocks' ),
			value: '',
			disabled: true,
		};

		const postTypeOptions = map( postTypes, ( postType ) => {
			return {
				value: postType.slug,
				label: postType.name,
			};
		} );

		return [ selectOption, ...postTypeOptions ];
	}

	setPostType( postType ) {
		const { setAttributes } = this.props;

		setAttributes( { postType } );
	}

	render() {
		const { setAttributes } = this.props;
		const { postType, showPostCounts, displayAsDropdown } = this.props.attributes;
		const postTypeOptions = this.getPostTypeOptions();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Archives settings', 'custom-post-type-widget-blocks' ) }>
						<SelectControl
							label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
							options={ postTypeOptions }
							value={ postType }
							onChange={ this.setPostType }
						/>
						<ToggleControl
							label={ __( 'Display as Dropdown', 'custom-post-type-widget-blocks' ) }
							checked={ displayAsDropdown }
							onChange={ () =>
								setAttributes( {
									displayAsDropdown: ! displayAsDropdown,
								} )
							}
						/>
						<ToggleControl
							label={ __( 'Show Post Counts', 'custom-post-type-widget-blocks' ) }
							checked={ showPostCounts }
							onChange={ () =>
								setAttributes( {
									showPostCounts: ! showPostCounts,
								} )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						block="custom-post-type-widget-blocks/archives"
						attributes={ this.props.attributes }
					/>
				</Disabled>
			</>
		);
	}
}

export default withSelect( ( select ) => {
	const { getPostTypes } = select( 'core' );

	const postTypes = filter( getPostTypes(), { 'viewable': true, 'hierarchical': false } );
	remove( postTypes, { 'slug': 'attachment' } );

	return {
		postTypes: postTypes,
	};
} )( ArchivesEdit );
