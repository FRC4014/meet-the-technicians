<?php
//this file will be require_once'd for the options page.

//UPDATE DATABASE
global $wpdb;
$thingsChanged = 0;

//DELETE PERSON
if (isset($_POST["delete"]) and $_POST["delete"] != "-1"){
	$succeed = $wpdb->delete($this->tableName, array('id' => $_POST["delete"]), array('%d'));
	if ($succeed === 1){ 
		$this->adminNotice ("Person deleted.");
		}
	else {
		$this->adminNotice("Person not deleted", "error");
		}
	}

//UPDATE PERSON
else if (isset($_POST["save"])){ 
	//initialize data
	$technicians = $this->getAll();
	$save_data = $_POST;
	unset($save_data["submit"]);
	$data_array = array();
	
	//orgainze data
	foreach ($save_data as $sliver => $data){
		$mt_id = substr($sliver, 
				strpos($sliver, "_") + 1, //after of first _
				strpos($sliver, "_", strpos($sliver, "_") + 1) -
				(strpos($sliver, "_") + 1) //before second _, after first _
				);
		$attribute = substr($sliver, 
				strpos($sliver, $mt_id) + strlen($mt_id) + 1 //after _, after id
				);
		$data_array[$mt_id][$attribute] = stripslashes($data);
		}
	$new_array = array();
	
	foreach ($data_array as $db_id => $data){
		if ($data[name] == "" and 
					$data[grade] == "" and
					$data[years] == "" and
					$data[title] == ""){
				continue; //none of new is filled out
				}
		if ($data[name] == "" or 
					$data[grade] == "" or
					$data[years] == "" or
					$data[title] == "" or
					$data[pic] == ""){
				$this->adminNotice("A required field is empty", "error");
				continue;
				}
		if ($db_id == "new"){
			$db_id = $last_id + 1; //should be in order of id, so last one will be greatest
			$succeed = $wpdb -> insert($this->tableName, 
					array_merge(array("id" => $db_id), $data), 
					array(
					'%d', //id
					'%s', //name
					'%d', //grade
					'%d', //years
					'%s', //title
					'%s', //pic
					'%s', //description
					'%s', //quote
					'%s'  //hobbies
					));
			if ($succeed !== false){ //can be successful and return 0
				$this->adminNotice ('New person "' . esc_attr($data[name]) . '" added!');
				$newperson = true;
				}
			else {
				$this->adminNotice("Not saved.  Maybe some of your answers are invalid or too big?", "error");
				}
			continue; //don't allow it to add to $new_array
			}
		$last_id = $db_id;
		$new_array[] = array_merge(array("id" => $db_id), $data);
		}
	foreach ($new_array as $person => $data){
		$differences = array_diff($data, $technicians[$person]);
		foreach ($differences as $name => $value){
			if (gettype($value) == "string") {$datatype = '%s';}
			else {$datatype = '%d';}
			$succeed = $wpdb -> update($this->tableName, array($name => $value), array("id" => $data["id"]), $datatype, "%d");
			
			if ($succeed !== false){ //can be successful and return 0
				$thingsChanged += $succeed;
				}
			else {
				$this->adminNotice("Not saved", "error");
				break 2;
				}
			}
		}
	}
	
//CHANGE FEATURE NAME
if (isset($_POST["MTfeaturename"]) and $_POST["MTfeaturename"] != get_option("MTfeaturename")){
	if ($this->changeFeatureName($_POST["MTfeaturename"]))
		$this->adminNotice ("Page name saved!");
	else
		$this->adminNotice ("Invalid page name", "error");
	}

//CHANGE TABLE SUFFIX
if (isset($_POST["MTtablesuffix"]) and $_POST["MTtablesuffix"] != get_option("MTtablesuffix")){
	if ($_POST["MTfeaturename"] != "") {
		update_option("MTtablesuffix", $_POST["MTtablesuffix"]);
		$this->tableSuffix = $_POST["MTtablesuffix"];
		$this->adminNotice ('Database name saved! (note: old data still stored in old database)  Redirecting in 3 seconds.');
		echo '<script>setTimeout(function(){window.location.reload();}, 3000);</script>';
		//3 second refresh for data
		}
	else
		$this->adminNotice ("Invalid short name", "error");
	}
	
//CHANGE POST STATUS
$status = $wpdb->get_results( "SELECT post_status FROM " . $wpdb->prefix . 
		"posts WHERE id='" . $this->pageId . "'", ARRAY_A );
$status = $status[0][post_status];
if (isset($_POST["MTstatus"]) and $_POST["MTstatus"] != $status) {
	$wpdb->update( $wpdb->posts, array( 'post_status' => $_POST["MTstatus"] ), array( 'ID' => $this->pageId ));
	$this->adminNotice ("Post status saved!");
	$status = $_POST["MTstatus"];
	}

//OUTPUT THINGS CHANGED
if ($thingsChanged > 1){
		$this->adminNotice ("Saved. $thingsChanged fields changed!");
		}
	else if ($thingsChanged === 1){
		$this->adminNotice ("Saved. One field changed!");
		}
//END UPADATE DATABASE
?>
<div class="wrap">
<form method="post" action="" id="mt_form" autocomplete="off"> 
<h2 id="main_title"><?= $this->featureName ?></h2>

<div id="mt_input_container">
<?php
//RETRIEVE FROM DATABASE
$technicians = $this->getAll();
array_push($technicians, array(
	id => "new",
	name => '',  
	grade => '', 
	years => '', 
	title => '', 
	pic => 'http://tophattechnicians.com/wp-content/uploads/2015/09/Hat-white.png', 
	description => '', 
	quote => '', 
	hobbies => ''
	));

//OUTPUT EACH BOX
foreach($technicians as $person){
	if ($person[id] == "new"){$legend = "New Person";}
	else {$legend = $person[name];}
	echo '<fieldset class="mt_person" id="person_' . $person[id] .
			'"><legend>' . esc_attr($legend) . '</legend>' . "\n";
	
	$fields = array( //array(display label, max length (see database), is required, width, size)
		name => array("Name", 30, true, 30),  
		grade => array("Grade", 2, true, 2), 
		years => array("Years on the Team", 2, true, 2), 
		title => array("Title/Responsibilities", 40, true, 30), 
		pic => array("Picture", 200, true, 30), 
		description => array("Description", 500, false), 
		quote => array("Quote", 100, false, 30), 
		hobbies => array("Hobbies", 100, false, 30)
			);
	
	foreach ($fields as $attribute => $info){
		$id = 'mt_' . $person[id] . "_" . $attribute;
		echo '<div class="mt_field"><label class="mt_label">' . $info[0];
		if (!$info[2]) { //isn't required field
			echo ' <span class="mt_optional">optional</span>';
			}
		echo '</label>';
		
		if ($attribute == "description"){ //for longer (paragraph) fields
			echo '<textarea name="' . $id . '" maxlength="' . $info[1] . '">';
			echo esc_attr($person[$attribute]);
			echo '</textarea>'  . '</div>'. "\n";
			}
		else if ($attribute == "pic"){
			echo '<input type="hidden" name="' . $id .  
					'" id="' . $id . 
					'" value="' . esc_attr($person[$attribute]) . 
					'" maxlength = "' . $info[1] .
					'" size = "30" ' .
					'onfocusout="' . "document.getElementById('mt_$person[id]_img').src = this.value;" .
					'"/></div>' . "\n";
			echo '<img src="' . $person[$attribute] . '" id="mt_' . $person[id] . '_img" width="240px" />';
                        
                        //make upload image iframe and button
                        $image_library_url = get_upload_iframe_src( 'image', null, 'type' );
                        $image_library_url = remove_query_arg( array('TB_iframe'), $image_library_url );
                        $image_library_url = add_query_arg( array('context' => 'meet-technicians', 'TB_iframe' => 1 ), $image_library_url );
                        echo '<a title="Upload Image for ' . $person[name] . '" href="' . esc_url( $image_library_url ) . 
                                '" class="button thickbox upload-image" person="'.$person[id].'">Upload New Picture</a>';
			}
		else {
			if ($attribute == "grade" or $attribute == "years") $size = 2;
			else $size = 30;
			echo '<input type="text" name="' . $id . 
					'" id="' . $id . 
					'" value="' . esc_attr($person[$attribute]) . 
					'" maxlength = "' . $info[1] .
					'" size = "' . $size .
					'"/></div>' . "\n";
			}
		}
	?>
	<input type="submit" class="button button-primary mt_save mt_person_button" name="save" value="Save">
	<a class="mt_delete mt_person_button" onclick="formSubmit(<?= $person[id] ?>);">Delete</a>
	<?php
	echo '</fieldset>' . "\n\n";
	}
//END RETRIEVE
?>
<input type="hidden" name="delete" id="mt_delete" value="-1">
<a onclick="document.getElementById('person_new').style.display = 'inline-block';document.getElementById('addnew').style.display = 'none';"><fieldset class="mt_person" id="addnew"><div id="plus">+</div><div id="text">add new person</div></fieldset></a>
<div id="bottom_buttons">
	<div id="mt_form_buttons">
		<input type="submit" name="save" class="button button-primary" value="Save Changes">
		<input type="reset" name="reset" id="reset" class="button button-secondary" value="Reset">
	</div>
	<div id="mt_general">
		<div>
			<label for="MTfeaturename">Page Name:</label>
			<input type="text" name="MTfeaturename" id="MTfeaturename" value="<?= esc_attr(get_option("MTfeaturename")) ?>">
		</div>
		<div>
			<label for="MTtablesuffix">Database/Slug Name:</label>
			<input type="text" name="MTtablesuffix" id="MTtablesuffix" value="<?= esc_attr(get_option("MTtablesuffix")) ?>">
		</div>
		<div>
			<select name="MTstatus">
				<option value="publish"<?php
				if ($status == "publish") echo " selected"
				?>>Published</option> 
				<option value="draft"<?php
				if ($status == "draft") echo " selected"
				?>>Private</option>
			</select>
			<?php if (isset($this->thePage) and ($this->thePage != null)) {
				echo '<a target="_blank" href="' . get_page_link($this->pageId)  . '">View page</a>';
				}
			?>
		</div>
	</div>
</div>
</div>
</form>
</div>