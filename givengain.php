<?php
/**
 * Plugin Name: GivenGain
 * Plugin URI: http://woothemes.com/products/givengain/
 * Description: Display your GivenGain cause or activist data using either a template tag, shortcode or widget (or all three!).
 * Author: WooThemes | GivenGain
 * Author URI: http://woothemes.com/
 * Version: 1.0.0
 * Stable tag: 1.0.0
 * License: GPL v2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class Givengain {
	/**
	 * The main plugin file.
	 * @access  private
	 * @since   1.0.0
	 * @var     string
	 */
	private $_file;

	/**
	 * The context in which the plugin is functioning (admin or frontend.
	 * @access  public
	 * @since   1.0.0
	 * @var     object
	 */
	public $context;

	/**
	 * Class constructor.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->_file = __FILE__;
		if ( is_admin() ) {
			require_once( 'classes/class-givengain-admin.php' );
			$this->context = new Givengain_Admin();
		} else {
			require_once( 'classes/class-givengain-frontend.php' );
			$this->context = new Givengain_Frontend();
		}
	} // End __construct()

	/**
	 * Generic setter for private properties.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __set ( $key, $value ) {
		// TODO
	} // End __set()

	/**
	 * Generic getting for private properties.
	 * @access  public
	 * @since   1.0.0
	 * @return  mixed
	 */
	public function __get ( $key ) {
		// TODO
	} // End __get()
} // End Class
?>