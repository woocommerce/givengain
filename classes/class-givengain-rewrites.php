<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * GivenGain Rewrites Class
 *
 * @package WordPress
 * @subpackage Givengain
 * @category Rewrites
 * @author WooThemes
 * @since 1.0.0
 */
final class Givengain_Rewrites {
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'rewrite_rules_array',array( $this, 'add_rewrite_rules' ) );
		add_action( 'init', array( $this, 'add_rewrite_tags' ) );
	} // End __construct()

	/**
	 * Add custom query variables to the list of allowed query_vars.
	 * @access  public
	 * @since   1.0.0
	 * @param   array $vars Current array of query vars.
	 * @return  array Modified array.
	 */
	public function add_query_vars ( $vars ) {
		$vars[] = 'givengain-type';
		$vars[] = 'givengain-entry';
		return $vars;
	} // End add_query_vars()

	/**
	 * Add rewrite tags, to recognise the custom query vars.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function add_rewrite_tags () {
		add_rewrite_tag( '%givengain-type%', '([^/]*)' );
		add_rewrite_tag( '%givengain-entry%', '([^/]*)' );
	} // End add_rewrite_tags()

	/**
	 * Add rewrite rules.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function add_rewrite_rules ( $rules ) {
		global $wp_rewrite;

		$rewrite_rule_structure = $wp_rewrite->root . 'givengain/%givengain-type%/%givengain-entry%';
		$new_rewrite_rules = $wp_rewrite->generate_rewrite_rules( $rewrite_rule_structure );

		return ( $rules + $new_rewrite_rules );
	} // End add_rewrite_rules()
} // End Class
?>