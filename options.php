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
	echo '<div id="mt_person">' . $person->name . '</div>';
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		echo '<div id="mt_label">' . $attribute . '</div>';
		if ($attribute == "description"){ //for longer fields
			echo '<textarea name="' . 'mt_' . $person->id . "_" . $attribute . '">';
			echo $person->$attribute;
			echo '</textarea>' . "\n";
			}
		else {
			echo '<input type="text" name="' . 'mt_' . $person->id . "_" . $attribute . 
					'" value="' . $person->$attribute . 
					'" />' . "\n";
			}
		}
	}
?>

<input type="checkbox" name="save" style="display: none;" checked="checked">
<?php submit_button(); ?>
</form>
</div>

<?php
/* don"t mind me, I'm just a sql query!
insert into $meettechnicians
(name, grade, years, title, pic, description, quote, hobbies)
values ('Lucas LeVieux', '12', '2', 'Programming (Lead), Electrical, Twitter, Webmaster', 'http://dummy.url', 'does cool stuff', 'and the truth is, we don&apost know anything', 'butter');
*/
?>