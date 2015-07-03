<?php
/**
 * @package   Tandem_Cal
 * @author    Jordan Ramos <me@jordanramos.com>
 * @license   GPL-2.0+
 * @link      http://jordanramos.com
 * @copyright 2014 Jordan Ramos
 *
 * @wordpress-plugin
 * Plugin Name:       Tandem Cal Feed Widget
 * Description:       Constructs XML calendar feed url based on options and parses to display in widget areas.
 * Version:           1.0.0
 * Author:            Jordan Ramos
 * Author URI:        www.jordanramos.com
 * Text Domain:       tandem-cal
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 // Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
    exit;
}

class Tandem_Cal extends WP_Widget {

    /**
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'tandem-cal';

    /*--------------------------------------------------*/
    /* Constructor
    /*--------------------------------------------------*/

    public function __construct() {

        // load plugin text domain
        add_action( 'init', array( $this, 'widget_textdomain' ) );

        // Hooks fired when the Widget is activated and deactivated
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        parent::__construct(
            $this->get_widget_slug(),
            __( 'Tandem Calendar Feed', $this->get_widget_slug() ),
            array(
                'classname'  => $this->get_widget_slug().'-class',
                'description' => __( 'Constructs XML calendar feed url based on options and parses to display in widget areas.', $this->get_widget_slug() )
            )
        );

        // Register site styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

        // Refreshing the widget's cached output with each new post
        add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
        add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

        add_shortcode( 'calendar', array($this, 'tandem_cal_shortcode') );

    } // end constructor

    public function tandem_cal_shortcode( $atts ) {

        // Attributes
        extract( shortcode_atts(
            $a = array(
                'school' => '',
                'department' => '',
            ),
            $atts
            ));

        $args = array( 'before_widget' => '', 'after_widget' => '', 'before_title' => '', 'after_title' => '' );

        // Set widget's values based on their input fields
        $school = $atts[school];
        $department = $atts[department];
        $limit = 5;

        // Check if there is a cached output
        $cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

        if ( !is_array( $cache ) )
            $cache = array();

        if ( ! isset ( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset ( $cache[ $args['widget_id'] ] ) )
            return print $cache[ $args['widget_id'] ];

        extract( $args, EXTR_SKIP );

        $widget_string = $before_widget;

        //Feed Constructor
        $xml_string = "http://bcs.tandemcal.com/index.php?type=export&action=xml" . ($school == '' ? '' : '&schools=' . $school) . ($department == '' ? '' : '&departments=' . $department) . ($limit == '' ? '5' : '&limit=' . $limit);
        $xml = simplexml_load_file($xml_string);

        $events = $xml->xpath("/tfs_events/event");
        $events = json_decode(json_encode((array)$events),1);

        ob_start();

        include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );

        $widget_string .= ob_get_clean();
        $widget_string .= $after_widget;


        $cache[ $args['widget_id'] ] = $widget_string;

        wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

        print $widget_string;
    }
    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

    /*--------------------------------------------------*/
    /* Widget API Functions
    /*--------------------------------------------------*/

    /**
     * Outputs the content of the widget.
     *
     * @param array args  The array of form elements
     * @param array instance The current instance of the widget
     */
    public function widget( $args, $instance ) {


        // Check if there is a cached output
        $cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

        if ( !is_array( $cache ) )
            $cache = array();

        if ( ! isset ( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset ( $cache[ $args['widget_id'] ] ) )
            return print $cache[ $args['widget_id'] ];

        extract( $args, EXTR_SKIP );

        $widget_string = $before_widget;

        // Set widget's values based on their input fields
        $school = empty($instance['school']) ? '' : apply_filters('school', $instance['school']);
        $department = empty($instance['department']) ? '' : apply_filters('department', $instance['department']);
        $limit = empty($instance['limit']) ? '5' : apply_filters('limit', $instance['limit']);


        //Feed Constructor
        $xml_string = "http://bcs.tandemcal.com/index.php?type=export&action=xml" . ($school == '' ? '' : '&schools=' . $school) . ($department == '' ? '' : '&departments=' . $department) . ($limit == '' ? '' : '&limit=' . $limit);
        $xml = simplexml_load_file($xml_string);

        $events = $xml->xpath("/tfs_events/event");
        $events = json_decode(json_encode((array)$events),1);

        ob_start();

        include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );

        $widget_string .= ob_get_clean();
        $widget_string .= $after_widget;


        $cache[ $args['widget_id'] ] = $widget_string;

        wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

        print $widget_string;

    } // end widget


    public function flush_widget_cache()
    {
        wp_cache_delete( $this->get_widget_slug(), 'widget' );
    }

    /**
     * Processes the widget's options to be saved.
     *
     * @param array new_instance The new instance of values to be generated via the update.
     * @param array old_instance The previous instance of values before the update.
     */
    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        // Update widget's old values with the new, incoming values
        $instance['school'] = strip_tags( $new_instance['school'] );
        $instance['department'] = strip_tags( $new_instance['department'] );
        $instance['limit'] = strip_tags( $new_instance['limit'] );

        return $instance;

    } // end widget

    /**
     * Generates the administration form for the widget.
     *
     * @param array instance The array of keys and values for the widget.
     */
    public function form( $instance ) {

        // Define default values for variables
        $defaults =  array(
                'school' => '',
                'department' => '',
                'limit' => '5',
             );
        $instance = wp_parse_args((array) $instance, $defaults);

        // TODO: Store the values of the widget in their own variable

        // Display the admin form
        include( plugin_dir_path(__FILE__) . 'views/admin.php' );

    } // end form

    /*--------------------------------------------------*/
    /* Public Functions
    /*--------------------------------------------------*/

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain() {

        load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

    } // end widget_textdomain

    /**
     * Fired when the plugin is activated.
     *
     * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    /*public function activate( $network_wide ) {
        // TODO define activation functionality here
    } // end activate
    */
    /**
     * Fired when the plugin is deactivated.
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
     */
    /* public function deactivate( $network_wide ) {
        // TODO define deactivation functionality here
    } // end deactivate
    */
    /**
     * Registers and enqueues admin-specific styles.
     */
    public function register_admin_styles() {

        wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

    } // end register_admin_styles

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles() {

        wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

    } // end register_widget_styles

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts() {

        wp_enqueue_script( $this->get_widget_slug().'-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery') );

    } // end register_widget_scripts

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Tandem_Cal");' ) );
