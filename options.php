<?php
//this file will be require_once'd for the options page.

//UPDATE DATABASE
global $wpdb;
if (isset($_POST["delete"]) and $_POST["delete"] != "-1"){ //delete data
	$succeed = $wpdb->delete($this->tableName, array('id' => $_POST["delete"]), array('%d'));
	if ($succeed === 1){ 
		$this->adminNotice ("Person deleted.");
		}
	else {
		$this->adminNotice("Person not deleted", "error");
		}
	}
else if (isset($_POST["save"])){ //update data
	$technicians = $this->getAll();
	$save_data = $_POST;
	unset($save_data["submit"]);
	$data_array = array();
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
					$data[title] == "" and
					$data[pic] == ""){
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
				$this->adminNotice ("New person \"$data[name]\" added!");
				$newperson = true;
				}
			else {
				$this->adminNotice("Not saved", "error");
				}
			continue; //don't allow it to add to $new_array
			}
		$last_id = $db_id;
		$new_array[] = array_merge(array("id" => $db_id), $data);
		}
	$thingsChanged = 0;
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
	if ($thingsChanged > 1){
			$this->adminNotice ("Saved. $thingsChanged fields changed!");
			}
		else if ($thingsChanged === 1){
			$this->adminNotice ("Saved. One field changed!");
			}
		else if (!$newperson){
			$this->adminNotice ("No changes to save.", "error");
			}
	}
//END UPADATE DATABASE
?>
<div class="wrap">
<form method="post" action="" id="mt_form"> 
<h2><?= $this->featureName ?></h2>

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
	pic => '', 
	description => '', 
	quote => '', 
	hobbies => ''
	));

foreach($technicians as $person){
	if ($person[id] == "new"){$legend = "New Person";}
	else {$legend = $person[name];}
	echo '<fieldset class="mt_person" id="person_' . $person[id] .
			'"><legend>' . $legend . '</legend>' . "\n";
	
	$fields = array( //array(display label, max length (see database), is required, width, size)
		name => array("Name", 30, true, 30),  
		grade => array("Grade", 2, true, 2), 
		years => array("Years on the Team", 2, true, 2), 
		title => array("Title/Responsibilities", 60, true, 30), 
		pic => array("URL to Picture", 100, true, 30), 
		description => array("Description", 200, false), 
		quote => array("Quote", 50, false, 30), 
		hobbies => array("Hobbies", 50, false, 30)
			);
	
	foreach ($fields as $attribute => $info){
		$id = 'mt_' . $person[id] . "_" . $attribute;
		echo '<div class="mt_field"><label class="mt_label">' . $info[0];
		if (!$info[2]) { //isn't required field
			echo ' <span class="mt_optional">optional</span>';
			}
		echo '</label>';
		
		if ($attribute == "description"){ //for longer (paragraph) fields
			echo '<textarea name="' . $id . '">';
			echo $person[$attribute];
			echo '</textarea>'  . '</div>'. "\n";
			}
		else if ($attribute =="pic"){
			echo '<input type="text" name="' . $id .  
					'" id="' . $id . 
					'" value="' . $person[$attribute] . 
					'" maxlength = "' . $info[1] .
					'" size = "30" ' .
					'onfocusout="' . "document.getElementById('mt_$person[id]_img').src = this.value;" .
					'"/></div>' . "\n";
			echo '<img src="' . $person[$attribute] . '" id="mt_' . $person[id] . '_img" width="240px" />';
			}
		else {
			if ($attribute == "grade" or $attribute == "years") $size = 2;
			else $size = 30;
			echo '<input type="text" name="' . $id . 
					'" id="' . $id . 
					'" value="' . $person[$attribute] . 
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
<p>
<input type="submit" name="save" class="button button-primary" value="Save Changes">
<input type="reset" name="reset" id="reset" class="button button-secondary" value="Reset">
</p>
<?php $page = get_page_by_title($this->featureName); ?>
</div>
</form>
</div>