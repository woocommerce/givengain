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

// Load in the frontend template functions.
require_once( 'givengain-template.php' );

/**
 * Returns the main instance of Givengain to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Givengain
 */
function Givengain() {
	return Givengain::instance();
} // End Givengain()

Givengain();

/**
 * GivenGain Class
 *
 * @package WordPress
 * @subpackage Givengain
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 */
final class Givengain {
	/**
	 * The single instance of Givengain.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin file.
	 * @access  private
	 * @since   1.0.0
	 * @var     string
	 */
	private $_file;

	/**
	 * An instance of the Givengain_API class.
	 * @access  public
	 * @since   1.0.0
	 * @var     string
	 */
	public $api;

	/**
	 * The context in which the plugin is functioning (admin or frontend.
	 * @access  public
	 * @since   1.0.0
	 * @var     object
	 */
	public $context;

	/**
	 * The custom URL rewrite rules, for displaying GivenGain API data.
	 * @access  public
	 * @since   1.0.0
	 * @var     object
	 */
	public $rewrites;

	/**
	 * Class constructor.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->_file = __FILE__;

		// Instantiate the GivenGain API connector class.
		require_once( 'classes/class-givengain-api.php' );
		$this->api = new Givengain_API( $this->_file );

		// Instantiate the GivenGain rewrite rules class.
		require_once( 'classes/class-givengain-rewrites.php' );
		$this->rewrites = new Givegain_Rewrites();

		if ( is_admin() ) {
			// Instantiate the GivenGain administration class.
			require_once( 'classes/class-givengain-admin.php' );
			$this->context = new Givengain_Admin( $this->_file, $this->api );
		} else {

			// Instantiate the GivenGain frontend output class.
			require_once( 'classes/class-givengain-frontend.php' );
			$this->context = new Givengain_Frontend( $this->_file, $this->api );
		}
	} // End __construct()

	/**
	 * Main Givengain Instance
	 *
	 * Ensures only one instance of Givengain is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Givengain()
	 * @return Main Givengain instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

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