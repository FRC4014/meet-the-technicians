<?php
//this file will be require_once'd for the options page.

global $wpdb;
$tablename = $wpdb->prefix . "meettechnicians";

if (isset($_POST["save"])){ //save POST data to database
	foreach ($_POST as $attribute => $data){
		$mt_id = substr($attribute, 
				strpos($attribute, "_") + 1, //after of first _
				strpos($attribute, "_", strpos($attribute, "_") + 1) -
					(strpos($attribute, "_") + 1) //before second _, after first _
				);
		echo $mt_id;
		}
	$sql = "INSERT INTO...";
	}
	

?>
<div class="wrap">
<form method="post" action=""> 
<h2>Meet The Technicians</h2>

<?php

$technicians = $wpdb->get_results( "SELECT * FROM $tablename ORDER BY id ASC", OBJECT );
foreach($technicians as $person){
	echo '<fieldset class="mt_person"><legend>' . $person->name . '</legend>' . "\n";
	$fields = array( //array(display label, max length (see database), is required, width, size)
		name => array("Name", 30, true, 30),  
		grade => array("Grade", 2, true, 2), 
		years => array("Years on the Team", 2, true, 2), 
		title => array("Title/Responsibilities", 60, true, 30), 
		pic => array("URL to Picture", 55, true, 30), 
		description => array("Description", 200, false), 
		quote => array("Quote", 50, false, 30), 
		hobbies => array("Hobbies", 50, false, 30)
			);
	foreach ($fields as $attribute => $info){
		$id = 'mt_' . $person->id . "_" . $attribute;
		echo '<label class="mt_label">' . $info[0];
		if (!$info[2]) { //isn't required field
			echo ' <span class="mt_optional">optional</span>';
			}
		if ($attribute == "description"){ //for longer fields
			echo '<textarea name="' . $id . '">';
			echo $person->$attribute;
			echo '</textarea>'  . '</label>'. "\n";
			}
		else {
			if ($attribute == "grade" or $attribute == "years") $size = 2;
			else $size = 30;
			echo '<input type="text" name="' . $id . 
					'" value="' . $person->$attribute . 
					'" maxlength = "' . $info[1] .
					'" size = "' . $size .
					'"/>' . '</label>' . "\n";
			}
		}
	echo '</fieldset>';
	}
?>

<input type="checkbox" name="save" style="display: none;" checked="checked">
<?php submit_button(); 
$page = get_page_by_title('Meet The Technicians');
?>
</form>
<a href="<?= get_page_link($page->ID) ?>" class="mt_viewpage">View Page</a>
</div>

<?php
/* don"t mind me, I'm just a sql query!
insert into $meettechnicians
(id, name, grade, years, title, pic, description, quote, hobbies)
values (1, 'Lucas LeVieux', '12', '2', 'Programming (Lead), Electrical, Twitter, Webmaster', 'http://dummy.url', 'does cool stuff', 'and the truth is, we don&apos;t know anything', 'butter');
*/
?>