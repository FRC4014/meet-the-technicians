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
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		$id = 'mt_' . $person->id . "_" . $attribute;
		echo '<label class="mt_label">' . $attribute;
		if ($attribute == "description"){ //for longer fields
			echo '<textarea name="' . $id . '">';
			echo $person->$attribute;
			echo '</textarea>'  . '</label>'. "\n";
			}
		else {
			echo '<input type="text" name="' . $id . 
					'" value="' . $person->$attribute . 
					'" />' . '</label>' . "\n";
			}
		}
	echo '</fieldset>';
	}
?>

<input type="checkbox" name="save" style="display: none;" checked="checked">
<?php submit_button(); ?>
</form>
</div>

<?php
/* don"t mind me, I'm just a sql query!
insert into $meettechnicians
(id, name, grade, years, title, pic, description, quote, hobbies)
values (1, 'Lucas LeVieux', '12', '2', 'Programming (Lead), Electrical, Twitter, Webmaster', 'http://dummy.url', 'does cool stuff', 'and the truth is, we don&apos;t know anything', 'butter');
*/
?>