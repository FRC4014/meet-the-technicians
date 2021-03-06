<?php
/**
Plugin Name: Meet the Technicians
Plugin URI:  https://github.com/FRC4014/meet-the-technicians
Description: Makes a page with a nicely designed grid of the the members of your team.
Version:     2.0
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
	private $tableVersion = "31";
	
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
	 * @var integer the wordpress id for the applicable frontend page
	 */
	private $pageId;
	
	/**
	 * The data for the page that is defined in pageId.
	 * 
	 * Defined in the constructor if the viewed page is the frontend or backend
	 * page for Meet the Technicians.
	 * @var object the content of the page specified by $pageId.
	 * @see get_post()
	 * @link https://developer.wordpress.org/reference/functions/get_post/
	 */
	private $thePage;
	
	/**
	 * Registers all the hooks, updates the table if nessesary, and defines:
	 *		featureName (according to parameter)
	 *		tableSuffixName (according to parameter)
	 *		tableName (according to tableSuffixName and WP database prefix)
	 *		pageId (according to value stored in WP database)
	 *		thePage (according to pageId and database)
	 * @param string $featureName the name of the frontend AND backend pages
	 * @param string $tableSuffixName suffix for the name of the table to be
	 * used in the database, should be short, no caps or spaces, and related to 
	 * featureName.
	 * @global object $wpdb wordpress database variable
	 */
	function __construct($featureName = "Meet The Technicians", 
			$tableSuffixName = "meettechnicians") {
		global $wpdb;
		
		$this->featureName = $featureName;
		$this->tableSuffixName = $tableSuffixName;
		$this->tableName = $wpdb->prefix . $this->tableSuffixName;
		$this->pageId = get_option('MTpageid');
		
		add_action('admin_head', array($this, 'initializeAdminJavascript')); //initialize variables
		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScript')); //actually add script
                
                //filters and actions for 'add image' iframe on backend
                add_action('admin_menu', array($this, 'addImageFilters')); 
		
		if ($this->isOnFrontend() or $this->isOnBackend()){ //user is in either MT area
			$this->thePage = get_post($this->pageId);
			}
		
		add_action('wp_enqueue_scripts', array($this, 'enqueuePageStyle'));
		add_filter('the_content', array($this, 'pageFilter'));
		
		if ($this->isOnBackend()){
			add_action('admin_enqueue_scripts', array($this, 'enqueueAdminStyle'));
			if (!isset($this->thePage)){
				add_action( 'admin_notices', array($this, 'makePage'));
				//make a page if it doesn't already exist
				}
			else if ($this->thePage->post_name != get_option("MTtablesuffix")){
				$wpdb->update( $wpdb->posts, array( 'post_name' => get_option("MTtablesuffix") ), array( 'ID' => $this->pageId ));
				//changes slug to table suffix if not already
				}
			}
		if (is_admin()){ //user is in the admin area
			add_action('admin_menu', array($this, 'addAdminMenu'));
				//add sidebar option
			
			if ($this->tableVersion != get_option("MTversion") or 
					empty($wpdb->get_results("DESCRIBE $this->tableName", ARRAY_A))){
				$this->createTable();
				}
			}
		register_activation_hook(__FILE__, array($this, 'activate'));
			//run on plugin activation
		
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
		if (get_the_ID() == $this->pageId){
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
			title varchar(75) NOT NULL,
			pic varchar(200) NOT NULL,
			description varchar(500),
			quote varchar(100),
			hobbies varchar(100),
			PRIMARY KEY (id)
			) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
		update_option("MTversion", $this->tableVersion);
		add_action( 'admin_notices', array($this, 'tableUpdatedNotice'));
		}
		
	/**
	 * @return array all the data from the database sorted by id
	 * @global object $wpdb wordpress database manager
	 */
	private function getAll() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM $this->tableName ORDER BY id ASC", ARRAY_A );
		return $results;
		}
	
	/**
	 * Test if the page requested is the frontend of the Meet the Technicians
	 * page.  Only works after get_the_id is initialized.
	 * @return boolean true if the id of the current page matches the pageId
	 */
	private function isOnFrontend(){
		if (!is_admin()){
			return (get_the_id() == $this->pageId);
			}
		else {
			return false;
			}
		return false;
		}
	
	/**
	 * Test if the page requested is the backend of the Meet the Technicians
	 * page.
	 * @return boolean true if the id of the current scr3een page corresponds
	 * to the tableSuffixName.
	 */
	private function isOnBackend(){
		return is_admin() and
			strpos($_SERVER['QUERY_STRING'], "page=meettechnicians") !== false;
		//the tablesuffixname is not in the query string (which will happen on
		//the correct admin page)
		}
	
	function makePage() {
		if (!isset($this->thePage)) {
			return false;
			}
		$p = array();
        $p['post_title'] = $this->featureName;
        $p['post_content'] = '<h2 style="text-align: center;">This page is for the ' . $this->featureName . ' feature.  You should be automatically redirected to correct editor.</h2>
<p style="text-align: center;">If this text shows up on the webpage, either the plugin is disabled or has some problems.</p>';
        $p['post_status'] = 'draft';
        $p['post_type'] = 'page';
        $p['comment_status'] = 'closed';
        $p['ping_status'] = 'closed';
        $p['post_category'] = array(1); // the default 'Uncategorized'

        // Insert the post into the database
        $this->pageId = wp_insert_post( $p );
		if ($this->pageId === false) {
			if (is_wp_error($this->pageId)) {
				$error_string = $this->pageId->get_error_message();
				}
			$this->adminNotice("Page \"" . $this->featureName . "\" not created." . $error_string, "error");
			}
		else {
			$this->adminNotice("Page \"" . $this->featureName . "\" created! Change the name in the page settings.");
			update_option('MTpageid', $this->pageId);
			}
		
		}
	
	/**
	 * Changes the value of featureName in all applicable places.
	 *		changes instance variable
	 *		updates WP database
	 *		changes post_title of applicable page
	 * @param string $newName the new featureName
	 * @return boolean false on invalid name, true otherwise
	 */
	public function changeFeatureName ($newName) {
		if ($newName == $this->featureName or $newName == "") return false;
		
		$this->featureName = $newName; //change instance variable for current excecution
		
		update_option("MTfeaturename", $newName); //update WP database
		
		$p = array();
		$p['id'] = $this->pageId;
		$p['post_title'] = $newName;
		wp_update_post($p);
		return true;
		}
	
	/**
	 * Changes the value of Table Suffix in all applicable places.
	 *		changes instance variable
	 *		updates WP database
	 * @param string $newName the new tableSuffixName
	 * @return boolean false on invalid name, true otherwise
	 */
	public function changeTableSuffixName ($newName){
		if ($newName == $this->tableSuffixName or $newName == "") return false;
		
		$this->tableSuffixName = $newName; //change instance variable for current excecution
		
		update_option("MTtablesuffix", $newName); //update WP database

		return true;
		}
		
	/**
	 * Makes admin notice about table updating using adminNotice
	 */
	public function tableUpdatedNotice() {
		$this->adminNotice("$this->featureName: Table updated to version $this->tableVersion");
		}

	/**
	 * Echos the notice in the admin area.  
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
		add_pages_page($this->featureName, $this->featureName, 'edit_posts', "meettechnicians", array ($this, "displayOptions")); 
		}

	/**
	 * Code for options page, called on associated page.
	 * 
	 * @global object $wpdb wordpress database manager
	 */
	function displayOptions() {
		require_once(ABSPATH . "wp-content/plugins/meet-the-technicians/options.php");
		}

	/**
	 * Registers and enqueues options.css (custom stylesheet) and thickbox
         * ("add media" stylesheet) on appropriate admin page.
	 * @param string $hook hook data passed by add_action
	 */
	function enqueueAdminStyle($hook) {
		if (!$this->isOnBackend()) {
			return;
			}
		wp_register_style( 'mt_admin_style', plugin_dir_url( __FILE__ ) . 'options.css' );
		wp_enqueue_style( 'mt_admin_style' );
                wp_enqueue_style('thickbox');
		}

	/**
	 * Registers and enqueues page.css on appropriate frontend page.
	 */
	function enqueuePageStyle() {
		if (!$this->isOnFrontend()) {
			return;
			}
		wp_register_style( 'mt_page_style', plugin_dir_url( __FILE__ ) . 'page.css' );
		wp_enqueue_style( 'mt_page_style' );
		}

	/**
	 * Registers and enqueues options.js as well as thickbox ("add media" 
         * script), to be done on an appropriate admin page.
	 * @param string $hook hook data passed by add_action
	 */
	function enqueueAdminScript($hook) {
		wp_register_script( 'mt_script', plugin_dir_url( __FILE__ ) . 'options.js' );
		wp_enqueue_script( 'mt_script' );
                wp_enqueue_script('thickbox');
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
	function initializeAdminJavascript() {
		?>
		<script>
			var pageName = "<?= $this->featureName ?>";
			var redirectName = "<?= $this->tableSuffixName ?>";
		</script>
		<?php
                }
        
        /**
         * Filter to add custom button to the iframe on the image uploader on 
         * the backend.
         * @param type $form_fields
         * @param type $post
         * @return string
         */
        function uploadImageButtonFilter($form_fields, $post) {
                $send = "<input type='submit' class='button' name='send[$post->ID]' value='" . esc_attr__( 'Use Image' ) . "' />";  
                $form_fields['buttons'] = array('tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$send</td></tr>\n");
                $form_fields['context'] = array( 'input' => 'hidden', 'value' => 'meet-technicians' );
                return $form_fields;
                }
        
        /**
         * Filter to change the URL via javascript when the image is uploaded.
         * Added by addImageFilters, which is excecuted in admin_menu.
         * @param type $html
         * @param type $send_id
         * @param type $attachment
         */
        function uploadImageAction($html, $send_id, $attachment) {
                ?>
                <script type="text/javascript">
                    var personid = window.top.document.getElementsByClassName('uploading')[0].getAttribute("person");
                    console.log(personid);
                    window.top.document.getElementById('mt_' + personid + '_pic').value = '<?= $attachment['url'] ?>';
                    window.top.document.getElementById('mt_' + personid + '_img').src = '<?= $attachment['url'] ?>';
                    window.top.tb_remove();
                </script>
                <?php
                }
                
        /**
         * To be run in admin_menu.  Adds uploadImageButtonFilter and
         * uploadImageAction, if it's in the customize upload iframe from the 
         * mt backend
         */
        function addImageFilters($hook){
                if ($this->check_upload_image_context('meet-technicians')) { //if in iframe
                        add_filter('attachment_fields_to_edit', array($this, 'uploadImageButtonFilter'), 20, 2);
                        add_filter('media_send_to_editor', array($this, 'uploadImageAction'), 10, 3);
                        }
                }
                
        function add_my_context_to_url($url, $type) {
            if ($type != 'image') return $url;
            if (isset($_REQUEST['context'])) {
                $url = add_query_arg('context', $_REQUEST['context'], $url);
                }
            return $url;    
            }

        function check_upload_image_context($context) {
            if (isset($_REQUEST['context']) && $_REQUEST['context'] == $context) {
                add_filter('media_upload_form_url', array($this, 'add_my_context_to_url'), 10, 2);
                return TRUE;
                } 
            return FALSE;
            }
	} //end class
	
//INSTANTIATE THE CLASS
if (get_option("MTfeaturename") !== false && get_option("MTtablesuffix") !== false){
	//wordpress options are defined (as they should be, per install)
	$meettechnicians = new MeetTechnicians(get_option("MTfeaturename"), get_option("MTtablesuffix"));
	}
else {
	$meettechnicians = new MeetTechnicians();
	}

?>
