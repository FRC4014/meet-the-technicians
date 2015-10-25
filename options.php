<?php
//this file will be require_once'd for the options page.
?>

<div class="wrap">
<form method="post" action=""> 
<h2>Meet The Technicians</h2>

<?php
global $wpdb;
$tablename = $wpdb->prefix . "meettechnicians";
$technicians = $wpdb->get_results( "SELECT * FROM $tablename ORDER BY id ASC", OBJECT );
foreach($technicians as $person){
	echo '<div id="mt_person">' . $person->name . '</div>';
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		echo '<div id="mt_label_">' . $attribute . '</div>';
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