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

add_action( 'wp_head', 'dxlog_header_code' );
function dxlog_header_code ()
{
    echo "<link type='text/css' rel='stylesheet' href='/wp-content/plugins/wp-dxlog/style/style.css'>\n";
}

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
    $form = "";
    $search_term = $_POST['wp-dxlog-callsign-search'];

    if ( $search_term ) {
        $form .= "<p>$search_term has worked the following bands and modes:</p>";
        $form .= dxlog_qso_table( dxlog_search( $search_term ) );

        $form .= "Search again?";
    }

    $form .= '<form action="' . get_permalink() . '" method="post">';
    $form .= '<input name="wp-dxlog-callsign-search" type="text">';
    $form .= '<input value="Search" type="submit">';
    $form .= '</form>';

    return $form;
}
add_shortcode( 'dxlog-search', 'dxlog_shortcode_search' );

function dxlog_search ( $callsign )
{
    $toreturn = array();

    $result = dxlog_query(
        "SELECT * FROM qsos WHERE UPPER(callsign) = UPPER('%s')",
        array( $callsign )
    );

    while ( $row = @mysql_fetch_assoc( $result ) ) {
        $toreturn[$row['band']][$row['op_mode']] = 1;
    }

    mysql_free_result( $result );

    return $toreturn;
}

function dxlog_qso_table ( $results )
{
    $modes = array( 'SSB', 'Data', 'CW' );
    sort($modes);

    $bandmodes['160'] = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['80']  = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['40']  = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['20']  = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['17']  = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['15']  = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['10']  = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['6']   = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['4']   = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );
    $bandmodes['2']   = array( 'SSB' => 1, 'Data' => 1, 'CW' => 1 );

    $toreturn = "<table class='wp-dxlog-search-results'>";

    $toreturn .= "<tr><th>Band</th>";
    foreach ( $modes as $mode ) {
        $toreturn .= "<th>$mode</th>";
    }
    $toreturn .= "</tr>";

    foreach ( $bandmodes as $band => $modes_for_band ) {
        $toreturn .= "<tr><td>$band</td>";

        foreach ( $modes as $mode ) {
            if ( $results[$band][$mode] ) {
                $toreturn .= "<td>X</td>";
            }
            else {
                $toreturn .= "<td>&nbsp;</td>";
            }
        }

        $toreturn .= "</tr>";
    }

    $toreturn .= "</table>";

    return $toreturn;
}

function dxlog_query ( $query, $params )
{
    static $dbh;
    $escaped_params = array_map( 'mysql_real_escape_string', $params );

    if ( ! isset( $dbh ) ) {
        require_once( 'wp-content/plugins/wp-dxlog/dbinc.php' );
        /* FIXME Check scope of imported variables */

        $dbh = @mysql_connect( $hostName, $username, $password );

        if ( ! is_resource( $dbh ) ) {
            die( "wp-dxlog: Failed to connect to database!" );
        }
        else {
            @mysql_select_db( $databaseName, $dbh );
        }
    }

    $final_query = vsprintf( $query, $escaped_params );

    return @mysql_query( $final_query, $dbh );
}

?>
