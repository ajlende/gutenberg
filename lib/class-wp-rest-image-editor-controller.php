<?php
/**
 * Start: Include for phase 2
 * REST API: WP_REST_Image_Editor_Controller class
 *
 * @package    WordPress
 * @subpackage REST_API
 */

/**
 * Image editor
 */
include_once __DIR__ . '/image-editor/class-image-editor.php';

/**
 * Controller which provides REST API endpoints for image editing.
 *
 * @since 7.x ?
 *
 * @see WP_REST_Controller
 */
class WP_REST_Image_Editor_Controller extends WP_REST_Controller {

	/**
	 * Constructs the controller.
	 *
	 * @since 7.x ?
	 * @access public
	 */
	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'media';
	}

	/**
	 * Registers the necessary REST API routes.
	 *
	 * @since 7.x ?
	 * @access public
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/edit',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'apply_edits' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'args'                => array(
						array(
							'modifiers' => array(
								'type'     => 'array',
								'required' => true,
								'items'    => array(
									'anyOf' => array(
										array(
											'type'       => 'object',
											'properties' => array(
												'modifier' => array(
													'type' => 'string',
													'required' => true,
													'const' => 'crop',
												),
												'left'     => array(
													'type' => 'number',
													'required' => true,
													'minimum' => 0,
													'maximum' => 100,
												),
												'top'      => array(
													'type' => 'number',
													'required' => true,
													'minimum' => 0,
													'maximum' => 100,
												),
												'width'    => array(
													'type' => 'number',
													'required' => true,
													'minimum' => 1,
													'maximum' => 100,
												),
												'height'   => array(
													'type' => 'number',
													'required' => true,
													'minimum' => 1,
													'maximum' => 100,
												),
											),
										),
										array(
											'type'       => 'object',
											'properties' => array(
												'modifier' => array(
													'type' => 'string',
													'required' => true,
													'const' => 'rotate',
												),
												'angle'    => array(
													'type' => 'integer',
													'required' => true,
												),
											),
										),
										array(
											'type'       => 'object',
											'properties' => array(
												'modifier' => array(
													'type' => 'string',
													'required' => true,
													'const' => 'flip',
												),
												'horizontal' => array(
													'type' => 'boolean',
													'required' => true,
												),
												'vertical' => array(
													'type' => 'boolean',
													'required' => true,
												),
											),
										),
									),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Checks if the user has permissions to make the request.
	 *
	 * @since 7.x ?
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function permission_callback( $request ) {
		if ( ! current_user_can( 'edit_post', $request['id'] ) ) {
			$error = __( 'Sorry, you are not allowed to edit images.', 'gutenberg' );
			return new WP_Error( 'rest_cannot_edit_image', $error, array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_Error( 'rest_cannot_edit_image', __( 'Sorry, you are not allowed to upload media on this site.', 'gutenberg' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Applies all edits in one go.
	 *
	 * @since 7.x ?
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error If successful image JSON for the modified image, otherwise a WP_Error.
	 */
	public function apply_edits( $request ) {
		$modifiers = array();
		foreach ( $request['modifiers'] as $modifier ) {
			if ( 'rotate' === $modifier['modifier'] ) {
				$modifiers[] = new Image_Editor_Rotate( $modifier['angle'] );
			} elseif ( 'flip' === $modifier['modifier'] ) {
				$modifiers[] = new Image_Editor_Flip( $modifier['vertical'], $modifier['horizontal'] );
			} elseif ( 'crop' === $modifier['modifier'] ) {
				$modifiers[] = new Image_Editor_Crop( $modifier['left'], $modifier['top'], $modifier['width'], $modifier['height'] );
			}
		}
		return $this->editor->modify_image( $request['media_id'], $modifiers );
	}
}
