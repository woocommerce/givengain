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
	private $_api = '';
	private $_file = '';

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file, $api_obj ) {
		$this->_file = $file;
		$this->_api = $api_obj;
	} // End __construct()
} // End Class
?>