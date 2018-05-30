<?php
namespace spamtonprof\stp_api;

use PDO;

class FactureManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function initFacturation($month, $year)
    // @todostp tester cette m�thode � la fin du mois de mai
    {
        $accountManager = new AccountManager();
        
        $accounts = $accountManager->getAccountToBill($month, $year, 100);
        
        foreach ($accounts as $account) {
            
            $paramsFacture;
            
            // $remise_interruption = $account -> getRemiseInterruption($month, $year) ;
            // @todostp faire le formulaire pour interruption, et faire cette m�thode
            
            $remise_demarrage = $account->getRemiseDemarrage($month, $year);
            
            $remise_arret = $account->getRemiseArret($month, $year);
            
            echo ("<br><br>Remise arret du compte n� " . $account->ref_compte() . ": " . $remise_arret . "<br><br>");
            
            // $paramsFacture = array(
            
            // $mois,
            
            // $annee,
            
            // $tarif_base,
            
            // $remise_interruption,
            
            // $remise_arret,
            
            // $remise_demarrage,
            
            // $tarif_final,
            
            // $paiement_recu,
            
            // $a_payer,
            
            // $ref_compte
            
            // );
        }
    }

    public function add(Abonnement $abonnement)
    {
        $q = $this->_db->prepare('INSERT INTO abonnement(ref_paypal_agreement,ref_stripe_subscription, ref_compte) VALUES(:ref_paypal_agreement, :ref_stripe_subscription, :ref_compte)');
        $q->bindValue(':ref_paypal_agreement', $abonnement->ref_paypal_agreement());
        $q->bindValue(':ref_compte', $abonnement->ref_compte());
        $q->bindValue(':ref_stripe_subscription', $abonnement->ref_stripe_subscription());
        $q->execute();
        
        $abonnement->setRef_abonnement($this->_db->lastInsertId());
        return ($abonnement);
    }

    public function count()
    {
        return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }

    /**
     * pour g�n�rer la table de facturation � importer dans vosfactures.fr
     * cette fonction g�n�re un fichier csv pour chaque �l�ve inscrit ou en essai
     * elle ne prend pas en compte les pauses - interruptions
     * il est encore n�cessaire de passer � la main derri�re pour faire quelques corrections
     * cela sert juste � d�panner S�bastien en attendant un syst�me plus automatis�e
     * ne g�re pas les �tudiants et les �l�ves d�sinscrits en cours de mois
     * attention les comptes � facturer doivent d'abord �tre enregistr�s dans tempo/invoice gr�ce � AccountSaverMg dans invoice
     */
    public function generateInvoicesCsv()
    {
        
        $accounts = array();
        $files = glob('../tempo/invoice/*');
        foreach($files as $file){
            if(is_file($file))
                $s = file_get_contents($file);
            
                $accountsPart = unserialize($s);
                $accounts = array_merge($accounts,$accountsPart);
                
        
        }
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $currentMonth = $now->format("n");
        $currentYear = $now->format("Y");
        
        $monthName = MONTH_NAMES[intval($currentMonth-1)];
        
        $nextMonth = $currentMonth + 1;
        
        $csvTableMathsPhysique = array(
            array_map("utf8_encode", array(
                "statut",
                "ref_compte",
                "buyer_name",
                "buyer_email",
                "title : Objet",
                "sell_date : Date de vente",
                "payment_to : Date limite de r�glement",
                "position.name : Produit - D�signation",
                "position.quantity : Produit - Qt�",
                "position.total_price_gross : Produit - Total TTC"
            ))
        );
        
        $csvTableFrancais = $csvTableMathsPhysique;
        
        $nbDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        
        $row = array();
        
        $csvNameMathsPhysique = "facture-maths-physique-du-$currentMonth-$currentYear.csv";
        $csvNameFrancais = "facture-francais-du$currentMonth-$currentYear.csv";
        
        foreach ($accounts as $account) {
            
            if ($account->attente_paiement() && ! $account->getTest_account() && ! $account->getLong_pay_plan()) {
                array_push($row, $account->statut());
                array_push($row, $account->ref_compte());
                array_push($row, "Mme/Mr " . $account->proche()->nom());
                array_push($row, $account->proche()->adresse_mail());
                array_push($row, "Cours en ligne du mois de " . $monthName);
                array_push($row, "$nbDaysInMonth/$currentMonth/$currentYear");
                array_push($row, "14/" . $nextMonth . "/" . $currentYear);
                array_push($row, "Cours en ligne de " . implode("-", $account->getMatieres()));
                array_push($row, 1);
                array_push($row, $account->tarif());
                
                if ($account->maths() || $account->physique()) {
                    array_push($csvTableMathsPhysique, $row);
                }
                
                if ($account->francais()) {
                    array_push($csvTableFrancais, $row);
                }
                
                $row = array();
            }
        }
        
        saveArrayAsCsv($csvTableMathsPhysique, "../tempo/invoice/" . $csvNameMathsPhysique);
        saveArrayAsCsv($csvTableFrancais, "../tempo/invoice/" . $csvNameFrancais);
        
        $slack = new \spamtonprof\slack\Slack();
        
        $url1 = plugins_url("spamtonprof/tempo/invoice/" . $csvNameMathsPhysique);
        $url2 = plugins_url("spamtonprof/tempo/invoice/" . $csvNameFrancais);
        
        echo ("chemin 1 : " . $url1 . "<br>");
        echo ("chemin 2 : " . $url2);
        
        $msgs = [
            $url1,
            $url2
        ];
        
        $slack->sendMessages($slack::Invoicing, $msgs);
    }
}