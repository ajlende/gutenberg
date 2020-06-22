<?php
/**
 * Start: Include for phase 2
 * Image Editor: Image_Editor_Crop class
 *
 * @package gutenberg
 * @since 7.x ?
 */

/**
 * Image editor modifier abstract class.
 */
require_once __DIR__ . '/class-image-editor-modifier.php';

/**
 * Crop image modifier.
 */
class Image_Editor_Crop extends Image_Editor_Modifier {
	/**
	 * Distance from the left for the crop.
	 *
	 * @var float
	 */
	private $left = 0;

	/**
	 * Distance from the top for the crop.
	 *
	 * @var float
	 */
	private $top = 0;

	/**
	 * Width of the crop.
	 *
	 * @var float
	 */
	private $width = 0;

	/**
	 * Height of the crop.
	 *
	 * @var float
	 */
	private $height = 0;

	/**
	 * Constructor.
	 *
	 * Will populate object properties from the provided arguments.
	 *
	 * @param float $left   Percentage from the left for the crop.
	 * @param float $top    Percentage from the top for the crop.
	 * @param float $width  Percentage width for the crop.
	 * @param float $height Percentage height for the crop.
	 */
	public function __construct( $left, $top, $width, $height ) {
		$this->left   = floatval( $left );
		$this->top    = floatval( $top );
		$this->width  = floatval( $width );
		$this->height = floatval( $height );
	}

	/**
	 * Update the image metadata with the modifier.
	 *
	 * @access public
	 *
	 * @param array $meta Metadata to update.
	 * @return array Updated metadata.
	 */
	public function apply_to_meta( $meta ) {
		$meta['crop_left']   = $this->left;
		$meta['crop_top']    = $this->top;
		$meta['crop_width']  = $this->width;
		$meta['crop_height'] = $this->height;

		return $meta;
	}

	/**
	 * Apply the modifier to the image
	 *
	 * @access public
	 *
	 * @param WP_Image_Editor $image Image editor.
	 * @return bool|WP_Error True on success, WP_Error object or false on failure.
	 */
	public function apply_to_image( $image ) {
		$size = $image->get_size();

		$left   = round( ( $size['width'] * $this->left ) / 100.0 );
		$top    = round( ( $size['height'] * $this->top ) / 100.0 );
		$width  = round( ( $size['width'] * $this->width ) / 100.0 );
		$height = round( ( $size['height'] * $this->height ) / 100.0 );

		return $image->crop( $left, $top, $width, $height );
	}

	/**
	 * Gets the new filename based on metadata.
	 *
	 * @access public
	 *
	 * @param array $meta Image metadata.
	 * @return string Filename for the edited image.
	 */
	public static function get_filename( $meta ) {
		if ( isset( $meta['crop_width'] ) && $meta['crop_width'] > 0 ) {
			$target_file = sprintf( 'crop-%d-%d-%d-%d', round( $meta['crop_left'], 2 ), round( $meta['crop_top'], 2 ), round( $meta['crop_width'], 2 ), round( $meta['crop_height'], 2 ) );

			// We need to change the original name to include the crop. This way if it's cropped again we won't clash.
			$meta['original_name'] = $target_file;

			return $target_file;
		}

		return false;
	}

	/**
	 * Gets the default metadata for the crop modifier.
	 *
	 * @access public
	 *
	 * @return array Default metadata.
	 */
	public static function get_default_meta() {
		return array();
	}
}
