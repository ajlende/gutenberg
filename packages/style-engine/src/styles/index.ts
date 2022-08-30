/**
 * Internal dependencies
 */
import filter from './filter';
import border from './border';
import color from './color';
import shadow from './shadow';
import spacing from './spacing';
import typography from './typography';

export const styleDefinitions = [
	...border,
	...color,
	...filter,
	...spacing,
	...typography,
	...shadow,
];
