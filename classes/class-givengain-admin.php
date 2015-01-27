<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * GivenGain Admin Class
 *
 * @package WordPress
 * @subpackage Givengain
 * @category Admin
 * @author WooThemes
 * @since 1.0.0
 */
final class Givengain_Admin {
	private $hook = '';
	private $settings = null;
	private $fields = null;
	private $_token = 'givengain';
	private $errors = array();
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

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
		add_action( 'admin_notices', array( $this, 'maybe_display_admin_notices' ) );
	} // End __construct()

	/**
	 * Add the admin menu item.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_settings_screen () {
		$this->hook = add_options_page( __( 'GivenGain', 'givengain' ), __( 'GivenGain', 'givengain' ), 'edit_theme_options', 'givengain', array( $this, 'settings_screen' ) );
	} // End register_settings_screen()

	/**
	 * Markup for the settings screen.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function settings_screen () {
		global $title;
		echo '<div class="wrap">' . "\n";
		echo '<h2>' . esc_html( sprintf( __( '%s Settings', 'givengain' ), $title ) ) . '</h2>' . "\n";
		echo '<form action="options.php" method="post">' . "\n";
		settings_fields( $this->_token );
		do_settings_sections( $this->_token );
		// Output an API test console, if the api_key and client_secret_key are both set.
		echo $this->_maybe_render_api_test_console();
		submit_button();
		echo '</form>' . "\n";
		echo '</div><!--/.wrap-->' . "\n";
	} // End settings_screen()

	/**
	 * Render a test console for the API, if the api_key and client_secret_key are both present.
	 * @access  private
	 * @since   1.0.0
	 * @return  string Rendered HTML markup.
	 */
	private function _maybe_render_api_test_console () {
		$settings = $this->get_settings();
		$html = '';
		if ( ( isset( $settings['access_token'] ) && '' != $settings['access_token'] ) ) {
			$data = $this->_api->api_status_check();
			if ( is_object( $data ) && isset( $data->name ) ) {
				$class = 'success';
				$message = sprintf( __( 'You can successfully reach the GivenGain API. Hi, %s!', 'givengain' ), $data->name );
			} else {
				$class = 'fail';
				$message = __( 'Oh no! It seems there is an error with your API key. Please try again.', 'givengain' );
			}
			$html .= '<div id="' . esc_attr( $this->_token . '-api-test-console' ) . '" class="' . esc_attr( $class ) . '">' . "\n";
			$html .= $message;
			$html .= '</div>' . "\n";
			$html .= '<style type="text/css">#' . esc_attr( $this->_token . '-api-test-console' ) . ' { border: 1px dashed #CCCCCC; background: #EBEBEB; color: #999999; font-family: Courier, sans-serif; padding: 0.5em; max-width: 520px; margin: 1.6em 0.6em; } #' . esc_attr( $this->_token . '-api-test-console' ) . '.success { color: #266A2E; } #' . esc_attr( $this->_token . '-api-test-console' ) . '.fail { color: #CC0033; }</style>' . "\n";
		}
		return $html;
	} // End _maybe_render_api_test_console()

	/**
	 * Register settings fields.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_settings () {
		register_setting( 'givengain', 'givengain', array( $this, 'validate_settings' ) );

		add_settings_section( 'access', __( 'API Access', 'givengain' ), array( $this, 'settings_section_text' ), 'givengain' );

		$fields = $this->get_settings_fields();

		if ( is_array( $fields ) && ( 0 < count( $fields ) ) ) {
			foreach ( $fields as $k => $v ) {
				$type = 'text';
				if ( in_array( $v['type'], array( 'text', 'textarea' ) ) ) {
					$type = $v['type'];
				}

				add_settings_field( $k, $v['name'], array( $this, 'form_field_' . $type ), 'givengain', $v['section'], array( 'label_for' => $k, 'key' => $k, 'data' => $v ) );
			}
		}
	} // End register_settings()

	/**
	 * Output a text input field.
	 * @access  public
	 * @param   array $args Arguments for the field.
	 * @since   1.0.0
	 * @return  void
	 */
	public function form_field_text ( $args = null ) {
		$options = $this->get_settings();

		echo '<input id="' . $args['key'] . '" name="' . $this->_token . '[' . $args['key'] . ']" size="40" type="text" value="' . $options[$args['key']] . '" />' . "\n";
		if ( isset( $args['data']['description'] ) ) {
			echo '<span class="description">' . $args['data']['description'] . '</span>' . "\n";
		}
	} // End form_field_text()

	/**
	 * Output a textarea field.
	 * @access  public
	 * @param   array $args Arguments for the field.
	 * @since   1.0.0
	 * @return  void
	 */
	public function form_field_textarea ( $args = null ) {
		$options = $this->get_settings();

		echo '<textarea id="' . $args['key'] . '" name="' . $this->_token . '[' . $args['key'] . ']" cols="42" rows="5">' . $options[$args['key']] . '</textarea>' . "\n";
		if ( isset( $args['data']['description'] ) ) {
			echo '<p><span class="description">' . $args['data']['description'] . '</span></p>' . "\n";
		}
	} // End form_field_textarea()

	/**
	 * Add error to our internal error log.
	 * @access protected
	 * @since  1.0.0
	 * @param  string $key
	 * @param  array $data
	 * @return void
	 */
	protected function add_error ( $key, $data ) {
		if ( isset( $data['error_message'] ) ) {
			$message = $data['error_message'];
		} else {
			$message = sprintf( __( '%s is a required field', 'givengain' ), $data['name'] );
		}
		$this->errors[$key] = $message;
	} // End add_error()

	/**
	 * Parse logged errors.
	 * @access  protected
	 * @since   1.0.0
	 * @return  void
	 */
	protected function parse_errors () {
		if ( count ( $this->errors ) > 0 ) {
			foreach ( $this->errors as $k => $v ) {
				add_settings_error( $this->_token . '-errors', $k, $v, 'error' );
			}
		} else {
			$message = __( 'Settings updated', 'givengain' );
			add_settings_error( $this->_token . '-errors', $this->_token, $message, 'updated' );
		}
	} // End parse_errors()

	/**
	 * Validate fields before saving.
	 * @access  public
	 * @param   $input Data to be validated.
	 * @since   1.0.0
	 * @return  array Validated fields.
	 */
	public function validate_settings ( $input ) {
		$options = $this->get_settings();

		foreach ( $this->fields as $k => $v ) {
			if ( isset( $input[$k] ) ) {
				// Perform checks on required fields.
				if ( isset( $v['required'] ) && ( $v['required'] == true ) ) {
					if ( $input[$k] == '' ) {
						$this->add_error( $k, $v );
						continue;
					}
				}

				$value = $input[$k];

				$is_valid = false;
				if ( '' != $value ) $is_valid = true;
				if ( 'e-mail' == $v['type'] && is_email( $input[$k] ) ) $is_valid = true;

				if ( ! $is_valid ) {
					$this->add_error( $k, $v );
					continue;
				}

				switch ( $v['type'] ) {
					case 'textarea':
						$options[$k] = esc_html( $input[$k] );
					break;

					case 'e-mail':
						$options[$k] = esc_attr( $input[$k] );
					break;

					case 'text':
					default:
						$options[$k] = esc_attr( $input[$k] );
					break;
				}
			}
		}

		// Parse error messages into the Settings API.
		$this->parse_errors();
		return $options;
	} // End validate_settings()

	/**
	 * Retrieve the settings.
	 * @access  public
	 * @since   1.0.0
	 * @return  array
	 */
	public function get_settings () {
		if ( ! is_array( $this->settings ) ) $this->settings = get_option( $this->_token, array() );

		if ( ! is_array( $this->fields ) ) $this->fields = $this->get_settings_fields();

		foreach ( $this->fields as $k => $v ) {
			if ( ! isset( $this->settings[$k] ) && isset( $v['default'] ) ) {
				$this->settings[$k] = $v['default'];
			}
		}

		return $this->settings;
	} // End get_settings()

	/**
	 * Get data for the settings fields.
	 * @access   public
	 * @since    1.0.0
	 * @return   array
	 */
	public function get_settings_fields () {
		$fields = array();

		$fields['access_token'] = array(
		    'name' => __( 'API Key', 'givengain' ),
		    'description' => sprintf( __( 'Please enter your %sGivenGain API Key%s.', 'givengain' ), '<a href="' . esc_url( 'https://www.givengain.com/api/key/' ) . '" title="' . esc_attr( __( 'Get your API key from the GivenGain website.', 'givengain' ) ) . '">', '</a>' ),
		    'type' => 'text',
		    'default' => '',
		    'section' => 'access'
		);

		return $fields;
	} // End get_settings_fields()

	/**
	 * Display a description for each section.
	 *
	 * @access  public
	 * @param    array $section Data for the current section.
	 * @since    1.0.0
	 * @return    void
	 */
	public function settings_section_text ( $section ) {
		switch ( $section['id'] ) {
			case 'access':
				_e( 'Your connection to the GivenGain website.', 'givengain' );
			break;

			default:
			break;
		}
	} // End settings_section_text()

	/**
	 * Display an admin notice, if not on the admin screen and if the account isn't yet connected.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function maybe_display_admin_notices () {
		if ( ( isset( $_GET['page'] ) && 'givengain' == $_GET['page'] ) ) return; // Don't show these notices on our admin screen.

		$settings = $this->get_settings();
		if ( ! isset( $settings['access_token'] ) || '' == $settings['access_token'] ) {
			$url = add_query_arg( 'page', 'givengain', admin_url( 'admin.php' ) );
			echo '<div class="updated fade"><p>' . sprintf( __( '%sGivenGain is almost ready.%s To get started, %senter your GivenGain API key%s.', 'givengain' ), '<strong>', '</strong>', '<a href="' . esc_url( $url ) . '">', '</a>' ) . '</p></div>' . "\n";
		}
	} // End maybe_display_admin_notices()
} // End Class
?>