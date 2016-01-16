<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.

$people = $this->getAll();
$the_content = "";
shuffle($people); //randomize order

foreach($people as $person){
	$the_content .=  '<div class="mt_person" id="mt_' . $person[id] . '">';
	foreach (array(name, grade, years, title, pic, description, quote, hobbies) as $attribute){
		if ($person[$attribute] == "") continue;
		$the_content .=  '<div class="mt_label ' . $attribute . '">' . $attribute . '</div>';
		$the_content .=  '<div class="mt_field mt_' . $attribute . '">' .
					$person[$attribute] . 
					'</div>' . "\n";
		
		}
	$the_content .= '</div>';
	}
?>