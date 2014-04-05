<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display a list of an entry's projects, if applicable.
 * @since  1.0.0
 * @param  string $content The content.
 * @param  object $data    The current data object.
 * @return string          The modified content.
 */
function givengain_maybe_add_projects_list ( $content, $data ) {
	if ( is_object( $data ) && isset( $data->projects ) && 0 < count( (array)$data->projects ) ) {
		$html = '<ul class="givengain-projects">' . "\n";
		foreach ( $data->projects as $k => $v ) {
			$entry = givengain_get_data( $data->type . '_project/%' . $data->type . '_project_id%', array( $data->type . '_project_id' => intval( $v ) ) );
			$html .= '<li class="project project-id-' . esc_attr( $v ) . '">' . "\n";
			$html .= '<a href="' . esc_url( givengain_construct_permalink( array( 'givengain-type' => $data->type . '_project', 'givengain-entry' => $v ) ) ) . '">' . esc_html( $entry->name ) . '</a>' . "\n";
			$html .= '</li>' . "\n";
		}
		$html .= '</ul>' . "\n";

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
		$text = __( 'View more on GivenGain.com', 'givengain' );
		$content .= ' <a href="' . esc_url( $data->link ) . '" class="button" title="' . esc_attr( $text ) . '">' . $text . '</a>' . "\n";
	}
	return $content;
} // End givengain_add_linkback()
add_filter( 'givengain_entry_description', 'givengain_add_linkback', 10, 2 );
?>