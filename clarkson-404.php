<?php
/*
 * Plugin Name: Custom 404 page
 * Version: 1.0.0
 * Description: A plugin to set a custom page as 404 for Clarkson
 * Author: Level Level
 * Author URI: http://www.level-level.com
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: clarkson-404
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Level Level
 * @since 0.1.0
 */

namespace Clarkson;

use Clarkson_Core\Objects;

class FourOFour {

    protected function __construct() {
        add_action( 'admin_init', array( $this, 'settings_field' ) );
        add_filter( 'clarkson_context_args', array( $this, 'add_404_object' ) );
        add_action( 'wp', array( $this, 'force_404' ) );
    }

    public function add_404_object( $objects ){

        if( !is_404() ) {
            return $objects;
        }

        $id = get_option( 'clarkson-page-for-404', false );

        if ( ! $id ) {
            return $objects;
        }

        $id = get_post( $id );
        global $post;
        $post = $id;

        $object_loader = Objects::get_instance();

        $page = $object_loader->get_object( $id );

        $objects['objects'] = array( $page );

        return $objects;
    }

    public function force_404() {
        $id = get_option( 'clarkson-page-for-404', false );

        if ( ! $id ) {
            return;
        }

        if ( is_page( $id ) ) {
            header( "Status: 404 Not Found" );
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
        }
    }

    /**
     * Create setting field page_for_404
     */
    public function settings_field() {

        register_setting( 'reading', 'clarkson-page-for-404' );

        add_settings_field( 'clarkson-page-for-404', __('404 Page', 'clarkson-404'), array( $this, 'options_reading_404' ), 'reading', 'default', array( 'label_for' => 'clarkson-page-for-404' ) );

        add_filter( 'display_post_states', array( $this, 'field_content' ), 10, 2 );
    }

    public function options_reading_404() {
        echo "<label for='clarkson-page-for-404'>";
        printf( __( 'Page: %s' ), wp_dropdown_pages( array( 'name' => 'clarkson-page-for-404',
            'echo'              => 0,
            'show_option_none'  => __( '&mdash; Select &mdash;', 'clarkson-404' ),
            'option_none_value' => '0',
            'selected'          => get_option( 'clarkson-page-for-404', false )
        ) ) );
        echo  '</label>';
    }

    /**
     * Add post state '404' in pages overview
     * @param $post_states
     * @param $post
     *
     * @return mixed
     */
    public function field_content( $post_states, $post ){

        $id = get_option('clarkson-page-for-404', false);

        if( !$id ) {
            return $post_states;
        }

        if( $post->ID === intval( $id ) ){
            $post_states['clarkson-page-for-404'] = __( '404', 'clarkson-404' );
        }

        return $post_states;
    }

    protected $instance = null;

    public static function get_instance() {
        static $instance = null;

        if ( null === $instance ) {
            $instance = new FourOFour();
        }

        return $instance;
    }
}

FourOFour::get_instance();
