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
class Givengain_Widget_Causes extends Givengain_Widget_Base {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct () {
		parent::__construct();
		/* Widget variable settings. */
		$this->givengain_endpoint = 'cause';
		$this->givengain_widget_cssclass = 'widget_givengain_causes';
		$this->givengain_widget_description = __( 'A slideshow of posts on your site', 'givengain' );
		$this->givengain_widget_idbase = 'widget_givengain_causes';
		$this->givengain_widget_title = __('Causes (GivenGain)', 'givengain' );

		$this->init();

		$this->defaults = array(
						'title' => __( 'Causes', 'givengain' ),
						'limit' => 5,
						'per_row' => 1
					);
	} // End Constructor

	/**
	 * Return an array of field data.
	 * @since  1.0.0
	 * @return array Field data for the fields pertaining to this widget.
	 */
	protected function get_fields () {
		$fields = array();

		$fields['limit'] = array( 'type' => 'text', 'name' => __( 'Limit', 'givengain' ), 'args' => array( 'key' => 'limit' ) );
		$fields['per_row'] = array( 'type' => 'text','name' => __( 'Per Row', 'givengain' ), 'args' => array( 'key' => 'per_row' ) );

		return $fields;
	} // End get_fields()
} // End Class
?>