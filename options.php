<?php
//this file will be require_once'd for the options page.

global $wpdb;
$tablename = $this->getTableName();

if (isset($_POST["submit"])){ //save POST data to database
	//print_r($_POST); //very useful when testing
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
				strpos($sliver, $mt_id) + 2 //after _, after id
				);
		$data_array[$mt_id][$attribute] = $data;
		
		//echo "id: $mt_id, attribute: $attribute, data: $data<br>";
		}
	$new_array = array();
	foreach ($data_array as $db_id => $data){
		$new_array[] = array_merge(array("id" => $db_id), $data);
		}
	$thingsChanged = 0;
	foreach ($new_array as $person => $data){
		$differences = array_diff($data, $technicians[$person]);
		//print_r($differences);
		foreach ($differences as $name => $value){
			if (gettype($value) == "string") {$datatype = '%s';}
			else {$datatype = '%d';}
			//echo "table: $tablename, data: array($name => $value), which: array(\"id\" => $person), datatype: $datatype, whichtype: %d" ;
			$succeed = $wpdb -> update($tablename, array($name => $value), array("id" => $data["id"]), $datatype, "%d");
			
			if ($succeed !== false){ //can be successful and return 0
				$thingsChanged += $succeed;
				}
			else {
				$this->adminNotice("Not saved", "error");
				break 2;
				}
			}
		if ($thingsChanged > 1){
			$this->adminNotice ("Saved. $thingsChanged fields changed!");
			}
		else if ($thingsChanged === 1){
			$this->adminNotice ("Saved. One field changed!");
			}
		else {
			$this->adminNotice ("No changes to save.", "error");
			}
		}
	}
	

?>
<div class="wrap">
<form method="post" action=""> 
<h2>Meet The Technicians</h2>

<?php
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
print_r($technicians); //very useful when testing

foreach($technicians as $person){
	if ($person[id] == "new"){$legend = "New Person";}
	else {$legend = $person[name];}
	echo '<fieldset class="mt_person"><legend>' . $legend . '</legend>' . "\n";
	
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
			echo '</textarea>'  . '</label>'. "\n";
			}
		else if ($attribute =="pic"){
			echo '<input type="text" name="' . $id .  
					'" id="' . $id . 
					'" value="' . $person[$attribute] . 
					'" maxlength = "' . $info[1] .
					'" size = "30" onfocusout="RefreshPic()"/></div>' . "\n";
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
	echo '</fieldset>' . "\n\n";
	}
?>

<p>
<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
<input type="reset" name="reset" id="reset" class="button button-secondary" value="Reset">
</p>
</form>
<?php $page = get_page_by_title('Meet The Technicians'); ?>
<a href="<?= get_page_link($page->ID) ?>" class="mt_viewpage">View Page</a>
</div>