<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class Givengain_Admin {
	private $hook = '';
	private $settings = null;
	private $fields = null;
	private $token = 'givengain';
	private $errors = array();

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
	} // End __construct()

	/**
	 * Add the admin menu item.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_settings_screen () {
		$this->hook = add_options_page( __( 'GivenGain', 'givengain' ), __( 'GivenGain', 'givengain' ), 'edit_theme_options', 'givengain', array( $this, 'settings_screen' ) );

		if ( isset( $_GET['page'] ) && ( 'givengain' == $_GET['page'] ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
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
		screen_icon();
		echo '<h2>' . esc_html( sprintf( __( '%s Settings', 'givengain' ), $title ) ) . '</h2>' . "\n";
		echo '<form action="options.php" method="post">' . "\n";
		settings_fields( 'givengain' );
		do_settings_sections( 'givengain' );
		submit_button();
		echo '</form>' . "\n";
		echo '</div><!--/.wrap-->' . "\n";
	} // End settings_screen()

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

		echo '<input id="' . $args['key'] . '" name="' . $this->token . '[' . $args['key'] . ']" size="40" type="text" value="' . $options[$args['key']] . '" />' . "\n";
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

		echo '<textarea id="' . $args['key'] . '" name="' . $this->token . '[' . $args['key'] . ']" cols="42" rows="5">' . $options[$args['key']] . '</textarea>' . "\n";
		if ( isset( $args['data']['description'] ) ) {
			echo '<p><span class="description">' . $args['data']['description'] . '</span></p>' . "\n";
		}
	} // End form_field_textarea()

	/**
	 * Optionally display admin notices.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_notices () {
		settings_errors( $this->token . '-errors' );
	} // End admin_notices()

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
				add_settings_error( $this->token . '-errors', $k, $v, 'error' );
			}
		} else {
			$message = __( 'Settings updated', 'givengain' );
			add_settings_error( $this->token . '-errors', $this->token, $message, 'updated' );
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
		if ( ! is_array( $this->settings ) ) $this->settings = get_option( $this->token, array() );

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

		$fields['api_key'] = array(
		    'name' => __( 'GivenGain API Key', 'givengain' ),
		    'description' => __( 'Please enter your GivenGain API Key.', 'givengain' ),
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
} // End Class
?>