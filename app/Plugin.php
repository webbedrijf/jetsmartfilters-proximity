<?php

namespace JetSmartFiltersProximity;

use Carbon_Fields\Carbon_Fields;


class Plugin
{

    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'loadCarbon']);

        Shortcode::register();
        Query::register();
    }


    function loadCarbon()
    {
        Carbon_Fields::boot();
    }

}

//debug Jet Smart Filters, AJAX submit type only
class JSF_Filters_Debug {

    private static $instance = null;

    public $debug_info = array();

    public $vars_in_request = array();

    public $print = true;

    public static function instance() {
        if ( empty( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {
        add_action( 'jet-smart-filters/init', array( $this, 'maybe_add_debug_code' ) );
    }

    public function add_data( $key, $data ) {
        $this->debug_info[ $key ] = $data;
    }

    public function maybe_add_debug_code() {

        if ( ! empty( $_GET['jsf_force_debug'] ) || ( wp_doing_ajax() || isset( $_GET['jsf_ajax'] ) ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'jet_smart_filters' ) {

            add_filter( 'jet-smart-filters/query/request', array( $this, 'jsf_request' ), 9999999, 2 );

            add_filter( 'jet-smart-filters/query/vars', array( $this, 'jsf_query_vars' ), 999999 );

            add_filter( 'jet-smart-filters/query/final-query', array( $this, 'jsf_final_query' ), 999999 );

            add_action( 'jet-engine/query-builder/query/before-get-items', array( $this, 'je_query_before_get_items' ), 999999 );

            add_action( 'shutdown', array( $this, 'print_debug_on_error' ) );

            //comment out if you do not need filters to add info to filter response data
            add_filter( 'jet-smart-filters/render/ajax/data', array( $this, 'add_debug_to_response' ), 999999 );

        }

    }

    public function add_debug_to_response( $data ) {

        $data['debug']['fired'] = array_keys( $this->debug_info );
        $data['debug']['info']  = $this->debug_info;

        $this->print = false;

        return $data;

    }

    public function jsf_request( $request, $query ) {

        $data = $request['query'] ?? array();

        if ( ! empty( $data ) ) {

            foreach( $query->query_vars() as $var ) {
                array_walk( $data, function( $value, $key ) use ( $var ) {
                    if ( strpos( $key, $var ) ) {
                        $this->vars_in_request[] = $var;
                    }
                } );
            }

        }

        $this->debug_info['jsf_request']['vars_in_request'] = $this->vars_in_request;
        $this->debug_info['jsf_request']['request']         = $request;

        return $request;

    }

    public function jsf_query_vars( $query_vars ) {

        $this->query_vars = $query_vars;

        $this->debug_info['jsf_query_vars'] = $query_vars;

        return $query_vars;

    }

    public function jsf_final_query( $query ) {

        $this->debug_info['jsf_final_query'] = $query;

        return $query;

    }

    public function je_query_before_get_items( $query ) {

        if ( ! empty( $this->debug_info ) ) {
            $this->debug_info['je_query_before_get_items'][] = $query->final_query;
        }

    }

    public function print_debug_on_error() {

        $errors_to_print = array(
            E_ERROR             => 'E_ERROR',
            E_PARSE             => 'E_PARSE',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        );

        $error = error_get_last();

        $is_fatal = isset( $errors_to_print[ $error['type'] ] );

        if ( ! empty( $this->debug_info ) && ( $is_fatal || $this->print ) ) {
            $error['type'] = $errors_to_print[ $error['type'] ];
            echo '<pre>';
            if ( $is_fatal ) {
                var_dump( array( 'error' => $error ) );
            }
            $fired = array_keys( $this->debug_info );
            var_dump( array( 'fired' => $fired ) );
            var_dump( $this->debug_info );
            echo '</pre>';
        }

    }

}

//JSF_Filters_Debug::instance();

function jsf_filters_debug() {
    return JSF_Filters_Debug::instance();
}
