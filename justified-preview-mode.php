<?php
/*
Plugin Name: Justified CMS - Preview Mode
Description: Check for a preview mode header and set the constant accordingly
Version: 0.0.1
Author: Error Studio
Author URI: http://errorstudio.co.uk
Plugin URI: http://errorstudio.co.uk
Text Domain: justified-preview-mode
*/

function set_preview_mode(){
    if(array_key_exists('HTTP_PREVIEW', $_SERVER)){
        define("JUSTIFIED_PREVIEW_MODE", $_SERVER['HTTP_PREVIEW']=="true" ? true : false);
    }else {
        define("JUSTIFIED_PREVIEW_MODE", false);
    }
}
add_action('plugins_loaded', 'set_preview_mode');

add_action('rest_prepare_post', function($response){
    global $post;

    /*
     * if the post in in anything but a published state, and we're NOT in preview mode,
     * we should send back a response which mimics the WP_Error auth failed response
     */

    if($post->post_status != 'publish' && !JUSTIFIED_PREVIEW_MODE){
        $response = new Custom_WP_Error('unauthorized', 'Authentication failed', array('status'=>403));
    }

    return $response;
});

/*
 * WP_REST_Posts_Controller is expecting a response object with a 'link_header' function, but in the case of
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
}

?>