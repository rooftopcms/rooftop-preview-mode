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
    if( array_key_exists( 'HTTP_PREVIEW', $_SERVER ) ) {
        define( "ROOFTOP_PREVIEW_MODE", $_SERVER['HTTP_PREVIEW']=="true" ? true : false );
    }else {
        define( "ROOFTOP_PREVIEW_MODE", false );
    }
});

/**
 * if any of the post types are in anything but a published state and we're NOT in preview mode,
 * we should send back a response which mimics the WP_Error auth failed response
 *
 * note: we need to add this hook since we can't alter the query args on a single-resource endpoint (rest_post_query is only called on collections)
 */

add_action( 'rest_api_init', function() {
    $types = get_post_types( array( 'public' => true ) );

    add_filter( 'rooftop_published_statuses', function() {
        if( ROOFTOP_PREVIEW_MODE ) {
            return array( 'publish', 'draft', 'scheduled', 'pending' );
        }else {
            return array( 'publish' );
        }
    });

    foreach( $types as $key => $type ) {
        add_action( "rest_prepare_$type", function( $response ) {
            global $post;

            $valid_post_statuses = apply_filters( 'rooftop_published_statuses', array() );

            if( !ROOFTOP_PREVIEW_MODE ) {
                if( !in_array( $post->post_status, $valid_post_statuses ) ) {
                    $response = new Custom_WP_Error( 'unauthorized', 'Authentication failed', array( 'status'=>403 ) );
                }
            }else {
                /**
                 * If we're showing a preview version, then we need to access the appropriate revision of the current post
                 */
                $show_preview = isset($_SERVER['HTTP_PREVIEW_ID']);

                /**
                 * if we have a preparing_preview global, then we've already come through here and called
                 * rest_prepare_$type we can skip over this and return our response, as it will be the response that
                 * corresponds to the post we get back from wp_get_post_autosave( $post->ID );
                 */
                $previewing = isset($GLOBALS['preparing_preview']) ? $GLOBALS['preparing_preview'] : null;

                if( $show_preview && !$previewing ) {
//                    $preview = wp_get_post_autosave( $post->ID );
//
//                    if( ! $preview ) {
//                        return $response;
//                    }
//
//                    $method = "GET";
//                    $route  = "/wp/v2/pages/{$preview->ID}";
//
//                    $preview_request = new WP_REST_Request($method, $route);
//                    $preview_data = prepare_item_for_response( $preview, $post->post_type, $preview_request );
//                    $preview_response = rest_ensure_response( $preview_data );
//
//                    return $preview_response;
                }
            }

            return $response;
        });
    }
}, 10, 1);

function prepare_item_for_response( $preview_post, $type, $preview_request ) {
    $GLOBALS['preparing_preview'] = $preview_post->ID;

    setup_postdata( $preview_post );

    // Base fields for every post.
    $preview_data = array(
        'id'           => $preview_post->ID,
        'guid'         => array(
            /** This filter is documented in wp-includes/post-template.php */
            'rendered' => apply_filters( 'get_the_guid', $preview_post->guid ),
            'raw'      => $preview_post->guid,
        ),
        'password'     => $preview_post->post_password,
        'slug'         => $preview_post->post_name,
        'status'       => $preview_post->post_status,
        'type'         => $preview_post->post_type,
        'link'         => get_permalink( $preview_post->ID ),
    );

    // Wrap the data in a response object.
    $preview_response = rest_ensure_response( $preview_data );

    /**
     * Filter the post data for a response.
     *
     * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
     * prepared for the response.
     *
     * @param WP_REST_Response   $response   The response object.
     * @param WP_Post            $post       Post object.
     * @param WP_REST_Request    $request    Request object.
     */

    $prepared = apply_filters( 'rest_prepare_'.$type, $preview_response, $preview_post, $preview_request );

    return $prepared;
}

/*
 * WP_REST_Posts_Controller is expecting a response object with a 'link_header' method, but in the case of
 * rendering an unpublished post when not in preview mode, we need to return a WP_Error, which we sub-class here
 * and stub the link_header method ourselves
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