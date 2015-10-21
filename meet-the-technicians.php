<?php
/*
Plugin Name: Meet the Technicians - IN DEVELOPMENT
Plugin URI:  https://github.com/FRC4014/meet-the-technicians
Description: Replaces a page called "Meet the Technicians" with a nicely designed grid of the HT^3 cast.  Also makes a widget with a 'random technician'.
Version:     1.0
Author:      Lucas LeVieux
Author URI:  http://lucaslevieux.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
$meettechniciansversion = "1.1";
function meet_technicians($the_content) {
	if (get_the_title() == "Meet the Technicians"){
	  //code for the page goes here
		$the_content = '<p>hi</p>';
		}
  	return $the_content;
	}
function createTechniciansTable() {
	global $wpdb, $meettechniciansversion;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . "meettechnicians"; 
	
	$sql = "CREATE TABLE $table_name (
		name varchar(30) NOT NULL,
		grade smallint(2) NOT NULL,
		years smallint(2) NOT NULL,
		title varchar(40) NOT NULL,
		pic varchar(55) NOT NULL,
		description varchar(200),
		quote varchar(50)
		hobbies varchar(50),
		UNIQUE KEY name (name)
		) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	update_option( 'meettechniciansversion', $meettechniciansversion );
	}

function meet_technicians_menu() {
	add_options_page( "Meet The Technicians", "Meet The Technicians", 'manage_options', "meet-the-technicians.php", "meet_technicians_options"); 
	}
function meet_technicians_options() {
	//code for options page goes here
	require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/options.php");
	}

if ( $meettechniciansversion != get_option( "meettechniciansversion" ) ) createTechniciansTable();


add_action( 'admin_menu', 'meet_technicians_menu' );
add_filter( 'the_content', 'meet_technicians' );
register_activation_hook( __FILE__, 'createTechniciansTable' );
?>