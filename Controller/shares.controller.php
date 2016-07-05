<?php
/**
 *
 * @package WPUpper Share Buttons
 * @author  Victor Freitas
 * @subpackage Social Icons Display
 * @version 2.0.0
 */

if ( ! function_exists( 'add_action' ) )
	exit(0);

use WPUSB_Utils as Utils;

class WPUSB_Shares_Controller
{
	private $_filter = 'wpusb-buttons';

	/**
	* Initialize the plugin by setting localization, filters, and administration functions.
	*
	* @since 1.2
	*/
	public function __construct()
	{
		add_shortcode( 'wpusb', array( &$this, 'share' ) );
		add_filter( 'the_content', array( &$this, 'content' ), 20 );
		add_action( 'wp_footer', array( &$this, 'buttons_fixed' ), 20 );
	}

	/**
	 * The content check insertions
	 *
	 * @since 1.0
	 * @param Null
	 * @return string
	 */
	protected function _check_position()
	{
		$before   = Utils::option( 'before' );
		$after    = Utils::option( 'after' );
		$position = false;

		if ( 'on' === $before && 'on' === $after )
			$position = 'full';

		if ( 'on' === $before && 'on' !== $after )
			$position = 'before';

		if ( 'on' !== $before && 'on' === $after )
			$position = 'after';

		return $position;
	}

	/**
	 * The content after it is finished processing
	 *
	 * @since 1.0
	 * @param String $content
	 * @return String content single, pages, home
	 */
	public function content( $content )
	{
		$position = $this->_check_position();

		if ( $position && $this->_is_active() ) :
			$buttons = apply_filters( $this->_filter, $this->buttons_share() );
			switch ( $position ) :
				case 'full' :
		      		$new_content  = $buttons;
		      		$new_content .= $content;
		      		$new_content .= $buttons;
		      		$content      = $new_content;
					break;

				case 'before' :
					$new_content  = $buttons;
					$new_content .= $content;
					$content      = $new_content;
					break;

				case 'after' :
					$new_content  = $content;
					$new_content .= $buttons;
					$content      = $new_content;
					break;
			endswitch;

	    	return $content;
		endif;

		return $content;
	}

	/**
	 * Make sure is activated the Share Buttons in singles
	 *
	 * @since 1.0
	 * @param Null
	 * @return Boolean
	 */
	protected function _is_single()
	{
		if ( is_single() && Utils::option( 'single' ) === 'on' )
			return true;

		return false;
	}

	/**
	 * Make sure is activated the Share Buttons in pages
	 *
	 * @since 1.0
	 * @param Null
	 * @return Boolean
	 */
	protected function _is_page()
	{
		if ( ( is_page() || is_page_template() ) && Utils::option( 'pages' ) === 'on' )
			return true;

		return false;
	}

	/**
	 * make sure is activated the Share Buttons in home
	 *
	 * @since 1.0
	 * @param Null
	 * @return Boolean
	 */
	protected function _is_home()
	{
		if ( ( is_home() || is_front_page() ) && Utils::option( 'home' ) === 'on' )
			return true;

		return false;
	}

	/**
	 * Add buttons on footer case selected layout fixed
	 *
	 * @since 1.0
	 * @param Null
	 * @return void
	 */
	public function buttons_fixed()
	{
		$this->_modal();

		if ( ! Utils::is_position_fixed() )
			return;

		if ( $this->_is_active() ) {
			$buttons = $this->buttons_share( array(), true );

			echo apply_filters( "{$this->_filter}-fixed", $buttons );
		}
	}

	/**
	 * Generate all icons sharing
	 *
	 * @since 1.0
	 * @param Array $atts
	 * @return HTML
	 *
	 */
	public function share( $atts = array() )
	{
		$atts =	shortcode_atts(
			array(
				'class_first'    => '',
				'class_second'   => '',
				'class_link'     => '',
				'class_icon'     => '',
				'layout'         => '',
				'remove_inside'  => 0,
				'remove_counter' => 0,
			),
			$atts,
			'wpusb'
		);

		$atts = array_map( array( 'WPUSB_Utils', 'esc_class' ), $atts );
		$args = array(
			'class_first'  => $atts['class_first'],
			'class_second' => $atts['class_second'],
			'class_link'   => $atts['class_link'],
			'class_icon'   => $atts['class_icon'],
			'layout'       => $atts['layout'],
			'elements'     => array(
				'remove_inside'  => $atts['remove_inside'],
				'remove_counter' => $atts['remove_counter']
			),
		);

		return Utils::get_buttons( $args );
	}

	/**
	 * Create custom class from icons
	 *
	 * @since 1.0
	 * @param Array $args
	 * @return HTML
	 */
	public function buttons_share( $atts = array(), $fixed = false )
	{
		$atts = array_map( array( 'WPUSB_Utils', 'esc_class' ), $atts );
		$args = array(
			'class_first'  => Utils::isset_get( $atts, 'class_first' ),
			'class_second' => Utils::isset_get( $atts, 'class_second' ),
			'class_link'   => Utils::isset_get( $atts, 'class_link' ),
			'class_icon'   => Utils::isset_get( $atts, 'class_icon' ),
			'layout'       => Utils::isset_get( $atts, 'layout' ),
			'elements'     => array(
				'remove_inside'  => Utils::isset_get( $atts, 'remove_inside' ),
				'remove_counter' => Utils::isset_get( $atts, 'remove_counter' ),
			),
		);

		return Utils::get_buttons( $args, $fixed );
	}

	/**
	 * Verify is active page option
	 *
	 * @since 1.0
	 * @param Null
	 * @return Boolean
	 */
	protected function _is_active()
	{
		return ( $this->_is_single() || $this->_is_page() || $this->_is_home() );
	}

	/**
	 * Adding in footer html modal social networks
	 *
	 * @since 1.0
	 * @param Null
	 * @return Void
	 */
	protected function _modal()
	{
		if ( $this->_is_active() )
			WPUSB_All_Items::init();
	}
}