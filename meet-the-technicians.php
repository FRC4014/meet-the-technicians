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

$tableversion = "20"; //arbitrary, change when table structure changes

/**
 * Filters the post_content.  Replaces the_content with Meet the Technicians
 * content if the title is correct.  Includes page.php.
 * 
 * @param string $the_content the input from the_content, insde the_loop
 * @return string $the_content filtered input, technicians content if applicable
 */
function meet_technicians($the_content) {
	if (get_the_title() == "Meet the Technicians"){
		require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/page.php"); //seperate file for page code
		}
  	return $the_content;
	}
	
/**
 * Creates or updates SQL table.
 * 
 * @global class $wpdb wordpress database manager
 * @global string $tableversion arbitrary version set in this fule
 */
function createTechniciansTable() {
	global $wpdb, $tableversion;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . "meettechnicians"; 
	
	$sql = "CREATE TABLE $table_name (
		id smallint(5) NOT NULL,
		name varchar(30) NOT NULL,
		grade smallint(2) NOT NULL,
		years smallint(2) NOT NULL,
		title varchar(60) NOT NULL,
		pic varchar(100) NOT NULL,
		description varchar(200),
		quote varchar(50),
		hobbies varchar(50),
		PRIMARY KEY (id)
		) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	dbDelta( $sql );
	update_option( 'meettechniciansversion', $tableversion );
	add_action( 'admin_notices', 'table_updated_notice' ); //display a notice
	}
function table_updated_notice() {
	admin_notice("Meet the Technicians: Table updated to version $GLOBALS[tableversion]");
	}
function saved_notice() {
	admin_notice("Saved");
	}
function notsaved_notice() {
	admin_notice("Not saved", "error");
	}

/**
 * Echos the notice in the admin area.  
 * 
 * @param string $notice message to send to user
 * @param string $class type of notice. "updated", "error"
 */
function admin_notice ($notice, $class = "updated"){
	?>
    <div class="<?= $class ?>">
        <p><?= $notice ?></p>
    </div>
    <?php
	}

/**
 * Adds options to settings menu. add_action'd at 'admin_menu'
 */
function meet_technicians_menu() {
	add_options_page( "Meet The Technicians", "Meet The Technicians", 'manage_options', "meet-the-technicians.php", "meet_technicians_options"); 
	}
	
/**
 * Code for options page, called on associated page
 */
function meet_technicians_options() {
	require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/options.php");
	}

/**
 * Registers and enqueues options.css on appropriate admin page.
 * @param string $hook hook data passed by add_action
 */
function mt_enqueue_admin_style($hook) {
    if ( 'settings_page_meet-the-technicians' != $hook ) {
        return;
		}
    wp_register_style( 'mt_admin_style', plugin_dir_url( __FILE__ ) . 'options.css' );
	wp_enqueue_style( 'mt_admin_style' );
	}
	
/**
 * Registers and enqueues page.css on appropriate frontend page.
 */
function mt_enqueue_page_style() {
    if (get_the_title() != "Meet the Technicians") {
        return;
		}
    wp_register_style( 'mt_page_style', plugin_dir_url( __FILE__ ) . 'page.css' );
	wp_enqueue_style( 'mt_page_style' );
	}

/**
 * Registers and enqueues script.js on appropriate admin and frontend pages.
 * @param string $hook hook data passed by add_action
 */
function mt_enqueue_script($hook) {
	if (get_the_title() != "Meet the Technicians" and $hook != 'settings_page_meet-the-technicians') {
        return;
		}
	wp_register_script( 'mt_script', plugin_dir_url( __FILE__ ) . 'script.js' );
	wp_enqueue_script( 'mt_script' );
	}

if ( $tableversion != get_option( "meettechniciansversion" ) ) createTechniciansTable();

add_action('admin_enqueue_scripts', 'mt_enqueue_admin_style');
add_action('wp_enqueue_scripts', 'mt_enqueue_page_style');
add_action('admin_enqueue_scripts', 'mt_enqueue_script');
add_action('wp_enqueue_scripts', 'mt_enqueue_script');
add_action('admin_menu', 'meet_technicians_menu' );
add_filter('the_content', 'meet_technicians' );
register_activation_hook(__FILE__, 'createTechniciansTable' );
?>