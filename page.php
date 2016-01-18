<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.

$people = $this->getAll();
$the_content = "";
shuffle($people); //randomize order

$the_content .=  '<div id="mt">';
foreach($people as $person){
	$the_content .=  '<div class="person" id="' . $person[id] . '" '
			. 'style="background-image: url(' . $person[pic] . ');">' . "\n";
	foreach (array(name, grade, years, title, description, quote, hobbies) as $attribute){
		if ($person[$attribute] == "") continue;
		$the_content .=  '<div class="field ' . $attribute . '">' .
					$person[$attribute] . 
					'</div>' . "\n";
		
		}
	$the_content .= '</div>';
	}
?>