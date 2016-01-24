<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.

$people = $this->getAll();
$the_content = "";
shuffle($people); //randomize order

$the_content .=  '<div id="mt">';
foreach($people as $person){
	$the_content .=  '<div class="person" id="' . $person[id] . '" '
			. 'style="background-image: url(' . $person[pic] . ');">' . "\n";
	$the_content .= '<div class="text">' . "\n";
	foreach (array(name, title, grade, years, quote, hobbies, description) as $attribute){
		$label = null;
		if ($person[$attribute] == "") continue;
		else if ($attribute == "grade") $label = "Grade:";
		else if ($attribute == "years") $label = "Years on the team:";
		else if ($attribute == "title") $label = "Title/Responsibilities:";
		else if ($attribute == "quote") $label = "Favorite Quote:";
		else if ($attribute == "hobbies") $label = "Hobbies:";
		
		if (isset($label))
			$the_content .=  '<div class="label ' . $attribute . '">' .
					$label . '</div>' . "\n";
		$the_content .=  '<div class="field ' . $attribute . '">' .
					$person[$attribute] . '</div>' . "\n";
		
		}
	$the_content .= '</div></div>';
	}
?>