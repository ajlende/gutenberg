/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { plus } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Button from '../button';
import ColorPicker from '../color-picker';
import Dropdown from '../dropdown';
import KeyboardShortcuts from '../keyboard-shortcuts';
import VisuallyHidden from '../visually-hidden';

import {
	addControlPoint,
	clampPercent,
	removeControlPoint,
	updateControlPointColor,
	updateControlPointColorByPosition,
	updateControlPointPosition,
	getHorizontalRelativeGradientPosition,
} from './utils';
import {
	COLOR_POPOVER_PROPS,
	GRADIENT_MARKERS_WIDTH,
	MINIMUM_SIGNIFICANT_MOVE,
	KEYBOARD_CONTROL_POINT_VARIATION,
} from './constants';

function ControlPointKeyboardMove( { value: position, onChange, children } ) {
	const shortcuts = {
		right( event ) {
			// Stop propagation of the key press event to avoid focus moving
			// to another editor area.
			event.stopPropagation();
			const newPosition = clampPercent(
				position + KEYBOARD_CONTROL_POINT_VARIATION
			);
			onChange( newPosition );
		},
		left( event ) {
			// Stop propagation of the key press event to avoid focus moving
			// to another editor area.
			event.stopPropagation();
			const newPosition = clampPercent(
				position - KEYBOARD_CONTROL_POINT_VARIATION
			);
			onChange( newPosition );
		},
	};

	return (
		<KeyboardShortcuts shortcuts={ shortcuts }>
			{ children }
		</KeyboardShortcuts>
	);
}

function ControlPointButton( {
	isOpen,
	position,
	color,
	onChange,
	...additionalProps
} ) {
	const instanceId = useInstanceId( ControlPointButton );
	const descriptionId = `components-custom-gradient-picker__control-point-button-description-${ instanceId }`;
	return (
		<ControlPointKeyboardMove value={ position } onChange={ onChange }>
			<Button
				aria-label={ sprintf(
					// translators: %1$s: gradient position e.g: 70, %2$s: gradient color code e.g: rgb(52,121,151).
					__(
						'Gradient control point at position %1$s%% with color code %2$s.'
					),
					position,
					color
				) }
				aria-describedby={ descriptionId }
				aria-haspopup="true"
				aria-expanded={ isOpen }
				className={ classnames(
					'components-custom-gradient-picker__control-point-button',
					{
						'is-active': isOpen,
					}
				) }
				style={ {
					left: `${ position }%`,
				} }
				{ ...additionalProps }
			/>
			<VisuallyHidden id={ descriptionId }>
				{ __(
					'Use your left or right arrow keys or drag and drop with the mouse to change the gradient position. Press the button to change the color or remove the control point.'
				) }
			</VisuallyHidden>
		</ControlPointKeyboardMove>
	);
}

function ControlPoints( {
	gradientPickerDomRef,
	ignoreMarkerPosition,
	value: controlPoints,
	onChange,
	onStartControlPointChange,
	onStopControlPointChange,
} ) {
	const controlPointMoveState = useRef();

	const onMouseMove = ( event ) => {
		const relativePosition = getHorizontalRelativeGradientPosition(
			event.clientX,
			gradientPickerDomRef.current,
			GRADIENT_MARKERS_WIDTH
		);
		const {
			initialPosition,
			index,
			significantMoveHappened,
		} = controlPointMoveState.current;
		if (
			! significantMoveHappened &&
			Math.abs( initialPosition - relativePosition ) >=
				MINIMUM_SIGNIFICANT_MOVE
		) {
			controlPointMoveState.current.significantMoveHappened = true;
		}

		updateControlPointPosition( controlPoints, index, relativePosition );
	};

	useEffect( () => {
		if ( window && window.addEventListener ) {
			window.addEventListener( 'mousemove', onMouseMove );
			window.addEventListener( 'mouseup', cleanEventListeners );
		}
		return () => {
			if (
				window &&
				window.removeEventListener &&
				controlPointMoveState.current &&
				controlPointMoveState.current.listenersActivated
			) {
				window.removeEventListener( 'mousemove', onMouseMove );
				window.removeEventListener( 'mouseup', cleanEventListeners );
			}
		};
	}, [] );

	return controlPoints.map( ( point, index ) => {
		const initialPosition = point?.position;
		return (
			ignoreMarkerPosition !== initialPosition && (
				<Dropdown
					key={ index }
					onClose={ onStopControlPointChange }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<ControlPointButton
							key={ index }
							onClick={ () => {
								if (
									controlPointMoveState.current
										.significantMoveHappened
								) {
									return;
								}
								if ( isOpen ) {
									onStopControlPointChange();
								} else {
									onStartControlPointChange();
								}
								onToggle();
							} }
							onMouseDown={ () => {
								onStartControlPointChange();
								controlPointMoveState.current = {
									initialPosition,
									index,
									significantMoveHappened: false,
									listenersActivated: true,
								};
							} }
							onMouseUp={ () => {
								onStopControlPointChange();
								controlPointMoveState.current.listenersActivated = false;
							} }
							isOpen={ isOpen }
							position={ point.position }
							color={ point.color }
							onChange={ ( newPosition ) => {
								onChange(
									updateControlPointPosition(
										controlPoints,
										index,
										newPosition
									)
								);
							} }
						/>
					) }
					renderContent={ ( { onClose } ) => (
						<>
							<ColorPicker
								color={ point.color }
								onChangeComplete={ ( { color } ) => {
									onChange(
										updateControlPointColor(
											controlPoints,
											index,
											color.toRgbString()
										)
									);
								} }
							/>
							<Button
								className="components-custom-gradient-picker__remove-control-point"
								onClick={ () => {
									onChange(
										removeControlPoint(
											controlPoints,
											index
										)
									);
									onClose();
								} }
								isLink
							>
								{ __( 'Remove Control Point' ) }
							</Button>
						</>
					) }
					popoverProps={ COLOR_POPOVER_PROPS }
				/>
			)
		);
	} );
}

function InsertPoint( {
	value: controlPoints,
	onChange,
	onOpenInserter,
	onCloseInserter,
	insertPosition,
} ) {
	const [ alreadyInsertedPoint, setAlreadyInsertedPoint ] = useState( false );
	return (
		<Dropdown
			className="components-custom-gradient-picker__inserter"
			onClose={ () => {
				onCloseInserter();
			} }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					aria-expanded={ isOpen }
					aria-haspopup="true"
					onClick={ () => {
						if ( isOpen ) {
							onCloseInserter();
						} else {
							setAlreadyInsertedPoint( false );
							onOpenInserter();
						}
						onToggle();
					} }
					className="components-custom-gradient-picker__insert-point"
					icon={ plus }
					style={ {
						left:
							insertPosition !== null
								? `${ insertPosition }%`
								: undefined,
					} }
				/>
			) }
			renderContent={ () => (
				<ColorPicker
					onChangeComplete={ ( { color } ) => {
						if ( ! alreadyInsertedPoint ) {
							onChange(
								addControlPoint(
									controlPoints,
									insertPosition,
									color.toRgbString()
								)
							);
							setAlreadyInsertedPoint( true );
						} else {
							onChange(
								updateControlPointColorByPosition(
									controlPoints,
									insertPosition,
									color.toRgbString()
								)
							);
						}
					} }
				/>
			) }
			popoverProps={ COLOR_POPOVER_PROPS }
		/>
	);
}
ControlPoints.InsertPoint = InsertPoint;

export default ControlPoints;
