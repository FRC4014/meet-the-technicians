<?php
/**
Plugin Name: Meet the Technicians - IN DEVELOPMENT
Plugin URI:  https://github.com/FRC4014/meet-the-technicians
Description: Makes a page with a nicely designed grid of the the members of your team.
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
	private $tableVersion = "27";
	
	/**
	 * @var string the name of the frontend AND backend pages
	 */
	private $featureName;
	
	/**
	 * @var string user-defined suffix for the name of the table to be used in the 
	 * database (tableName)
	 */
	private $tableSuffixName;
	
	/**
	 * @var string the name of the table to be used in the database, including
	 * both system-defined prefix and user-defined suffix
	 */
	private $tableName;
	
	/**
	 * Registers all the hooks, updates the table if nessesary, and defines:
	 *		featureName (according to parameter)
	 *		tableSuffixName (according to parameter)
	 *		tableName (according to tableSuffixName and WP database prefix)
	 * @param string $featureName the name of the frontend AND backend pages
	 * @param string $tableSuffixName suffix for the name of the table to be
	 * used in the database, should be short, no caps or spaces, and related to 
	 * featureName.
	 * @global object $wpdb wordpress database variable
	 */
	function __construct($featureName = "Meet The Technicians", 
			$tableSuffixName = "meettechnicians") {
		global $wpdb;
		
		add_action('admin_menu', array($this, 'addAdminMenu'));
		add_filter('admin_head', array($this, 'initializeJavascript'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminStyle'));
		add_action('wp_enqueue_scripts', array($this, 'enqueuePageStyle'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueScript'));
		add_action('wp_enqueue_scripts', array($this, 'enqueueScript'));
		add_filter('the_content', array($this, 'pageFilter'));
		register_activation_hook(__FILE__, array($this, 'activate'));
		
		$this->featureName = $featureName;
		$this->tableSuffixName = $tableSuffixName;
		$this->tableName = $wpdb->prefix . $this->tableSuffixName;
		
		if ($this->tableVersion != get_option($this->tableSuffixName . "version")){
			$this->createTable(); //updates if new tableversion 
			}
		}
	
	/**
	 * Filters the post_content.  Replaces the_content with Meet the Technicians
	 * content if the title is correct.  Includes page.php.
	 * 
	 * @param string $the_content the input from the_content, insde the_loop
	 * @return string $the_content filtered input, technicians content if applicable
	 * @global object $wpdb wordpress database manager
	 */
	function pageFilter($the_content) {
		if (get_the_title() == $this->featureName){
			require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/page.php"); //seperate file for page code
			}
		return $the_content;
		}

	/**
	 * Creates or updates SQL table.  Is run on plugin activation and when the
	 * table needs updating (from constructor).
	 * 
	 * @global object $wpdb wordpress database manager
	 */
	function createTable() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $this->tableName (
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
		update_option($this->tableSuffixName . "version", $this->tableVersion );
		add_action( 'admin_notices', array($this, 'tableUpdatedNotice'));
		}
		
	/**
	 * @return array all the data from the database sorted by id
	 * 
	 * @global object $wpdb wordpress database manager
	 */
	private function getAll() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM $this->tableName ORDER BY id ASC", ARRAY_A );
		return $results;
		}
		
	/**
	 * Makes admin notice about table updating using adminNotice
	 */
	function tableUpdatedNotice() {
		$this->adminNotice("$this->featureName: Table updated to version $this->tableVersion");
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
		add_pages_page($this->featureName, $this->featureName, 'edit_posts', $this->tableSuffixName, array ($this, "displayOptions")); 
		}

	/**
	 * Code for options page, called on associated page
	 * 
	 * @global object $wpdb wordpress database manager
	 */
	function displayOptions() {
		require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/options.php");
		}

	/**
	 * Registers and enqueues options.css on appropriate admin page.
	 * @param string $hook hook data passed by add_action
	 */
	function enqueueAdminStyle($hook) {
		if ($hook != 'pages_page_' . $this->tableSuffixName) {
			return;
			}
		wp_register_style( 'mt_admin_style', plugin_dir_url( __FILE__ ) . 'options.css' );
		wp_enqueue_style( 'mt_admin_style' );
		}

	/**
	 * Registers and enqueues page.css on appropriate frontend page.
	 */
	function enqueuePageStyle() {
		if (get_the_title() != $this->featureName) {
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
		if (get_the_title() != $this->featureName and $hook != 'pages_page_' . $this->tableSuffixName) {
			return;
			}
		wp_register_script( 'mt_script', plugin_dir_url( __FILE__ ) . 'script.js' );
		wp_enqueue_script( 'mt_script' );
		}
	
	/** 
	 * To be run when the program is first activated.  Runs createTable, and 
	 * initializes wordpress options for the page name and table suffix.
	 */
	function activate() {
		$this->createTable();
		add_option("MTfeaturename", "Meet the Technicians");
		add_option("MTtablesuffix", "meettechnicians");
	}
		
	/**
	 * Defines variables in javascript to be used in script.js. To be add 
	 * action'd in admin_head.
	 */
	function initializeJavascript() {
		?>
		<script>
			var pageName = "<?= $this->featureName ?>";
			var redirectName = "<?= $this->tableSuffixName ?>";
		</script>
		<?php
		}
	} //end class
	
	

$meettechnicians = new MeetTechnicians("Meet the Technicians", "meettechnicians");
?>
