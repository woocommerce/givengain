<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display a list of an entry's posts, if applicable.
 * @since  1.0.0
 * @param  string $content The content.
 * @param  object $data    The current data object.
 * @return string          The modified content.
 */
function givengain_maybe_add_posts_list ( $content, $data ) {
	if ( is_object( $data ) && isset( $data->posts ) && 0 < count( (array)$data->posts ) ) {
		// Loop through and grab only published posts.
		$entries = array();
		foreach ( $data->posts as $k => $v ) {
			$entry = givengain_get_data( $data->type . '_post/%' . $data->type . '_post_id%', array( $data->type . '_post_id' => intval( $v ) ) );
			if ( is_object( $entry ) && isset( $entry->id ) ) {
				$entries[$v] = $entry;
			}
		}

		if ( 0 < count( $entries ) ) {
			$html = '';
			$html .= '<h3>' . __( 'Posts', 'givengain' ) . '</h3>' . "\n";
			$html .= '<ul class="givengain-posts">' . "\n";
			foreach ( $entries as $k => $v ) {
				$html .= '<li class="givengain-post post-id-' . esc_attr( $k ) . '">' . "\n";
				$html .= '<a href="' . esc_url( givengain_construct_permalink( array( 'givengain-type' => $data->type . '_post', 'givengain-entry' => $k ) ) ) . '">' . esc_html( $entry->name ) . '</a>' . "\n";
				$html .= '</li>' . "\n";
			}
			$html .= '</ul>' . "\n";
		}

		if ( '' != $html ) {
			$content .= ' ' . $html;
		}
	}
	return $content;
} // End givengain_maybe_add_posts_list()
add_filter( 'givengain_entry_description', 'givengain_maybe_add_posts_list', 10, 2 );

/**
 * Display a list of an entry's projects, if applicable.
 * @since  1.0.0
 * @param  string $content The content.
 * @param  object $data    The current data object.
 * @return string          The modified content.
 */
function givengain_maybe_add_projects_list ( $content, $data ) {
	if ( is_object( $data ) && isset( $data->projects ) && 0 < count( (array)$data->projects ) ) {
		// Loop through and grab only published posts.
		$entries = array();
		foreach ( $data->projects as $k => $v ) {
			$entry = givengain_get_data( $data->type . '_project/%' . $data->type . '_project_id%', array( $data->type . '_project_id' => intval( $v ) ) );
			if ( is_object( $entry ) && isset( $entry->id ) ) {
				$entries[$v] = $entry;
			}
		}

		if ( 0 < count( $entries ) ) {
			$html = '';
			$html .= '<h3>' . __( 'Projects', 'givengain' ) . '</h3>' . "\n";
			$html .= '<ul class="givengain-projects">' . "\n";
			foreach ( $entries as $k => $v ) {
				$html .= '<li class="givengain-project project-id-' . esc_attr( $k ) . '">' . "\n";
				$html .= '<a href="' . esc_url( givengain_construct_permalink( array( 'givengain-type' => $data->type . '_project', 'givengain-entry' => $k ) ) ) . '">' . esc_html( $entry->name ) . '</a>' . "\n";
				$html .= '</li>' . "\n";
			}
			$html .= '</ul>' . "\n";
		}

		if ( '' != $html ) {
			$content .= ' ' . $html;
		}
	}
	return $content;
} // End givengain_maybe_add_projects_list()
add_filter( 'givengain_entry_description', 'givengain_maybe_add_projects_list', 15, 2 );

/**
 * Display a link back to GivenGain.com at the end of an entry's content.
 * @since  1.0.0
 * @param  string $content The content.
 * @param  object $data    The current data object.
 * @return string          The modified content.
 */
function givengain_add_linkback ( $content, $data ) {
	if ( is_object( $data ) && isset( $data->link ) ) {
		$text = __( 'View on GivenGain.com', 'givengain' );
		$content .= ' <a href="' . esc_url( $data->link ) . '" class="button" title="' . esc_attr( $text ) . '">' . $text . '</a>' . "\n";
	}
	return $content;
} // End givengain_add_linkback()
add_filter( 'givengain_entry_description', 'givengain_add_linkback', 30, 2 );

/**
 * Display a link back to the relevant parent entry.
 * @since  1.0.0
 * @param  string $content The content.
 * @param  object $data    The current data object.
 * @return string          The modified content.
 */
function givengain_maybe_add_parent_link ( $content, $data ) {
	if ( is_object( $data ) ) {
		if ( isset( $data->type ) ) {
			$type_bits = explode( '_', $data->type );
			$parent_type_id = $type_bits[0] . '_id';
			if ( isset( $data->$parent_type_id ) ) {
				$endpoint = $type_bits[0] . '/%' . esc_attr( $type_bits[0] ) . '_id%';
				$args = array( $type_bits[0] . '_id' => $data->$parent_type_id );
				$parent = givengain_get_data( $endpoint, $args );
				if ( ! isset( $parent->id ) || ! isset( $parent->name ) ) {
					return; // No data, no display.
				}
				$text = sprintf( __( 'Read more about %s', 'givengain' ), $parent->name );
				$content .= ' <a href="' . esc_url( givengain_construct_permalink( array( 'givengain-type' => $type_bits[0], 'givengain-entry' => $parent->id ) ) ) . '" class="button" title="' . esc_attr( $text ) . '">' . $text . '</a>' . "\n";
			}
		}
	}
	return $content;
} // End givengain_maybe_add_parent_link()
add_filter( 'givengain_entry_description', 'givengain_maybe_add_parent_link', 20, 2 );
?>