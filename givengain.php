<?php
/**
 * Plugin Name: GivenGain
 * Plugin URI: http://woothemes.com/products/givengain/
 * Description: Display your GivenGain cause or activist data using either a template tag, shortcode or widget (or all three!).
 * Author: WooThemes | GivenGain
 * Author URI: http://woothemes.com/
 * Version: 1.0.0
 * Stable tag: 1.0.0
 * License: GPL v3 or later - http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load in the frontend template functions.
require_once( 'givengain-template.php' );

// Load in the hooked functions.
require_once( 'givengain-hooks.php' );

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
	 * The plugin's version.
	 * @access  private
	 * @since   1.0.0
	 * @var     string
	 */
	private $_version;

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
		$this->_version = '1.0.0';

		// Instantiate the GivenGain API connector class.
		require_once( 'classes/class-givengain-api.php' );
		$this->api = new Givengain_API( $this->_file );

		// Instantiate the GivenGain rewrite rules class.
		require_once( 'classes/class-givengain-rewrites.php' );
		$this->rewrites = new Givengain_Rewrites();

		if ( is_admin() ) {
			// Instantiate the GivenGain administration class.
			require_once( 'classes/class-givengain-admin.php' );
			$this->context = new Givengain_Admin( $this->_file, $this->api );
		} else {

			// Instantiate the GivenGain frontend output class.
			require_once( 'classes/class-givengain-frontend.php' );
			$this->context = new Givengain_Frontend( $this->_file, $this->api );
		}

		// Run this on activation.
		register_activation_hook( $this->_file, array( $this, 'activation' ) );
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
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'givengain', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'givengain';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
		$this->flush_rewrite_rules();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( '' != $this->_version ) {
			update_option( 'givengain' . '-version', $this->_version );
		}
	} // End register_plugin_version()

	/**
	 * Flush the rewrite rules
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	private function flush_rewrite_rules () {
		flush_rewrite_rules();
	} // End flush_rewrite_rules()
} // End Class
?>