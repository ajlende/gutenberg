<?php
/**
 * Duotone block support flag.
 *
 * @package gutenberg
 */

/**
 * Registers the style and colors block attributes for block types that support it.
 *
 * @param WP_Block_Type $block_type Block Type.
 */
function gutenberg_register_duotone_support( $block_type ) {
	$has_duotone_support = false;
	if ( property_exists( $block_type, 'supports' ) ) {
		$has_duotone_support = gutenberg_experimental_get( $block_type->supports, array( 'duotone' ), false );
	}

	if ( $has_duotone_support ) {
		if ( ! $block_type->attributes ) {
			$block_type->attributes = array();
		}

		if ( ! array_key_exists( 'duotone', $block_type->attributes ) ) {
			$block_type->attributes['duotone'] = array(
				'type' => 'object',
			);
		}
	}
}


/**
 * Add CSS classes and inline styles for colors to the incoming attributes array.
 * This will be applied to the block markup in the front-end.
 *
 * @param  WP_Block_Type $block_type       Block type.
 * @param  array         $block_attributes Block attributes.
 *
 * @return array Colors CSS classes and inline styles.
 */
function gutenberg_apply_duotone_support( $block_type, $block_attributes ) {
	$has_duotone_support = false;
	if ( property_exists( $block_type, 'supports' ) ) {
		$has_duotone_support = gutenberg_experimental_get( $block_type->supports, array( 'duotone' ), false );
	}
	if ( $has_duotone_support ) {
		$has_duotone_attribute = array_key_exists( 'duotone', $block_attributes );

		if ( $has_duotone_attribute ) {
			$attributes['class'] = 'duotone-filter-' . $block_attributes['duotone']['slug'];
		}
	}

	return $attributes;
}

/**
 * Render out the duotone stylesheet and SVG.
 *
 * @param  WP_Block_Type $block_type       Block type.
 * @param  array         $block_attributes Block attributes.
 * @param  string        $block_content    Rendered block content.
 *
 * @return string filtered block content.
 */
function gutenberg_render_duotone_support( $block_type, $block_attributes, $block_content ) {
	$duotone_support = false;
	if ( property_exists( $block_type, 'supports' ) ) {
		$duotone_support = gutenberg_experimental_get( $block_type->supports, array( 'duotone' ), false );
	}

	$has_duotone_attribute = array_key_exists( 'duotone', $block_attributes );

	if (
		! $duotone_support ||
		! $has_duotone_attribute
	) {
		return $block_content;
	}

	$duotone_slug   = $block_attributes['duotone']['slug'];
	$duotone_colors = $block_attributes['duotone']['values'];

	$duotone_id = 'duotone-filter-' . $duotone_slug;

	// Object | boolean | string | string[] -> boolean | string | string[].
	$edit_selector =
		! array_key_exists( 'edit', $duotone_support )
			? $duotone_support
			: $duotone_support['edit'];

	// boolean | string | string[] -> boolean[] | string[].
	$edit_selectors = is_array( $edit_selector )
		? $edit_selector
		: array( $edit_selector );

	// boolean[] | string[] -> string[].
	$duotone_class     = '.' . $duotone_id;
	$duotone_selectors = array_map(
		function ( $selector ) use ( $duotone_class ) {
			return is_string( $selector )
				? $duotone_class . ' ' . $selector
				: $duotone_class;
		},
		$edit_selectors
	);

	// string[] -> string.
	$duotone_selector = implode( ', ', $duotone_selectors );

	ob_start();

	?>

	<style>
		<?php echo $duotone_selector; ?> {
			filter: url( <?php echo '#' . $duotone_id; ?> );
		}
	</style>

	<svg
		xmlns:xlink="http://www.w3.org/1999/xlink"
		viewBox="0 0 0 0"
		width="0"
		height="0"
		focusable="false"
		role="none"
		style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;"
	>
		<defs>
			<filter id="<?php echo $duotone_id; ?>">
				<feColorMatrix
					type="matrix"
					<?php // phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent ?>
					values=".299 .587 .114 0 0
					        .299 .587 .114 0 0
					        .299 .587 .114 0 0
					        0 0 0 1 0"
					<?php // phpcs:enable Generic.WhiteSpace.DisallowSpaceIndent ?>
				/>
				<feComponentTransfer color-interpolation-filters="sRGB">
					<feFuncR type="table" tableValues="<?php echo join( ' ', $duotone_colors['r'] ); ?>" />
					<feFuncG type="table" tableValues="<?php echo join( ' ', $duotone_colors['g'] ); ?>" />
					<feFuncB type="table" tableValues="<?php echo join( ' ', $duotone_colors['b'] ); ?>" />
				</feComponentTransfer>
			</filter>
		</defs>
	</svg>

	<?php

	$duotone = ob_get_clean();

	return $block_content . $duotone;
}

// Register the block support.
WP_Block_Supports::get_instance()->register(
	'duotone',
	array(
		'register_attribute' => 'gutenberg_register_duotone_support',
		'apply'              => 'gutenberg_apply_duotone_support',
		'render_block'       => 'gutenberg_render_duotone_support',
	)
);
