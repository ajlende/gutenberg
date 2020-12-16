/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Button from '../button';
import ColorPicker from '../color-picker';
import Dropdown from '../dropdown';
import VisuallyHidden from '../visually-hidden';
import { getHorizontalRelativeGradientPosition } from './utils';
import {
	COLOR_POPOVER_PROPS,
	GRADIENT_MARKERS_WIDTH,
	MINIMUM_SIGNIFICANT_MOVE,
} from './constants';
import KeyboardShortcuts from '../keyboard-shortcuts';

function ControlPointKeyboardMove( { gradientIndex, onChange, children } ) {
	const shortcuts = {
		right( event ) {
			// Stop propagation of the key press event to avoid focus moving
			// to another editor area.
			event.stopPropagation();
			onChange( {
				type: 'INCREASE_POSITION_BY_INDEX',
				gradientIndex,
			} );
		},
		left( event ) {
			// Stop propagation of the key press event to avoid focus moving
			// to another editor area.
			event.stopPropagation();
			onChange( {
				type: 'DECREASE_POSITION_BY_INDEX',
				gradientIndex,
			} );
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
	gradientIndex,
	...additionalProps
} ) {
	const instanceId = useInstanceId( ControlPointButton );
	const descriptionId = `components-custom-gradient-picker__control-point-button-description-${ instanceId }`;
	return (
		<ControlPointKeyboardMove
			onChange={ onChange }
			gradientIndex={ gradientIndex }
		>
			<Button
				aria-label={ sprintf(
					// translators: %1$s: gradient position e.g: 70%, %2$s: gradient color code e.g: rgb(52,121,151).
					__(
						'Gradient control point at position %1$s with color code %2$s.'
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
					left: position,
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

export default function ControlPoints( {
	gradientPickerDomRef,
	ignoreMarkerPosition,
	markerPoints,
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
			position,
			significantMoveHappened,
		} = controlPointMoveState.current;
		if (
			! significantMoveHappened &&
			Math.abs( initialPosition - relativePosition ) >=
				MINIMUM_SIGNIFICANT_MOVE
		) {
			controlPointMoveState.current.significantMoveHappened = true;
		}

		onChange( {
			type: 'UPDATE_POSITION_BY_MOUSE',
			relativePosition,
			position,
		} );
	};

	const cleanEventListeners = () => {
		if (
			window &&
			window.removeEventListener &&
			controlPointMoveState.current &&
			controlPointMoveState.current.listenersActivated
		) {
			window.removeEventListener( 'mousemove', onMouseMove );
			window.removeEventListener( 'mouseup', cleanEventListeners );
			onStopControlPointChange();
			controlPointMoveState.current.listenersActivated = false;
		}
	};

	useEffect( () => {
		return () => {
			cleanEventListeners();
		};
	}, [] );

	return markerPoints.map(
		( point, index ) =>
			point &&
			ignoreMarkerPosition !== point.positionValue && (
				<Dropdown
					key={ index }
					onClose={ onStopControlPointChange }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<ControlPointButton
							key={ index }
							onClick={ () => {
								if (
									controlPointMoveState.current &&
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
								if ( window && window.addEventListener ) {
									controlPointMoveState.current = {
										initialPosition: point.positionValue,
										position: index,
										significantMoveHappened: false,
										listenersActivated: true,
									};
									onStartControlPointChange();
									window.addEventListener(
										'mousemove',
										onMouseMove
									);
									window.addEventListener(
										'mouseup',
										cleanEventListeners
									);
								}
							} }
							isOpen={ isOpen }
							position={ point.position }
							color={ point.color }
							onChange={ onChange }
							gradientIndex={ index }
						/>
					) }
					renderContent={ ( { onClose } ) => (
						<>
							<ColorPicker
								color={ point.color }
								onChangeComplete={ ( { rgb } ) => {
									onChange( {
										type: 'UPDATE_COLOR_BY_INDEX',
										index,
										rgb,
									} );
								} }
							/>
							<Button
								className="components-custom-gradient-picker__remove-control-point"
								onClick={ () => {
									onChange( {
										type: 'REMOVE_BY_INDEX',
										index,
									} );
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
}
