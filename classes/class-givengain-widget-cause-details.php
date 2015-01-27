<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Givengain "Causes" Widget Class
 *
 * Widget class for the "Causes" widget for Givengain.
 *
 * @package WordPress
 * @subpackage Givengain
 * @category Widgets
 * @author Matty
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - __construct()
 */
class Givengain_Widget_Cause_Details extends Givengain_Widget_Base {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct () {
		parent::__construct();
		/* Widget variable settings. */
		$this->givengain_endpoint = 'cause/%cause_id%';
		$this->givengain_widget_cssclass = 'widget_givengain_cause_details';
		$this->givengain_widget_description = __( 'Details for a specific GivenGain cause', 'givengain' );
		$this->givengain_widget_idbase = 'widget_givengain_cause_details';
		$this->givengain_widget_title = __('Cause Details (GivenGain)', 'givengain' );

		$this->init();

		$this->defaults = array(
						'title' => __( 'Cause Details', 'givengain' ),
						'limit' => 5,
						'per_row' => 1,
						'cause_id' => 0
					);

		add_action( 'widget_givengain_cause_details_bottom', array( $this, 'display_donate_button' ) );
	} // End Constructor

	/**
	 * Return an array of field data.
	 * @since  1.0.0
	 * @return array Field data for the fields pertaining to this widget.
	 */
	protected function get_fields () {
		$fields = array();

		$causes = givengain_get_data( 'cause', array() );

		if ( 0 < count( $causes ) ) {
			foreach ( $causes as $k => $v ) {
				$causes[$v->id] = $v->name;
				unset( $causes[$k] );
			}
		}

		$fields['limit'] = array( 'type' => 'text', 'name' => __( 'Limit', 'givengain' ), 'args' => array( 'key' => 'limit' ) );
		$fields['per_row'] = array( 'type' => 'text','name' => __( 'Per Row', 'givengain' ), 'args' => array( 'key' => 'per_row' ) );
		$fields['cause_id'] = array( 'type' => 'select','name' => __( 'Cause', 'givengain' ), 'args' => array( 'key' => 'cause_id', 'data' => array( 'options' => $causes ) ) );

		return $fields;
	} // End get_fields()


	/**
	 * Display a donation button at the end of the widget content.
	 * @access public
	 * @since  1.0.0
	 * @param  array $args Widget instance arguments.
	 * @return void
	 */
	public function display_donate_button ( $args ) {
		if ( ! is_array( $args ) || ! isset( $args['cause_id'] ) ) {
			return false;
		}

		echo '<p><a href="' . esc_url( 'http://givengain.com/cause/' . intval( $args['cause_id'] ) . '/' ) . '" title="' . esc_attr__( 'View more and donate at GivenGain.com', 'givengain' ) . '" class="button">' . __( 'View more and donate at GivenGain.com', 'givengain' ) . '</a></p>' . "\n";
	} // End display_donate_button()
} // End Class
?>