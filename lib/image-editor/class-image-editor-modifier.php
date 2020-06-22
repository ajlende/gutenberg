<?php
/**
 * Start: Include for phase 2
 * Image Editor: Image_Editor class
 *
 * @package gutenberg
 * @since 7.x ?
 */

/**
 * Abstract class for image modifiers. Any modifier to an image should implement this.
 *
 * @abstract
 */
abstract class Image_Editor_Modifier {

	/**
	 * Update the image metadata with the modifier.
	 *
	 * @abstract
	 * @access public
	 *
	 * @param array $meta Metadata to update.
	 * @return array Updated metadata.
	 */
	abstract public function apply_to_meta( $meta );

	/**
	 * Apply the modifier to the image
	 *
	 * @abstract
	 * @access public
	 *
	 * @param WP_Image_Editor $image Image editor.
	 * @return bool|WP_Error True on success, WP_Error object or false on failure.
	 */
	abstract public function apply_to_image( $image );

	/**
	 * Gets the new filename based on metadata.
	 *
	 * @abstract
	 * @access public
	 *
	 * @param array $meta Image metadata.
	 * @return string Filename for the edited image.
	 */
	abstract public static function get_filename( $meta );

	/**
	 * Gets the default metadata for an image modifier.
	 *
	 * @abstract
	 * @access public
	 *
	 * @return array Default metadata.
	 */
	abstract public static function get_default_meta();
}
