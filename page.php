<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.

$people = $this->getAll();
$the_content = "";
shuffle($people); //randomize order

foreach($people as $person){
	$the_content .=  '<div class="mt_person">' . $person->name . '</div>';
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		$the_content .=  '<div id="mt_label_">' . $attribute . '</div>';
		
		$the_content .=  '<div id="' . 'mt_' . $person->id . "_" . $attribute . '">' .
					$person->$attribute . 
					'</div>' . "\n";
		
		}
	}
?>