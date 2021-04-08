/**
 * Internal dependencies
 */
import CustomGradientBar from '../custom-gradient-bar';

import {
	getColorStopsFromValues,
	getGradientFromValues,
	getValuesFromColorStops,
} from './utils';

const PLACEHOLDER_VALUES = {
	r: [ 0.2, 0.8 ],
	g: [ 0.2, 0.8 ],
	b: [ 0.2, 0.8 ],
};

export default function CustomDuotoneBar( { value, onChange } ) {
	const hasGradient = !! value;
	const values = hasGradient ? value : PLACEHOLDER_VALUES;
	const background = getGradientFromValues( values );
	const controlPoints = getColorStopsFromValues( values );
	return (
		<CustomGradientBar
			disableInserter
			disableAlpha
			background={ background }
			hasGradient={ hasGradient }
			value={ controlPoints }
			onChange={ ( newColorStops ) => {
				const newValue = getValuesFromColorStops( newColorStops );
				onChange( newValue );
			} }
		/>
	);
}
