/**
 * Internal dependencies
 */
import type { StyleDefinition } from '../../types';
import { generateRule } from '../utils';

const duotone: StyleDefinition = {
	name: 'duotone',
	generate: ( style, options ) => {
		return generateRule(
			style,
			options,
			[ 'color', 'duotone' ],
			'--wp--style--filter'
		);
	},
};

export default [ duotone ];
