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

?>