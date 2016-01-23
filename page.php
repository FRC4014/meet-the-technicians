<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.

$people = $this->getAll();
$the_content = "";
shuffle($people); //randomize order

$the_content .=  '<div id="mt">';
foreach($people as $person){
	$the_content .=  '<div class="person" id="' . $person[id] . '" '
			. 'style="background-image: url(' . $person[pic] . ');">' . "\n";
	foreach (array(name, grade, years, title, description, quote, hobbies) as $attribute){
		$label = null;
		if ($person[$attribute] == "") continue;
		else if ($attribute == "grade") {
			if ($person[$attribute]  == 9) $person[$attribute] = "Nineth";
			if ($person[$attribute]  == 10) $person[$attribute] = "Tenth";
			if ($person[$attribute]  == 11) $person[$attribute] = "Eleventh";
			if ($person[$attribute]  == 12) $person[$attribute] = "Twelfth";
			$person[$attribute] .= " grade";
			}
		else if ($attribute == "years") {
			if ($person[$attribute] == 1)
				$person[$attribute] = "First year on the team";
			else
				$person[$attribute] .= " years on the team";
			}
		else if ($attribute == "quote") $label = "Favorite quote:";
		
		if (isset($label))
			$the_content .=  '<div class="label ' . $attribute . '">' .
					$label . '</div>' . "\n";
		$the_content .=  '<div class="field ' . $attribute . '">' .
					$person[$attribute] . '</div>' . "\n";
		
		}
	$the_content .= '</div>';
	}
?>