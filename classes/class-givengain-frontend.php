<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * GivenGain Frontend Class
 *
 * @package WordPress
 * @subpackage Givengain
 * @category Frontend
 * @author WooThemes
 * @since 1.0.0
 */
final class Givengain_Frontend {
	public $api = '';
	private $_file = '';

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file, $api_obj ) {
		$this->_file = $file;
		$this->api = $api_obj;

		add_action( 'pre_get_posts', array( $this, 'prepare_output_object' ) );
	} // End __construct()

	public function prepare_output_object ( $query ) {
		if ( ! $query->is_main_query() ) return;

		// TODO
	} // End prepare_output_object()
} // End Class
?>