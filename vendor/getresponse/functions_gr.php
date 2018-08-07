<?php 


	function getTagNbMessage($nb_message)
	{
		$tagMessage;
	    if($nb_message == 0){
	    	$tagMessage = 'aZm2';
	    }else if($nb_message >= 1 && $nb_message <= 5){
	    	$tagMessage = 'aZ0M';
	    }else if($nb_message > 5 && $nb_message <= 10){
	    	$tagMessage = 'aZDY';
	    }else if($nb_message > 10){
	    	$tagMessage = 'aZeC';
	    }
	    return $tagMessage;
	}

	function getTagNbMessage_2($nb_message)
	{
		$tagMessage;
	    if($nb_message == 0){
	    	$tagMessage = 'aZS8';
	    }else if($nb_message >= 1 && $nb_message <= 5){
	    	$tagMessage = 'aZNe';
	    }else if($nb_message > 5 && $nb_message <= 10){
	    	$tagMessage = 'aZ1i';
	    }else if($nb_message > 10){
	    	$tagMessage = 'aZAs';
	    }
	    return $tagMessage;
	}

	function getParamsToPostNewsLetters($name, 
		$subject,
		$campaignId, 
		$date_envoi,
		 $fromFieldId, 
		 $HtmlContent,
		$selectedCampaigns = array(),
		$selectedSegments = array(),
		$selectedSuppressions = array(),
		$excludedCampaigns = array(),
		$excludedSegments = array(),
		$selectedContacts = array())
	{
		$selectedCampaigns = json_encode($selectedCampaigns);
		$selectedSegments = json_encode($selectedSegments);
		$selectedSuppressions = json_encode($selectedSuppressions);
		$excludedCampaigns = json_encode($excludedCampaigns);
		$excludedSegments = json_encode($excludedSegments);
		$selectedContacts = json_encode($selectedContacts);
		$params = 
		'{
		    "name": "'.$name.'",
		    "subject": '.json_encode($subject).',
		    "flags": [
		        "openrate"
		    ],
		    "editor": "custom",
		    "campaign": {
		        "campaignId": "'.$campaignId.'"
		    },
		    "sendOn": "'.$date_envoi.'",
		    "fromField": {
		        "fromFieldId": "'.$fromFieldId.'"
		    },
		    "content": {
		        "html": '.json_encode($HtmlContent).'
		    },
		    "sendSettings": {
		        "timeTravel": "false",
		        "perfectTiming": "true",
		        "selectedCampaigns": '.$selectedCampaigns.',
		        "selectedSegments": '.$selectedSegments.',
		        "selectedSuppressions": '.$selectedSuppressions.',
		        "excludedCampaigns": '.$excludedCampaigns.',
		        "excludedSegments": '.$excludedSegments.',
		        "selectedContacts": '.$selectedContacts.'
		    }
		}';
		echo($params);
		return(json_decode($params));
	}


	function getTabNbJoursInactivite($nb_jour_inactivite)
	{
		$tag_nb_jour_sans_rep = "";
		if($nb_jour_inactivite == 7){
			$tag_nb_jour_sans_rep = '
		        {
		            "tagId": "aijc"
		        },';
		}else if($nb_jour_inactivite == 10){
			$tag_nb_jour_sans_rep = '
		        {
		            "tagId": "aiZl"
		        },';
		}else{
			$tag_nb_jour_sans_rep="";
		}
		return($tag_nb_jour_sans_rep);
	}


	function getTabNbJoursInactivite_2($nb_jour_inactivite)
	{
		$tag_nb_jour_sans_rep = "";
		if($nb_jour_inactivite == 7){
			$tag_nb_jour_sans_rep = '
		        {
		            "tagId": "ai3i"
		        },';
		}else if($nb_jour_inactivite == 10){
			$tag_nb_jour_sans_rep = '
		        {
		            "tagId": "aits"
		        },';
		}else{
			$tag_nb_jour_sans_rep="";
		}
		return($tag_nb_jour_sans_rep);
	}
?>