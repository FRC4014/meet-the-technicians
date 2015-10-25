<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.
?>

<link rel="stylesheet" type="text/css" href="<?= home_url( '/wp-content/plugins/meet-the-technicians/page.css' ) ?>" media="screen"/>

<?php
global $wpdb;
$tablename = $wpdb->prefix . "meettechnicians";
$technicians = $wpdb->get_results( "SELECT * FROM $tablename ORDER BY ID", OBJECT );
$the_content = "";
shuffle($technicians); //randomize order

foreach($technicians as $person){
	$the_content .=  '<div class="mt_person">' . $person->name . '</div>';
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		$the_content .=  '<div id="mt_label_">' . $attribute . '</div>';
		
		$the_content .=  '<div id="' . 'mt_' . $person->id . "_" . $attribute . '">' .
					$person->$attribute . 
					'</div>' . "\n";
		
		}
	}
?>