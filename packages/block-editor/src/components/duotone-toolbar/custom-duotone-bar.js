/**
 * WordPress dependencies
 */
import { __experimentalCustomGradientBar as CustomGradientBar } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	getControlPointsFromValues,
	getCustomDuotoneIdFromHexColors,
	getGradientFromValues,
	getHexColorsFromControlPoints,
	getValuesFromHexColors,
} from './utils';

function CustomDuotoneBar( { value, onChange } ) {
	const hasGradient = !! value?.values;
	const background = hasGradient
		? getGradientFromValues( value.values )
		: 'white';
	const controlPoints = hasGradient
		? getControlPointsFromValues( value.values )
		: [];

	return (
		<CustomGradientBar
			hasGradient={ hasGradient }
			background={ background }
			value={ controlPoints }
			onChange={ ( newControlPoints ) => {
				if ( newControlPoints.length >= 2 ) {
					const newColors = getHexColorsFromControlPoints(
						newControlPoints
					);
					onChange( {
						values: getValuesFromHexColors( newColors ),
						id: getCustomDuotoneIdFromHexColors( newColors ),
					} );
				} else {
					onChange( undefined );
				}
			} }
		/>
	);
}

export default CustomDuotoneBar;
