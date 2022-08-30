<?php
/**
 * Block support flags.
 *
 * @package WordPress
 *
 * @since 5.6.0
 */

class WP_Block_Supports_6_1 extends WP_Block_Supports {}

/**
 * Applies block wrapper attributes to block markup in the render_block stage.
 * 
 * @since 6.1.0
 * 
 * @param  string $block_content Rendered block content.
 * @param  array  $block         Block object.
 * @return string                Filtered block content.
 */
function gutenberg_render_block_wrapper_attributes( $block_content, $block, $instance ) {
	/*
	 * TODO: Using WP_Block_Supports like this seems wrong, but
	 *       it looks like that's how it's done in class-wp-block.php.
	 */
	$tmp_block = WP_Block_Supports::$block_to_render;
	WP_Block_Supports::$block_to_render = $block;
	$new_attributes = WP_Block_Supports::get_instance()->apply_block_supports();
	WP_Block_Supports::$block_to_render = $tmp_block;

	if ( empty( $new_attributes ) ) {
		return $block_content;
	}

	/*
	 * Dynamic blocks are handled manually using get_block_wrapper_attributes
	 * in their render_callback which is processed in the WP_Block render call.
	 */
	$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block['blockName'] );
	if ( $block_type && $block_type->is_dynamic() ) {
		return $block_content;
	}

	// This is hardcoded on purpose.
	// We only support a fixed list of attributes.
	$attributes_to_render = array( 'style', 'class' );
	foreach ( $attributes_to_render as $attribute_name ) {
		if ( empty( $new_attributes[ $attribute_name ] ) ) {
			continue;
		}

		// TODO: Use HTML Walker instead of regular expressions.
		$attribute_pattern = '/'. preg_quote( $attribute_name, '/' ) . '="([^"]*)"/';
		$wrapper_pattern   = '/<[^>]+?' . substr( $attribute_pattern, 1, -1 ) . '[^>]*>/';
		preg_match(
			$wrapper_pattern,
			$block_content,
			$matches
		);

		if ( isset( $matches[1] ) ) {
			// Replace the attribute.
			$value         = $new_attributes[ $attribute_name ] . ' ' . $matches[1];
			$block_content = preg_replace(
				$attribute_pattern,
				sprintf( '%s="%s"', $attribute_name, esc_attr( $value ) ),
				$block_content,
				1
			);
		} else {
			// No matching attribute was found or there was an error, so add a new attribute.
			$value         = $new_attributes[ $attribute_name ];
			$block_content = preg_replace(
				'/(\s*\/?>)/',
				sprintf( ' %s="%s"${0}', $attribute_name, esc_attr( $value ) ),
				$block_content,
				1
			);
		}
	}

	return $block_content;
}

add_filter( 'render_block', 'gutenberg_render_block_wrapper_attributes', 10, 3 );
