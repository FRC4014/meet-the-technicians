<?php
//this file will be require_once'd for the options page.
?>

<div class="wrap">
<form method="post" action="options.php"> 
<h2>Meet The Technicians</h2>

<?php
global $wpdb;
$tablename = $wpdb->prefix . "meettechnicians";
$technicians = $wpdb->get_results( "SELECT * FROM $tablename", OBJECT );
foreach($technicians as $person){
	echo '<div id="mt_person">' . $person->name . '</div>';
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		echo '<div id="mt_label">' . $attribute . '</div>';
		echo '<input type="text" name="' . 'mt_' . $person->name . "_" . $attribute . '" value="' . $person->$attribute . '" />' . "\n";
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