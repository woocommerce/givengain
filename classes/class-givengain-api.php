<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * GivenGain API Class
 *
 * @package WordPress
 * @subpackage Givengain
 * @category API
 * @author WooThemes
 * @since 1.0.0
 */
final class Givengain_API {
	protected $_transient_expire_time;
	private $_client_id;
	private $_api_url = 'https://api.givengain.com/';
	private $_token = 'givengain';

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {
		$this->_file = $file;
		$this->_transient_expire_time = 60 * 60 * 24; // 1 day.
		$this->_client_id = '0892a24bd09fb786b201f3ba6887e34fd37380c0';
	} // End __construct()

	/**
	 * Retrieve data for the /me endpoint.
	 * @access  public
	 * @since   1.0.0
	 * @return  array
	 */
	public function get_endpoint_me () {
		$data = '';
		$transient_key = $this->_token . '-endpoint-me';

// delete_transient( $transient_key ); // DEBUG

		if ( false === ( $data = get_transient( $transient_key ) ) ) {
			$response = $this->request_endpoint_me();

			if ( isset( $response->data ) ) {
				$data = json_encode( $response );
				set_transient( $transient_key, $data, $this->_transient_expire_time );
			}
		}

		return json_decode( $data );
	} // End get_endpoint_me()

	/**
	 * Generic getting for private properties.
	 * @access  public
	 * @since   1.0.0
	 * @return  mixed
	 */
	public function __get ( $key ) {
		switch ( $key ) {
			case 'access_token':
				$settings = $this->_get_settings();
				if ( isset( $settings['access_token'] ) )
					return $settings['access_token'];
				else
					return '';
			break;
			default:
			break;
		}
	} // End __get()

	/**
	 * Retrieve profile data for the stored API key, as an API status check.
	 * @access  public
	 * @since   1.0.0
	 * @return  array
	 */
	public function api_status_check () {
		$data = array();
		if ( ! $this->_has_access_token() ) return false;
		$response = $this->_request( 'me', array(), 'get' );

		if( is_wp_error( $response ) ) {
		   $data = new StdClass;
		} else {
		   $data = $response;
		}

		return $data;
	} // End api_status_check()

	/**
	 * Return an array of accepted API endpoints.
	 * @access  private
	 * @since   1.0.0
	 * @return  array Accepted API endpoints.
	 */
	private function _get_accepted_endpoints () {
		return array(
					'me',
					'cause',
					'cause_project',
					'cause_post',
					'activist',
					'activist_project',
					'donation',
					'comment',
					'location'
				);
	} // End _get_accepted_endpoints()

	/**
	 * Retrieve data from the specified endpoint.
	 * @access  public
	 * @since   1.0.0
	 * @param   string $endpoint The API endpoint to touch.
	 * @param   array $args
	 * @return  array
	 */
	public function get_data ( $endpoint, $args = array(), $method = 'get' ) {
		$data = array();
		if ( ! $this->_has_access_token() || ! in_array( $endpoint, $this->_get_accepted_endpoints() ) ) return false;
		$response = $this->_request( $endpoint, $args, $method );

		if( is_wp_error( $response ) ) {
		   $data = new StdClass;
		} else {
		   $data = $response;
		}

		return $data;
	} // End get_data()

	/**
	 * Make a request to the API.
	 * @access  private
	 * @since   1.0.0
	 * @param   string $endpoint The endpoint of the API to be called.
	 * @param   array  $params   Array of parameters to pass to the API.
	 * @return  object           The response from the API.
	 */
	private function _request ( $endpoint, $params = array(), $method = 'post' ) {
		$return = '';
		if ( ! $this->_has_access_token() ) return $return;

		// Default parameters.
		$params['client_id'] = $this->_client_id;
		$params['access_token'] = $this->__get( 'access_token' );

		if ( $method == 'get' ) {
			$url = $this->_api_url . $endpoint;

			if ( count( $params ) > 0 ) {
				$url .= '?';
				$count = 0;
				foreach ( $params as $k => $v ) {
					$count++;

					if ( $count > 1 ) {
						$url .= '&';
					}

					$url .= $k . '=' . $v;
				}
			}
			$response = wp_remote_get( $url,
				array(
					'sslverify' => apply_filters( 'https_local_ssl_verify', false )
				)
			);
		} else {
			$response = wp_remote_post( $this->_api_url . $endpoint,
				array(
					'body' => $params,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false )
				)
			);
		}

		if ( ! is_wp_error( $response ) ) {
			$return = json_decode( $response['body'] );
		}

		return $return;
	} // End _request()

	/**
	 * Check if there is an API key present.
	 * @access  private
	 * @since   1.0.0
	 * @return  boolean False if none, true if exists.
	 */
	private function _has_access_token () {
		$settings = $this->_get_settings();
		if ( ! isset( $settings['access_token'] ) || '' == $settings['access_token'] ) return false;
		return true;
	} // End _has_access_token()

	/**
	 * If the parameter is an object with our expected properties, display an error notice.
	 * @access  private
	 * @since   1.0.0
	 * @param   object/string $obj Object if an error, empty string if not.
	 * @return  boolean/string     String if an error, boolean if not.
	 */
	private function _maybe_display_error ( $obj ) {
		if ( ! is_object( $obj ) || ! isset( $obj->code ) || ! isset( $obj->error_message ) ) return;
		return '<p class="givengain-error error">' . esc_html( $obj->error_message ) . '</p>' . "\n";
	} // End _maybe_display_error()

	/**
	 * Retrieve stored settings.
	 * @access  private
	 * @since   1.0.0
	 * @return  array Stored settings.
	 */
	private function _get_settings () {
		return wp_parse_args( (array)get_option( $this->_token, array( 'access_token' => '' ) ), array( 'access_token' => '' ) );
	} // End _get_settings()
} // End Class
?>