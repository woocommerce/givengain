<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'givengain_construct_permalink' ) ) {
/**
 * Create an internal permalink for a given entry.
 * @since  1.0.0
 * @return string
 */
function givengain_construct_permalink ( $args ) {
	$structure = get_option( 'permalink_structure' );
	$url = site_url();

	if ( '' == $structure ) {
		// We're using the default structure, so many a query string.
		foreach ( $args as $k => $v ) {
			$url = add_query_arg( $k, urlencode( $v ), $url );
		}
	} else {
		// Make a pretty permalink.
		$url = trailingslashit( site_url( '/givengain' ) ) . trailingslashit( urlencode( $args['givengain-type'] ) ) . trailingslashit( urlencode( $args['givengain-entry'] ) );
	}

	return $url;
} // End givengain_construct_permalink()
}

if ( ! function_exists( 'is_givengain_single' ) ) {
/**
 * Determine if we're viewing a GivenGain entry.
 * @since  1.0.0
 * @return boolean
 */
function is_givengain_single () {
	$response = false;
	if ( '' != get_query_var( 'givengain-entry' ) )
		$response = true;

	return $response;
} // End is_givengain_single()
}

if ( ! function_exists( 'is_givengain_archive' ) ) {
/**
 * Determine if we're viewing a GivenGain archive.
 * @since  1.0.0
 * @return boolean
 */
function is_givengain_archive () {
	$response = false;
	$type = get_query_var( 'givengain-type' );
	if ( '' == get_query_var( 'givengain-entry' ) && '' != $type && in_array( $type, array( 'cause', 'cause_project', 'cause_post', 'activist', 'activist_project' ) ) )
		$response = true;

	return $response;
} // End is_givengain_archive()
}

if ( ! function_exists( 'givengain_get_data' ) ) {
/**
 * Wrapper function to get the data from the Givengain_Frontend class.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return array/boolean       Array if true, boolean if false.
 */
function givengain_get_data ( $type = 'cause', $args = '' ) {
	return Givengain()->context->api->get_data( $type, $args );
} // End givengain_get_data()
}

/**
 * Enable the usage of do_action() to display data within a theme/plugin.
 *
 * @since  1.0.0
 */
add_action( 'givengain', 'givengain_output' );

if ( ! function_exists( 'givengain_output' ) ) {
/**
 * Display or return HTML-formatted data.
 * @param  string $type The type of data to return.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return string
 */
function givengain_output ( $type = 'cause', $args = '' ) {
	$defaults = array(
		'limit' => 5,
		'id' => 0,
		'echo' => true,
		'per_row' => 3,
		'title' => '',
		'before' => '<div class="widget widget_givengain">',
		'after' => '</div><!--/.widget widget_givengain-->',
		'before_title' => '<h2>',
		'after_title' => '</h2>'
	);

	$args = wp_parse_args( $args, $defaults );

	// Make sure we have a clean query_args array.
	$query_args = array( 'limit' => intval( $args['limit'] ) );
	foreach ( $args as $k => $v ) {
		if ( ! in_array( $k, array_keys( $defaults ) ) ) $query_args[$k] = $v;
	}

	// Allow child themes/plugins to filter here.
	$args = apply_filters( 'givengain_output_args', $args, $type );
	$args = apply_filters( 'givengain_' . esc_attr( $args ) . '_output_args', $args, $type );
	$html = '';

	do_action( 'givengain_output_before', $args );
	do_action( 'givengain_' . esc_attr( $type ) . '_output_before', $args );

		// The Query.
		$query = givengain_get_data( $type, $query_args );

		// The Display.
		if ( ! is_wp_error( $query ) && ( ( is_array( $query ) && 0 < count( $query ) ) || is_object( $query ) ) ) {
			$html .= $args['before'] . "\n";

			if ( '' != $args['title'] ) {
				$title = esc_html( $args['title'] );
				// Allow child themes/plugins to filter here.
				$title = apply_filters( 'givengain_output_title', $title, $type, $args );
				$title = apply_filters( 'givengain_' . esc_attr( $type ) . '_output_title', $title, $type, $args );

				$html .= $args['before_title'] . $title . $args['after_title'] . "\n";
			}

			$class = str_replace( '%', '', $type );
			$class = str_replace( '/', '-', $class );

			$html .= '<div class="givengain givengain-' . esc_attr( $class ) . ' columns-' . esc_attr( intval( $args['per_row'] ) ) . '">' . "\n";

			// Begin templating logic.
			$tpl = '<li class="%%CLASS%%">%%IMAGE%%<span class="item-title">%%TITLE%%</span><div class="item-content">%%CONTENT%%</div></li>';
			$tpl = apply_filters( 'givengain_item_template', $tpl, $type, $args );
			$tpl = apply_filters( 'givengain_' . esc_attr( $type ) . '_item_template', $tpl, $type, $args );

			$i = 0;

			// If this isn't array, we can assume this is a single item. Cast as an array to make life easier for our loop.
			if ( ! is_array( $query ) ) $query = array( $query );

			$html .= '<ul>' . "\n";

			foreach ( $query as $post ) {
				$template = $tpl;
				$i++;

				$class = str_replace( '%', '', $type );
				$class = str_replace( '/', '-', $class );

				if( ( 0 == $i % $args['per_row'] ) ) {
					$class .= ' last';
				} elseif ( 0 == ( $i - 1 ) % ( $args['per_row'] ) ) {
					$class .= ' first';
				}

				$image_size = apply_filters( 'givengain_output_image_size', 'thumbnail', $post );

				$image = '';
				if ( '' != $post->thumbnail ) { $image = '<img src="' . esc_url( $post->thumbnail ) . '" alt="' . esc_attr( $post->name ) . '" />'; }

				// Allow child themes/plugins to filter here.
				$image = apply_filters( 'givengain_output_image', $image, $post, $type, $args );
				$image = apply_filters( 'givengain_' . esc_attr( $type ) . '_output_image', $image, $post, $type, $args );

				$title = $post->name;
				if ( isset( $post->link ) ) $title = '<a href="' . esc_url( $post->link ) . '" title="' . esc_attr( $title ) . '">' . $title . '</a>';

				// Optionally display the image, if it is available.
				$template = str_replace( '%%IMAGE%%', $image, $template );

				$template = str_replace( '%%CLASS%%', $class, $template );
				$template = str_replace( '%%TITLE%%', $title, $template );

				$content = '';
				if ( isset( $post->description ) ) $content = $post->description;
				$content = apply_filters( 'givengain_output_content', $content, $post, $type, $args );
				$content = apply_filters( 'givengain_' . esc_attr( $type ) . '_output_content', $content, $post, $type, $args );
				$template = str_replace( '%%CONTENT%%', $content, $template );

				$template = apply_filters( 'givengain_template', $template, $post );

				$html .= $template;

				if( ( 0 == $i % $args['per_row'] ) ) {
					$html .= '<div class="fix"></div>' . "\n";
				}
			}

			$html .= '</ul>' . "\n";

			$html .= '</div><!--/.' . esc_attr( $type ) . '-->' . "\n";
			$html .= $args['after'] . "\n";
		}

		// Allow child themes/plugins to filter here.
		$html = apply_filters( 'givengain_output_html', $html, $query, $type, $args );
		$html = apply_filters( 'givengain_' . esc_attr( $type ) . '_output_html', $html, $query, $type, $args );

		if ( $args['echo'] != true ) { return $html; }

		// Should only run is "echo" is set to true.
		echo $html;

		do_action( 'givengain_output_after', $args ); // Only if "echo" is set to true.
		do_action( 'givengain_' . esc_attr( $type ) . '_output_after', $args ); // Only if "echo" is set to true.
} // End givengain_output()
}

if ( ! function_exists( 'givengain_shortcode' ) ) {
/**
 * The shortcode function.
 * @since  1.0.0
 * @param  array  $atts    Shortcode attributes.
 * @param  string $content If the shortcode is a wrapper, this is the content being wrapped.
 * @return string          Output using the template tag.
 */
function givengain_shortcode ( $atts, $content = null ) {
	$args = (array)$atts;

	$defaults = array(
		'limit' => 5,
		'id' => 0,
		'echo' => true,
		'per_row' => 3,
		'title' => '',
		'before' => '<div class="widget widget_givengain">',
		'after' => '</div><!--/.widget widget_givengain-->',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
		'type' => 'cause'
	);

	$args = shortcode_atts( $defaults, $atts );
	$args = array_merge( $args, $atts ); // Make sure our atts aren't lost, if not in the defaults array.

	// Make sure we return and don't echo.
	$args['echo'] = false;

	// Fix integers.
	if ( isset( $args['limit'] ) ) $args['limit'] = intval( $args['limit'] );
	if ( isset( $args['per_row'] ) &&  ( 0 < intval( $args['per_row'] ) ) ) $args['per_row'] = intval( $args['per_row'] );

	return givengain_output( $args['type'], $args );
} // End givengain_shortcode()
}

add_shortcode( 'givengain', 'givengain_shortcode' );

if ( ! function_exists( 'givengain_content_default_filters' ) ) {
/**
 * Adds default filters to the "givengain_output_content" filter point.
 * @since  1.0.0
 * @return void
 */
function givengain_content_default_filters () {
	add_filter( 'givengain_output_content', 'do_shortcode' );
} // End givengain_content_default_filters()

add_action( 'givengain_output_before', 'givengain_content_default_filters' );
}
?>