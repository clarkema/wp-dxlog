<?php
/*
Plugin Name: DXLog
Plugin URI: http://www.clarkema.org/wp-dxlog/
Description: A basic Wordpress plugin to radio DX-pedition logs.
Version: 0.0.1
Author: Michael Clarke
*/

define( 'DXLOG_URLPATH', WP_CONTENT_URL
    . '/plugins/' . plugin_basename( dirname( __FILE__ ) ) . '/' );

add_action( 'admin_menu', 'dxlog_add_admin_menu' );
function dxlog_add_admin_menu ()
{
    add_menu_page( 'DXLog', 'DXLog', 'upload_files', 'wp-dxlog/admin/upload.php' );
}

function dxlog_shortcode_stats ( $attributes )
{
    return "These are some DXLog stats included in a page";

}
add_shortcode( 'dxlog-stats', 'dxlog_shortcode_stats' );

function dxlog_shortcode_search ( $attributes )
{
    $form = '<form action="' . get_permalink() . '" method="post">';
    $form .= '<input type="text">';
    $form .= '</form>';
    return $form;

}
add_shortcode( 'dxlog-search', 'dxlog_shortcode_search' );

?>
