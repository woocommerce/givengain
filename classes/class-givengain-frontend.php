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
		add_action( 'the_post', array( $this, 'setup_the_post' ) );
		add_filter( 'the_author', array( $this, 'hijack_the_author' ), 50, 1 );
	} // End __construct()

	/**
	 * Setup data for the current GivenGain entry.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function setup_the_post () {
		if ( ! is_givengain_archive() && ! is_givengain_single() ) return;
		global $post, $wp_object_cache, $wp_query;
		$post_data = get_post( $post );
		$wp_object_cache->posts[$wp_query->current_post] = $post_data;
		$wp_query->posts[$wp_query->current_post] = $post_data;
	} // End setup_the_post()

	/**
	 * On GivenGain screens, replace query data with GivenGain API data.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function hijack_wp_query () {
		global $wp_query, $post, $wp_object_cache, $authordata;
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

		// Make sure we always have an array, to simplify the rest of our logic.
		if ( ! is_array( $data ) ) {
			$data = array( $data );
		}

		if ( 0 < count( $data ) ) {
			$count = 0;

			// If it's a single entry, add some extra data.
			if ( is_givengain_single() && ( stristr( $type, 'post' ) || stristr( $type, 'project' ) ) ) {
				$author_endpoint = $endpoint;
				$author_endpoint = str_replace( '_project', '', $author_endpoint );
				$author_endpoint = str_replace( '_post', '', $author_endpoint );
				$author_endpoint = str_replace( '_id/', '/', $author_endpoint );
				$type_arg_bits = explode( '/', $author_endpoint );
				$type_arg = str_replace( '%', '', $type_arg_bits[1] );

				$author_args = array();

				if ( isset( $data[0]->cause_id ) ) {
					$author_args[$type_arg] = $data[0]->cause_id;
				}

				$author_data = $this->_get_api_data( $author_endpoint, $author_args );
			}

			// Backup normal posts, and replace with GivenGain posts.
			$wp_object_cache->normal_posts = $wp_object_cache->posts;
			$wp_object_cache->posts = array();

			foreach ( $data as $k => $v ) {
				$count++;
				$v->type = $type; // Pass the type value through as well.
				$data[$k] = $this->_format_api_response( $v );
				if ( 1 == $count ) {
					$post = $data[$k];
				}
				// Author data.
				if ( isset( $author_data ) ) {
					$post->author = 'givengain-' . $author_data->id;
					$post->author_name = esc_html( $author_data->name );

					$authordata = new WP_User( 'givengain-' . $author_data->id, esc_html( $author_data->name ) );
					$authordata->data = new StdClass();
					$authordata->id = 'givengain-' . $author_data->id;
					$authordata->data->id = 'givengain-' . $author_data->id;
					$authordata->data->display_name = esc_html( $author_data->name );
				}
				wp_cache_add( $data[$k]->ID, $data[$k], 'posts' );
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
	 * Replace the author with the GivenGain constructed author.
	 * @access  public
	 * @since   1.0.0
	 * @param   object/string $author    The author.
	 * @return  object/string            The modified author.
	 */
	public function hijack_the_author ( $author ) {
		global $post;
		if ( 'givengain' == $post->post_type ) {
			// TODO
		}

		return $author;
	} // End hijack_the_author()

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
			if ( true == apply_filters( 'givengain_link_externally', false ) ) {
				$permalink = esc_url( $post->guid );
			} else {
				// TODO - Create internal links, using custom permalink structure, or default, based on stored permastruct.
				$structure = get_option( 'permalink_structure' );
				$url = site_url();
				$url_bits = explode( '/', str_replace( 'http://', '', $post->guid ) );

				$args = array( 'givengain-type' => $url_bits[1] );
				if ( isset( $url_bits[2] ) && '' != $url_bits[2] ) {
					$args['givengain-entry'] = $url_bits[2];
				}

				// If this is a sub-content type, use those values instead, and make a new type setting, combining 2 and 4.
				if ( isset( $url_bits[3] ) && isset( $url_bits[4] ) ) {
					$args['givengain-type'] = $url_bits[1] . '_' . $url_bits[3];
					$args['givengain-entry'] = $url_bits[4];
				}

				if ( 's' == substr($args['givengain-type'], -1 ) ) {
					$args['givengain-type'] = substr($args['givengain-type'], 0, ( strlen( $args['givengain-type'] ) -1 ) );
				}

				if ( '' == $structure ) {
					// We're using the default structure, so many a query string.
					foreach ( $args as $k => $v ) {
						$url = add_query_arg( $k, urlencode( $v ), $url );
					}
				} else {
					// Make a pretty permalink.
					$url = trailingslashit( site_url( '/givengain' ) ) . trailingslashit( urlencode( $args['givengain-type'] ) ) . trailingslashit( urlencode( $args['givengain-entry'] ) );
				}

				$permalink = $url;
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
		$response->guid = $data->link;
		$response->post_type = 'givengain';
		$response->givengain_data = array();

		// Backup the original object, for passing through to filters.
		$original_data = $data;

		// A few parameters are only there for single entries or special cases. Work with those.
		if ( isset( $data->name ) ) {
			$response->post_title = $data->name;
			unset( $data->name );
		} else {
			if ( isset( $data->first_name ) && isset( $data->last_name ) ) {
				$response->post_title = $data->first_name . ' ' . $data->last_name;
				unset( $data->first_name );
				unset( $data->last_name );
			}
		}

		if ( isset( $data->bio ) ) {
			$response->post_content = $data->bio;
			$response->post_excerpt = apply_filters( 'get_the_excerpt', $data->bio );
			unset( $data->bio );
		}

		if ( isset( $data->description ) ) {
			$response->post_content = apply_filters( 'givengain_entry_description', $data->description, $original_data );
			$response->post_excerpt = apply_filters( 'get_the_excerpt', $data->description );
			unset( $data->description );
		}

		// Remove the parameters we've already used.
		unset( $data->id );
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