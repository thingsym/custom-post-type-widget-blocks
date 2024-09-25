'use strict';

/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import {
	useBlockProps,
	BlockControls,
	InspectorControls,
	RichText,
	__experimentalUseBorderProps as useBorderProps,
	__experimentalUseColorProps as useColorProps,
	getTypographyClassesAndStyles as useTypographyProps,
	store as blockEditorStore,
	__experimentalGetElementClassName,
	useSettings,
} from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useRef } from '@wordpress/element';
import {
	ToolbarDropdownMenu,
	ToolbarGroup,
	Button,
	ButtonGroup,
	ToolbarButton,
	ResizableBox,
	PanelBody,
	__experimentalVStack as VStack,
	__experimentalUseCustomUnits as useCustomUnits,
	__experimentalUnitControl as UnitControl,
	SelectControl,
} from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { Icon, search } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { __unstableStripHTML as stripHTML } from '@wordpress/dom';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import {
	buttonOnly,
	buttonOutside,
	buttonInside,
	noButton,
	buttonWithIcon,
	toggleLabel,
} from './icons';
import {
	PC_WIDTH_DEFAULT,
	PX_WIDTH_DEFAULT,
	MIN_WIDTH,
	MIN_WIDTH_UNIT,
	isPercentageUnit,
} from './utils.js';

// Used to calculate border radius adjustment to avoid "fat" corners when
// button is placed inside wrapper.
const DEFAULT_INNER_PADDING = '4px';

export default function SearchEdit( {
	className,
	attributes,
	setAttributes,
	toggleSelection,
	isSelected,
	clientId,
} ) {
	const {
		postType,
		label,
		showLabel,
		placeholder,
		width,
		widthUnit,
		align,
		buttonText,
		buttonPosition,
		buttonUseIcon,
		isSearchFieldHidden,
		style,
	} = attributes;

	const { postTypes } = useSelect( ( select ) => {
		return {
			postTypes: select( coreStore ).getPostTypes( {
				per_page: -1,
			} ),
		};
	}, [] );

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

	const wasJustInsertedIntoNavigationBlock = useSelect(
		( select ) => {
			const { getBlockParentsByBlockName, wasBlockJustInserted } =
				select( blockEditorStore );
			return (
				!! getBlockParentsByBlockName( clientId, 'core/navigation' )
					?.length && wasBlockJustInserted( clientId )
			);
		},
		[ clientId ]
	);
	const { __unstableMarkNextChangeAsNotPersistent } =
		useDispatch( blockEditorStore );

	useEffect( () => {
		if ( wasJustInsertedIntoNavigationBlock ) {
			// This side-effect should not create an undo level.
			__unstableMarkNextChangeAsNotPersistent();
			setAttributes( {
				showLabel: false,
				buttonUseIcon: true,
				buttonPosition: 'button-inside',
			} );
		}
	}, [
		__unstableMarkNextChangeAsNotPersistent,
		wasJustInsertedIntoNavigationBlock,
		setAttributes,
	] );

	const borderRadius = style?.border?.radius;
	let borderProps = useBorderProps( attributes );

	// Check for old deprecated numerical border radius. Done as a separate
	// check so that a borderRadius style won't overwrite the longhand
	// per-corner styles.
	if ( typeof borderRadius === 'number' ) {
		borderProps = {
			...borderProps,
			style: {
				...borderProps.style,
				borderRadius: `${ borderRadius }px`,
			},
		};
	}

	const colorProps = useColorProps( attributes );
	const [ fluidTypographySettings, layout ] = useSettings(
		'typography.fluid',
		'layout'
	);
	const typographyProps = useTypographyProps( attributes, {
		typography: {
			fluid: fluidTypographySettings,
		},
		layout: {
			wideSize: layout?.wideSize,
		},
	} );
	const unitControlInstanceId = useInstanceId( UnitControl );
	const unitControlInputId = `wp-block-custom-post-type-widget-blocks-search__width-${ unitControlInstanceId }`;
	const isButtonPositionInside = 'button-inside' === buttonPosition;
	const isButtonPositionOutside = 'button-outside' === buttonPosition;
	const hasNoButton = 'no-button' === buttonPosition;
	const hasOnlyButton = 'button-only' === buttonPosition;
	const searchFieldRef = useRef();
	const buttonRef = useRef();

	const units = useCustomUnits( {
		availableUnits: [ '%', 'px' ],
		defaultValues: { '%': PC_WIDTH_DEFAULT, px: PX_WIDTH_DEFAULT },
	} );

	useEffect( () => {
		if ( hasOnlyButton && ! isSelected ) {
			setAttributes( {
				isSearchFieldHidden: true,
			} );
		}
	}, [ hasOnlyButton, isSelected, setAttributes ] );

	// Show the search field when width changes.
	useEffect( () => {
		if ( ! hasOnlyButton || ! isSelected ) {
			return;
		}

		setAttributes( {
			isSearchFieldHidden: false,
		} );
	}, [ hasOnlyButton, isSelected, setAttributes, width ] );

	const getBlockClassNames = () => {
		return clsx(
			className,
			isButtonPositionInside
				? 'wp-block-custom-post-type-widget-blocks-search__button-inside'
				: undefined,
			isButtonPositionOutside
				? 'wp-block-custom-post-type-widget-blocks-search__button-outside'
				: undefined,
			hasNoButton ? 'wp-block-custom-post-type-widget-blocks-search__no-button' : undefined,
			hasOnlyButton ? 'wp-block-custom-post-type-widget-blocks-search__button-only' : undefined,
			! buttonUseIcon && ! hasNoButton
				? 'wp-block-custom-post-type-widget-blocks-search__text-button'
				: undefined,
			buttonUseIcon && ! hasNoButton
				? 'wp-block-custom-post-type-widget-blocks-search__icon-button'
				: undefined,
			hasOnlyButton && isSearchFieldHidden
				? 'wp-block-search__searchfield-hidden'
				: undefined,

			isButtonPositionInside
				? 'wp-block-search__button-inside'
				: undefined,
			isButtonPositionOutside
				? 'wp-block-search__button-outside'
				: undefined,
			hasNoButton ? 'wp-block-blocks-search__no-button' : undefined,
			hasOnlyButton ? 'wp-block-search__button-only' : undefined,
			! buttonUseIcon && ! hasNoButton
				? 'wp-block-search__text-button'
				: undefined,
			buttonUseIcon && ! hasNoButton
				? 'wp-block-search__icon-button'
				: undefined,
			'wp-block-search'
		);
	};

	const buttonPositionControls = [
		{
			role: 'menuitemradio',
			title: __( 'Button outside', 'custom-post-type-widget-blocks' ),
			isActive: buttonPosition === 'button-outside',
			icon: buttonOutside,
			onClick: () => {
				setAttributes( {
					buttonPosition: 'button-outside',
					isSearchFieldHidden: false,
				} );
			},
		},
		{
			role: 'menuitemradio',
			title: __( 'Button inside', 'custom-post-type-widget-blocks' ),
			isActive: buttonPosition === 'button-inside',
			icon: buttonInside,
			onClick: () => {
				setAttributes( {
					buttonPosition: 'button-inside',
					isSearchFieldHidden: false,
				} );
			},
		},
		{
			role: 'menuitemradio',
			title: __( 'No button', 'custom-post-type-widget-blocks' ),
			isActive: buttonPosition === 'no-button',
			icon: noButton,
			onClick: () => {
				setAttributes( {
					buttonPosition: 'no-button',
					isSearchFieldHidden: false,
				} );
			},
		},		{
			role: 'menuitemradio',
			title: __( 'Button only', 'custom-post-type-widget-blocks' ),
			isActive: buttonPosition === 'button-only',
			icon: buttonOnly,
			onClick: () => {
				setAttributes( {
					buttonPosition: 'button-only',
					isSearchFieldHidden: true,
				} );
			},
		},
	];

	const getButtonPositionIcon = () => {
		switch ( buttonPosition ) {
			case 'button-inside':
				return buttonInside;
			case 'button-outside':
				return buttonOutside;
			case 'no-button':
				return noButton;
			case 'button-only':
				return buttonOnly;
		}
	};

	const getResizableSides = () => {
		if ( hasOnlyButton ) {
			return {};
		}

		return {
			right: align !== 'right',
			left: align === 'right',
		};
	};

	const renderTextField = () => {
		// If the input is inside the wrapper, the wrapper gets the border color styles/classes, not the input control.
		const textFieldClasses = clsx(
			'wp-block-search__input wp-block-custom-post-type-widget-blocks-search__input',
			isButtonPositionInside ? undefined : borderProps.className,
			typographyProps.className
		);
		const textFieldStyles = {
			...( isButtonPositionInside
				? { borderRadius }
				: borderProps.style ),
			...typographyProps.style,
			textDecoration: undefined,
		};

		return (
			<input
				type="search"
				className={ textFieldClasses }
				style={ textFieldStyles }
				aria-label={ __( 'Optional placeholder text', 'custom-post-type-widget-blocks' ) }
				// We hide the placeholder field's placeholder when there is a value. This
				// stops screen readers from reading the placeholder field's placeholder
				// which is confusing.
				placeholder={
					placeholder ? undefined : __( 'Optional placeholder…', 'custom-post-type-widget-blocks' )
				}
				value={ placeholder }
				onChange={ ( event ) =>
					setAttributes( { placeholder: event.target.value } )
				}
				ref={ searchFieldRef }
			/>
		);
	};

	const renderButton = () => {
		// If the button is inside the wrapper, the wrapper gets the border color styles/classes, not the button.
		const buttonClasses = clsx(
			'wp-block-search__button wp-block-custom-post-type-widget-blocks-search__button',
			colorProps.className,
			typographyProps.className,
			isButtonPositionInside ? undefined : borderProps.className,
			buttonUseIcon ? 'has-icon' : undefined,
			__experimentalGetElementClassName( 'button' )
		);
		const buttonStyles = {
			...colorProps.style,
			...typographyProps.style,
			...( isButtonPositionInside
				? { borderRadius }
				: borderProps.style ),
		};
		const handleButtonClick = () => {
			if ( hasOnlyButton ) {
				setAttributes( {
					isSearchFieldHidden: ! isSearchFieldHidden,
				} );
			}
		};

		return (
			<>
				{ buttonUseIcon && (
					<button
						type="button"
						className={ buttonClasses }
						style={ buttonStyles }
						aria-label={
							buttonText
								? stripHTML( buttonText )
								: __( 'Search' )
						}
						onClick={ handleButtonClick }
						ref={ buttonRef }
					>
						<Icon icon={ search } />
					</button>
				) }

				{ ! buttonUseIcon && (
					<RichText
						identifier="buttonText"
						className={ buttonClasses }
						style={ buttonStyles }
						aria-label={ __( 'Button text', 'custom-post-type-widget-blocks' ) }
						placeholder={ __( 'Add button text…', 'custom-post-type-widget-blocks' ) }
						withoutInteractiveFormatting
						value={ buttonText }
						onChange={ ( html ) =>
							setAttributes( { buttonText: html } )
						}
						onClick={ handleButtonClick }
					/>
				) }
			</>
		);
	};

	const controls = (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						title={ __( 'Toggle search label', 'custom-post-type-widget-blocks' ) }
						icon={ toggleLabel }
						onClick={ () => {
							setAttributes( {
								showLabel: ! showLabel,
							} );
						} }
						className={ showLabel ? 'is-pressed' : undefined }
					/>
					<ToolbarDropdownMenu
						icon={ getButtonPositionIcon() }
						label={ __( 'Change button position', 'custom-post-type-widget-blocks' ) }
						controls={ buttonPositionControls }
					/>
					{ ! hasNoButton && (
						<ToolbarButton
							title={ __( 'Use button with icon', 'custom-post-type-widget-blocks' ) }
							icon={ buttonWithIcon }
							onClick={ () => {
								setAttributes( {
									buttonUseIcon: ! buttonUseIcon,
								} );
							} }
							className={
								buttonUseIcon ? 'is-pressed' : undefined
							}
						/>
					) }
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Search settings', 'custom-post-type-widget-blocks' ) } >
					<VStack
						className="wp-block-search__inspector-controls"
						spacing={ 4 }
					>
						<SelectControl
							__nextHasNoMarginBottom
							label={ __( 'Post Type', 'custom-post-type-widget-blocks' ) }
							options={ getPostTypeOptions() }
							value={ postType }
							onChange={ ( selectedPostType ) =>
								setAttributes( { postType: selectedPostType } )
							}
						/>
						<UnitControl
							__next40pxDefaultSize
							label={ __( 'Width', 'custom-post-type-widget-blocks' ) }
							id={ unitControlInputId } // unused, kept for backwards compatibility
							min={
								isPercentageUnit( widthUnit ) ? 0 : MIN_WIDTH
							}
							max={
								isPercentageUnit( widthUnit ) ? 100 : undefined
							}
							step={ 1 }
							onChange={ ( newWidth ) => {
								const filteredWidth =
									widthUnit === '%' &&
									parseInt( newWidth, 10 ) > 100
										? 100
										: newWidth;

								setAttributes( {
									width: parseInt( filteredWidth, 10 ),
								} );
							} }
							onUnitChange={ ( newUnit ) => {
								setAttributes( {
									width:
										'%' === newUnit
											? PC_WIDTH_DEFAULT
											: PX_WIDTH_DEFAULT,
									widthUnit: newUnit,
								} );
							} }
							__unstableInputWidth="80px"
							value={ `${ width }${ widthUnit }` }
							units={ units }
						/>

						<ButtonGroup
							className="wp-block-custom-post-type-widget-blocks-search__components-button-group"
							aria-label={ __( 'Percentage Width', 'custom-post-type-widget-blocks' ) }
						>
							{ [ 25, 50, 75, 100 ].map( ( widthValue ) => {
								return (
									<Button
										key={ widthValue }
										size="small"
										variant={
											widthValue === width &&
											widthUnit === '%'
												? 'primary'
												: undefined
										}
										onClick={ () =>
											setAttributes( {
												width: widthValue,
												widthUnit: '%',
											} )
										}
									>
										{ widthValue }%
									</Button>
								);
							} ) }
						</ButtonGroup>
					</VStack>
				</PanelBody>
			</InspectorControls>
		</>
	);

	const padBorderRadius = ( radius ) =>
		radius ? `calc(${ radius } + ${ DEFAULT_INNER_PADDING })` : undefined;

	const getWrapperStyles = () => {
		const styles = isButtonPositionInside
			? borderProps.style
			: {
				borderRadius: borderProps.style?.borderRadius,
				borderTopLeftRadius: borderProps.style?.borderTopLeftRadius,
				borderTopRightRadius:
					borderProps.style?.borderTopRightRadius,
				borderBottomLeftRadius:
					borderProps.style?.borderBottomLeftRadius,
				borderBottomRightRadius:
					borderProps.style?.borderBottomRightRadius,
			};

		const isNonZeroBorderRadius =
			borderRadius !== undefined && parseInt( borderRadius, 10 ) !== 0;

		if ( isButtonPositionInside && isNonZeroBorderRadius ) {
			// We have button inside wrapper and a border radius value to apply.
			// Add default padding so we don't get "fat" corners.
			//
			// CSS calc() is used here to support non-pixel units. The inline
			// style using calc() will only apply if both values have units.

			if ( typeof borderRadius === 'object' ) {
				// Individual corner border radii present.
				const {
					topLeft,
					topRight,
					bottomLeft,
					bottomRight,
				} = borderRadius;

				return {
					...styles,
					borderTopLeftRadius: padBorderRadius( topLeft ),
					borderTopRightRadius: padBorderRadius( topRight ),
					borderBottomLeftRadius: padBorderRadius( bottomLeft ),
					borderBottomRightRadius: padBorderRadius( bottomRight ),
				};
			}

			// The inline style using calc() will only apply if both values
			// supplied to calc() have units. Deprecated block's may have
			// unitless integer.
			const radius = Number.isInteger( borderRadius )
				? `${ borderRadius }px`
				: borderRadius;

			styles.borderRadius = `calc(${ radius } + ${ DEFAULT_INNER_PADDING })`;
		}

		return styles;
	};

	const blockProps = useBlockProps( {
		className: getBlockClassNames(),
		style: {
			...typographyProps.style,
			// Input opts out of text decoration.
			textDecoration: undefined,
		},
	} );

	const labelClassnames = clsx(
		'wp-block-custom-post-type-widget-blocks-search__label',
		typographyProps.className
	);

	return (
		<div { ...blockProps }>
			{ controls }

			{ showLabel && (
				<RichText
					identifier="label"
					className={ labelClassnames }
					aria-label={ __( 'Label text', 'custom-post-type-widget-blocks' ) }
					placeholder={ __( 'Add label…', 'custom-post-type-widget-blocks' ) }
					withoutInteractiveFormatting
					value={ label }
					onChange={ ( html ) => setAttributes( { label: html } ) }
					style={ typographyProps.style }
				/>
			) }

			<ResizableBox
				size={ {
					width: `${ width }${ widthUnit }`,
				} }
				className={ clsx(
					'wp-block-search__inside-wrapper wp-block-custom-post-type-widget-blocks-search__inside-wrapper',
					isButtonPositionInside ? borderProps.className : undefined
				) }
				style={ getWrapperStyles() }
				minWidth={ MIN_WIDTH }
				enable={ getResizableSides() }
				onResizeStart={ ( event, direction, elt ) => {
					setAttributes( {
						width: parseInt( elt.offsetWidth, 10 ),
						widthUnit: 'px',
					} );
					toggleSelection( false );
				} }
				onResizeStop={ ( event, direction, elt, delta ) => {
					setAttributes( {
						width: parseInt( width + delta.width, 10 ),
					} );
					toggleSelection( true );
				} }
				showHandle={ isSelected }
			>
				{ ( isButtonPositionInside ||
					isButtonPositionOutside ||
					hasOnlyButton ) && (
					<>
						{ renderTextField() }
						{ renderButton() }
					</>
				) }

				{ hasNoButton && renderTextField() }
			</ResizableBox>
		</div>
	);
}
