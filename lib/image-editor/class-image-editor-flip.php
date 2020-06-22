<?php
/**
 * Start: Include for phase 2
 * Image Editor: Image_Editor_Flip class
 *
 * @package gutenberg
 * @since 7.x ?
 */

/**
 * Image editor modifier abstract class.
 */
require_once __DIR__ . '/class-image-editor-modifier.php';

/**
 * Flip/mirror image modifier.
 */
class Image_Editor_Flip extends Image_Editor_Modifier {
	/**
	 * If the image is flipped vertically.
	 *
	 * @var boolean
	 */
	private $vertical = false;

	/**
	 * If the image is flipped horizontally.
	 *
	 * @var boolean
	 */
	private $horizontal = false;

	/**
	 * Constructor.
	 *
	 * Will populate object properties from the provided arguments.
	 *
	 * @param boolean $vertical   Whether the image should be flipped vertically.
	 * @param boolean $horizontal Whether the image should be flipped horizontally.
	 */
	public function __construct( $vertical, $horizontal ) {
		$this->vertical   = $vertical;
		$this->horizontal = $horizontal;
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
		if ( $this->vertical ) {
			$meta['flip_vertical'] = ! $meta['flip_vertical'];
		}
		if ( $this->horizontal ) {
			$meta['flip_horizontal'] = ! $meta['flip_horizontal'];
		}
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
		return $image->flip( $this->vertical, $this->horizontal );
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
		$parts = array();

		if ( $meta['flip_horizontal'] ) {
			$parts[] = 'flip_horizontal';
		}

		if ( $meta['flip_vertical'] ) {
			$parts[] = 'flip_vertical';
		}

		if ( count( $parts ) > 0 ) {
			return implode( '-', $parts );
		}

		return false;
	}

	/**
	 * Gets the default metadata for the flip modifier.
	 *
	 * @access public
	 *
	 * @return array Default metadata.
	 */
	public static function get_default_meta() {
		return array(
			'flip_horizontal' => false,
			'flip_vertical'   => false,
		);
	}
}
