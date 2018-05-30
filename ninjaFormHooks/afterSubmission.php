<?php
add_action('inscription_essai_eleve_parent_submit_data', 'doTrialSubscription');

function doTrialSubscription($form_data)
{
    
    to_log_slack(array("str1" => "salut"));
    
    $prenomEleve = $fields["356"];
    $nomEleve = $fields["357"];
    $classe = $fields["358"];
    $emailEleve= $fields["359"];
    $telephoneEleve = $fields["360"];
    $chapitreMaths = $fields["361"];
    $difficulteMaths = $fields["362"];
    $matieres = $fields["363"];
    $proche = $fields["364"];
    $noteMaths = $fields["365"];
    
    $html1 = $fields["366"];
    $html2 = $fields["367"];
    $html3 = $fields["368"];
    $html4 = $fields["374"];
    $html5 = $fields["377"];
    $html6 = $fields["692"];
    
    $chapitrePhysique = $fields["369"];
    $difficultePhysique = $fields["370"];
    $notePhysique = $fields["371"];
    $prenomParent = $fields["372"];
    $emailParent = $fields["373"];
    
    $nomProche = $fields["375"];
    $telephoneParent = $fields["376"];
    
    $cgv = $fields["378"];
    $remarque = $fields["379"];
    $submitButton = $fields["380"];
    $tarif = $fields["509"];
    $codeParrain = $fields["648"];
    
    $s = serialize($form_data);
    
    file_put_contents(get_home_path() . "/object-test2", $s);
    
      
}


