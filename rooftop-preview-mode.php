<?php
/*
Plugin Name: Rooftop CMS - Preview Mode
Description: Check for a preview mode header and set the constant accordingly
Version: 0.0.1
Author: Error Studio
Author URI: http://errorstudio.co.uk
Plugin URI: http://errorstudio.co.uk
Text Domain: rooftop-preview-mode
*/

/**
 * If we have a HTTP_PREVIEW (preview: true) then we should set the global const ROOFTOP_PREVIEW_MODE to true.
 *
 * We use this
 */

add_action('muplugins_loaded', function(){
    if(array_key_exists('HTTP_PREVIEW', $_SERVER)){
        define("ROOFTOP_PREVIEW_MODE", $_SERVER['HTTP_PREVIEW']=="true" ? true : false);
    }else {
        define("ROOFTOP_PREVIEW_MODE", false);
    }
});

/**
 * if the post in in anything but a published state, and we're NOT in preview mode,
 * we should send back a response which mimics the WP_Error auth failed response
 */
add_action('rest_prepare_post', function($response){
    global $post;

    if($post->post_status != 'publish' && !ROOFTOP_PREVIEW_MODE){
        $response = new Custom_WP_Error('unauthorized', 'Authentication failed', array('status'=>403));
    }

    return $response;
});


/*
 * WP_REST_Posts_Controller is expecting a response object with a 'link_header' method, but in the case of
 * rendering an unpublished post when not in preview mode, we need to return a WP_Error, which we sub-class here
 * and stubb the link_header method ourselves
 */
class Custom_WP_Error extends WP_Error {
    public $data;
    public $headers;
    public $status;

    public function link_header( $rel, $link, $other = array() ) {
        $header = '<' . $link . '>; rel="' . $rel . '"';

        foreach ( $other as $key => $value ) {
            if ( 'title' === $key ) {
                $value = '"' . $value . '"';
            }
            $header .= '; ' . $key . '=' . $value;
        }
        return $this->header( 'Link', $header, false );
    }
    public function header( $key, $value, $replace = true ) {
        if ( $replace || ! isset( $this->headers[ $key ] ) ) {
            $this->headers[ $key ] = $value;
        } else {
            $this->headers[ $key ] .= ', ' . $value;
        }
    }

    public function get_matched_route(){
        return "";
    }
    public function get_matched_handler(){
        return null;
    }
    public function get_headers(){
        return $this->headers;
    }
    public function get_status(){
        return $this->status;
    }
    public function get_data(){
        return $this->data;
    }
}

?>