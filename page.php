<?php //file to output to the page using $the_content.  Included in meet-the-technicians.php.

$people = $this->getAll();
$the_content = '<center><em>Rollover or tap on a technician to see the full profile</em></center>';
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
	$the_content .= "</div></div>\n\n";
	}
        
//$the_content.= '<div class="person" id="join" style="background-image: url(http://tophattechnicians.com/wp-content/uploads/2015/09/Hat-white.png);">
//<div class="text">
//    <div class="field name">You?</div>
//    <div class="field description">You can be the next Top Hat Technician.</div>
//    <div class="label"> <!--- wrapped in label so it will be hidden when not hovered -->
//        <div class="call-to-action">
//            <a href="https://docs.google.com/forms/d/e/1FAIpQLSfKTtJCN1GhkdAJOt5NfMOjTUioTx6_-9mV9oDRuxCRa3ZdDg/viewform" class="blue button">Join the Team</a>
//        </div>
//    </div>
//</div></div>
//';
?>