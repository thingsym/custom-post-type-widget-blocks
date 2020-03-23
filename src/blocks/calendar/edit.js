/**
 * External dependencies
 */
import { map, filter, remove } from 'lodash';
import moment from 'moment';
import memoize from 'memize';

/**
 * WordPress dependencies
 */
import { PanelBody, SelectControl, Disabled } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

class CalendarEdit extends Component {
	constructor() {
		super( ...arguments );
		this.setPostType = this.setPostType.bind( this );
		this.getYearMonth = memoize( this.getYearMonth.bind( this ), {
			maxSize: 1,
		} );
		this.getServerSideAttributes = memoize(
			this.getServerSideAttributes.bind( this ),
			{
				maxSize: 1,
			}
		);
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

	getYearMonth( date ) {
		if ( ! date ) {
			return {};
		}
		const momentDate = moment( date );
		return {
			year: momentDate.year(),
			month: momentDate.month() + 1,
		};
	}

	getServerSideAttributes( attributes, date ) {
		return {
			...attributes,
			...this.getYearMonth( date ),
		};
	}

	render() {
		const postTypeOptions = this.getPostTypeOptions();
		const { postType } = this.props.attributes;

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Calendar settings', 'custom-post-type-widget-blocks' ) }>
						<SelectControl
							label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
							options={ postTypeOptions }
							value={ postType }
							onChange={ this.setPostType }
						/>
					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						block="custom-post-type-widget-blocks/calendar"
						attributes={ this.getServerSideAttributes(
							this.props.attributes,
							this.props.date
						) }
					/>
				</Disabled>
			</>
		);
	}
}

export default withSelect( ( select ) => {
	const coreEditorSelect = select( 'custom-post-type-widget-blocks/editor' );
	const { getPostTypes } = select( 'core' );

	const postTypes = filter( getPostTypes(), { 'viewable': true, 'hierarchical': false } );
	remove( postTypes, { 'slug': 'attachment' } );

	if ( ! coreEditorSelect ) {
		return {
			postTypes: postTypes,
		};
	}
	const { getEditedPostAttribute } = coreEditorSelect;
	const postType = getEditedPostAttribute( 'type' );
	// Dates are used to overwrite year and month used on the calendar.
	// This overwrite should only happen for 'post' post types.
	// For other post types the calendar always displays the current month.
	return {
		postTypes: postTypes,
		date:
			postType === 'post' ? getEditedPostAttribute( 'date' ) : undefined,
	};
} )( CalendarEdit );
