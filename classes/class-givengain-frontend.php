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
		add_action( 'template_redirect', array( $this, 'hijack_wp_query' ) );
		add_filter( 'post_link', array( $this, 'hijack_permalink' ), 10, 3 );
	} // End __construct()

	/**
	 * On GivenGain screens, replace query data with GivenGain API data.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function hijack_wp_query () {
		global $wp_query, $post, $wp_object_cache;
		if ( ! is_givengain_archive() && ! is_givengain_single() ) return;

		$type = get_query_var( 'givengain-type' );
		$entry = get_query_var( 'givengain-entry' );

		if ( ! in_array( $type, Givengain()->api->__get( 'accepted_endpoints' ) ) ) return;

		// Setup API request endpoint and arguments.
		if ( '' != $entry ) {
			$endpoint = $type . '/%' . $type . '_id%';
			$args = array( $type . '_id' => $entry );
		} else {
			$endpoint = $type;
			$args = array();
		}

		$data = $this->_get_api_data( $endpoint, $args );

		if ( 0 < count( $data ) ) {
			$count = 0;
			// Backup normal posts, and replace with GivenGain posts.
			$wp_object_cache->normal_posts = $wp_object_cache->posts;
			$wp_object_cache->posts = array();

			foreach ( $data as $k => $v ) {
				$count++;
				$data[$k] = $this->_format_api_response( $v );
				if ( 1 == $count ) {
					$post = $data[$k];
				}

				$wp_object_cache->posts[$data[$k]->ID] = $data[$k];
				$wp_query->posts[] = $data[$k];
			}

			$wp_query->post_count = count( $wp_query->posts );
			$wp_query->found_posts = count( $wp_query->posts );
			$wp_query->max_num_pages = 1;
			$wp_query->is_404 = false;
			if ( '' == $entry ) {
				$wp_query->is_archive = true;
			} else {
				$wp_query->is_single = true;
			}
		}
	} // End hijack_wp_query()

	/**
	 * Replace the permalink with the GivenGain constructed URL.
	 * @access  public
	 * @since   1.0.0
	 * @param   string $permalink The type of permalink structure in use.
	 * @param   object $post      The current post object.
	 * @param   string $leavename The current leave name.
	 * @return  strong            The modified permalink.
	 */
	public function hijack_permalink ( $permalink, $post, $leavename ) {
		if ( 'givengain' == $post->post_type ) {
			if ( true == apply_filters( 'givengain_link_externally', true ) ) {
				$permalink = esc_url( $post->guid );
			} else {
				// TODO - Create internal links, using custom permalink structure, or default, based on stored permastruct.
			}
		}

		return $permalink;
	} // End hijack_permalink()

	/**
	 * Format a single array of given API data, into a WordPress post-style object.
	 * @access  private
	 * @since   1.0.0
	 * @param   array  	$args API data.
	 * @return  object        Formatted object.
	 */
	private function _format_api_response ( $data ) {
		$response = new stdClass();
		$response->ID = $data->id;
		$response->post_title = $data->name;
		$response->guid = $data->link;
		$response->post_type = 'givengain';
		$response->givengain_data = array();

		// Remove the parameters we've already used.
		unset( $data->id );
		unset( $data->name );
		unset( $data->link );

		// Store any left over parameters in a dedicated array.
		if ( 0 < count( $data ) ) {
			foreach ( $data as $k => $v ) {
				$response->givengain_data[$k] = $v;
				unset( $data->$k );
			}
		}

		return $response;
	} // End _format_api_response()

	/**
	 * Get data, for the given endpoint, from the GivenGain API.
	 * @access  private
	 * @since   1.0.0
	 * @param   string $endpoint The API endpoint from which to request data.
	 * @param   array  $args     Optional arguments for the desired endpoint.
	 * @return  array            API response.
	 */
	private function _get_api_data ( $endpoint, $args = array() ) {
		return Givengain()->api->get_data( $endpoint, $args );
	} // End _get_api_data()

	/**
	 * Parse the query, if we're on a GivenGain screen.
	 * @access  public
	 * @since   1.0.0
	 * @param   object $query Query parameters.
	 * @return  void
	 */
	public function prepare_output_object ( $query ) {
		if ( ! $query->is_main_query() ) return;
		if ( ! is_givengain_archive() && ! is_givengain_single() ) return;

		$query->set( 'post_type', 'givengain' );

		$query->is_home = false;

		if ( is_givengain_archive() ) {
			$query->is_archive = true;
		} else {
			$query->is_single = true;
		}

		$query->parse_query();
	} // End prepare_output_object()
} // End Class
?>