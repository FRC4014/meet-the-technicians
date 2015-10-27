<?php
/**
Plugin Name: Meet the Technicians - IN DEVELOPMENT
Plugin URI:  https://github.com/FRC4014/meet-the-technicians
Description: Replaces a page called "Meet the Technicians" with a nicely designed grid of the HT^3 cast.  Also makes a widget with a 'random technician'.
Version:     1.0
Author:      Lucas LeVieux
Author URI:  http://lucaslevieux.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Class for MeetTechnicians plugin. Hooks and such need to be registered ouside 
 * the class.
 */
class MeetTechnicians {
	/**
	 * @var string arbitrary version number for the table.  when this changes, 
	 * the table is refreshed
	 */
	private $tableVersion = "20";
	
	/**
	 * Updates table, if nessesary
	 */
	function __construct() {
		if ($this->tableVersion != get_option( "meettechniciansversion" )){
			$this->createTable(); //updates if new tableversion 
			}
		}
	
	/**
	 * Filters the post_content.  Replaces the_content with Meet the Technicians
	 * content if the title is correct.  Includes page.php.
	 * 
	 * @param string $the_content the input from the_content, insde the_loop
	 * @return string $the_content filtered input, technicians content if applicable
	 */
	function pageFilter($the_content) {
		if (get_the_title() == "Meet the Technicians"){
			require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/page.php"); //seperate file for page code
			}
		return $the_content;
		}

	/**
	 * Creates or updates SQL table.
	 * 
	 * @global class $wpdb wordpress database manager
	 */
	function createTable() {
		global $wpdb;
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
		update_option( 'meettechniciansversion', $this->tableVersion );
		add_action( 'admin_notices', 'MeetTechnicians -> tableUpdatedNotice' );
		}
	
	/**
	 * @return string name of table
	 */
	private function getTableName() {
		return $GLOBALS[wpdb]->prefix . "meettechnicians";
		}
		
	/**
	 * @return array all the data from the database sorted by id
	 */
	private function getAll() {
		global $wpdb;
		$tablename = $this->getTableName();
		return $wpdb->get_results( "SELECT * FROM $tablename ORDER BY id ASC", ARRAY_A );
		}
		
	/**
	 * Makes admin notice about table updating.
	 */
	function tableUpdatedNotice() {
		admin_notice("Meet the Technicians: Table updated to version $this->tableversion");
		}

	/**
	 * Echos the notice in the admin area.  
	 * 
	 * @param string $notice message to send to user
	 * @param string $class type of notice. "updated", "error"
	 */
	private function adminNotice ($notice, $class = "updated"){
		?>
		<div class="<?= $class ?>">
			<p><?= $notice ?></p>
		</div>
		<?php
		}

	/**
	 * Adds options to settings menu. add_action'd at 'admin_menu'
	 */
	function addAdminMenu() {
		add_options_page( "Meet The Technicians", "Meet The Technicians", 'edit_posts', "meet-the-technicians.php", array ($this, "displayOptions")); 
		}

	/**
	 * Code for options page, called on associated page
	 */
	function displayOptions() {
		echo "<!-- displayOptions running -->";
		require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/options.php");
		}

	/**
	 * Registers and enqueues options.css on appropriate admin page.
	 * @param string $hook hook data passed by add_action
	 */
	function enqueueAdminStyle($hook) {
		if ( 'settings_page_meet-the-technicians' != $hook ) {
			return;
			}
		wp_register_style( 'mt_admin_style', plugin_dir_url( __FILE__ ) . 'options.css' );
		wp_enqueue_style( 'mt_admin_style' );
		}

	/**
	 * Registers and enqueues page.css on appropriate frontend page.
	 */
	function enqueuePageStyle() {
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
	function enqueueScript($hook) {
		if (get_the_title() != "Meet the Technicians" and $hook != 'settings_page_meet-the-technicians') {
			return;
			}
		wp_register_script( 'mt_script', plugin_dir_url( __FILE__ ) . 'script.js' );
		wp_enqueue_script( 'mt_script' );
		}
	} //end class

$meettechnicians = new MeetTechnicians();

add_action('admin_enqueue_scripts', array($meettechnicians, 'enqueueAdminStyle'));
add_action('wp_enqueue_scripts', array($meettechnicians, 'enqueuePageStyle'));
add_action('admin_enqueue_scripts', array($meettechnicians, 'enqueueScript'));
add_action('wp_enqueue_scripts', array($meettechnicians, 'enqueueScript'));
add_action('admin_menu', array($meettechnicians, 'addAdminMenu'));
add_filter('the_content', array($meettechnicians, 'pageFilter'));
register_activation_hook(__FILE__, array($meettechnicians, 'createTable'));
?>