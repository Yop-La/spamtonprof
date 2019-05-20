<?php
namespace spamtonprof\stp_api;

/**
 *
 * @author alexg
 *        
 */
class LbcTexteGenerator
{

    protected $lbcBaseTexts, $nbTexteToGene = 100, $nbPara, $nbTexte, $pas, $nbTextMax, $geneTextesIndexs, // contient la composition des textes g�n�r�s. Un �lement de ce tableau est par exemple ( 4 , 1 , 3, 5 ) ie un
                                                                                                           // texte de 4 paras dont le 1er para vient du texte de base 4 , le second du texte de base 1 ,
    $texteIndexsSelected, $textesGenerated;

    public function __construct(\spamtonprof\stp_api\LbcTexteCat $lbcTexteCat)
    
    {
        $lbcBaseTextMg = new \spamtonprof\stp_api\LbcBaseTextMg();
        
        $this->lbcBaseTexts = array_values($lbcBaseTextMg->getTextsByParagraphs(array(
            "ref_text_cat" => $lbcTexteCat->getRef_texte_cat()
        )));
        
        $this->nbPara = count($this->lbcBaseTexts[0]);
        $this->nbTexte = count($this->lbcBaseTexts);
        
        $this->nbTextMax = pow($this->nbTexte, $this->nbPara);
        
        $this->pas = (int) ($this->nbTextMax / $this->nbTexteToGene);
        
        $this->geneTextesIndexs = [];
        
        $this->textesGenerated = [];
        
        $this->setTextIndexs();
    }

    /*
     * cette fonction g�n�re des indices des textes que l'on va utiliser pour r�cup�rer les textes parmi tous les textes possibles
     *
     * c'est gr�ce � cette fonction que l'on peut s�lectionner les textes
     * en fait , elle s�lectionne les textes avec un pas parmi tous les indices de textes possible tri�es
     * un texte est ici un tableau. Exemple : ( 1 , 1 , 2 , 4 ) est un texte de 4 paragraphes .
     * Le 1er paragraphe vient du texte de base n�1
     * Le 2eme para vient du texte de base n�1
     * Le 3eme para vient du texte de base n�2
     * Le 4eme para vient du texte de base n�4
     *
     */
    private function setTextIndexs()
    {
        $this->texteIndexsSelected = [];
        
        $index = 0;
        while (count($this->texteIndexsSelected) != $this->nbTexteToGene) {
            $indice1 = $index * $this->pas;
            $indice2 = $this->nbTextMax - 1 - $index * $this->pas;
            $this->texteIndexsSelected[] = $indice1;
            $this->texteIndexsSelected[] = $indice2;
            $index ++;
        }
    }

    public function generateTexts($withSymbols = false)
    {
        $this->setGeneTextesIndexs();
        ;
        for ($i = 0 ; $i < count($this->geneTextesIndexs); $i++) {
            
            
            
            $textIndexs = $this->geneTextesIndexs[$i];
            
            $texteGenerated = "";
            $paraIndex = 0;
            foreach ($textIndexs as $textIndex) {
                $para = $this->lbcBaseTexts[$textIndex - 1][$paraIndex];
                
                $texteGenerated = $texteGenerated  . $para . PHP_EOL . PHP_EOL;
                $paraIndex++;
            }
            
            if($withSymbols){
                
                $symbolLine = $this->generateLineSymboles();
                
                $texteGenerated = $symbolLine . PHP_EOL . PHP_EOL . PHP_EOL . $texteGenerated . PHP_EOL . PHP_EOL . PHP_EOL .$symbolLine;
                
            }
            
            $this->textesGenerated[] = $texteGenerated;
               
        }
        return ($this->textesGenerated);
    }
    
    
    
    
    private function generateLineSymboles(){
        
        $symboles = ['-','_','I','*',':','.','='];
        $nbSymbolesMax = 30;
        
        $line="";
        $indiceSymbole = rand (0, count($symboles) - 1);
        $symbole = $symboles[$indiceSymbole];
        $lineLength = rand (1, $nbSymbolesMax+1);
        for($i=0;$i<$lineLength;$i++){
            $line=$line . $symbole;
        }
        return $line;
    }
    

    private function nextTexteGene($nbPara, $texteGene, $nbTexte)
    {
        $retenue = 0;
        
        $add = true;
        
        for ($i = $nbPara; $i >= 1; $i --) {
            
            $texte = $texteGene[$i - 1] + $retenue;
            if ($add) {
                $texte = $texte + 1;
                $add = false;
            }
            
            if ($texte > $nbTexte) {
                $retenue = $texte - $nbTexte;
            } else {
                $retenue = 0;
            }
            
            if ($retenue == 0) { // si pas de retenue
                $texteGene[$i - 1] = $texte;
                break;
            } else {
                $texteGene[$i - 1] = 1;
            }
        }
        return ($texteGene);
    }

    // retourne le premier texte g�n�r�
    private function getFirstTextGenerated($nbPara)
    {
        $texteGene = [];
        for ($idPara = 1; $idPara <= $nbPara; $idPara ++) {
            $texteGene[] = 1;
        }
        return ($texteGene);
    }

    /**
     *
     * @param multitype: $parasIndex
     */
    private function setGeneTextesIndexs()
    {
        
        // on parcoure l'ensemble des textes possibles pour r�cup�rer uniquement ceux dont les indices sont dans $texteIndex
        for ($texteIndex = 0; $texteIndex <= $this->nbTextMax - 1; $texteIndex ++) {
            
            if ($texteIndex == 0) {
                $texteGene = $this->getFirstTextGenerated($this->nbPara);
            } else {
                $texteGene = $this->nextTexteGene($this->nbPara, $texteGene, $this->nbTexte);
            }
            
            if (in_array($texteIndex, $this->texteIndexsSelected)) {
                $index = array_search($texteIndex, $this->texteIndexsSelected);
                $this->geneTextesIndexs[$index] = $texteGene;
            }
        }
    }

    /**
     *
     * @return array
     */
    public function getLbcBaseTexts()
    {
        return $this->lbcBaseTexts;
    }

    /**
     *
     * @return number
     */
    public function getNbTexteToGene()
    {
        return $this->nbTexteToGene;
    }

    /**
     *
     * @return number
     */
    public function getNbPara()
    {
        return $this->nbPara;
    }

    /**
     *
     * @return number
     */
    public function getNbTexte()
    {
        return $this->nbTexte;
    }

    /**
     *
     * @return number
     */
    public function getPas()
    {
        return $this->pas;
    }

    /**
     *
     * @return number
     */
    public function getNbTextMax()
    {
        return $this->nbTextMax;
    }

    /**
     *
     * @return multitype:
     */
    public function getGeneTextesIndexs()
    {
        return $this->geneTextesIndexs;
    }

    /**
     *
     * @return multitype:
     */
    public function getTexteIndexsSelected()
    {
        return $this->texteIndexsSelected;
    }

    /**
     *
     * @return multitype:
     */
    public function getTextesGenerated()
    {
        return $this->textesGenerated;
    }

    /**
     *
     * @param array $lbcBaseTexts
     */
    public function setLbcBaseTexts($lbcBaseTexts)
    {
        $this->lbcBaseTexts = $lbcBaseTexts;
    }

    /**
     *
     * @param number $nbTexteToGene
     */
    public function setNbTexteToGene($nbTexteToGene)
    {
        $this->nbTexteToGene = $nbTexteToGene;
    }

    /**
     *
     * @param number $nbPara
     */
    public function setNbPara($nbPara)
    {
        $this->nbPara = $nbPara;
    }

    /**
     *
     * @param number $nbTexte
     */
    public function setNbTexte($nbTexte)
    {
        $this->nbTexte = $nbTexte;
    }

    /**
     *
     * @param number $pas
     */
    public function setPas($pas)
    {
        $this->pas = $pas;
    }

    /**
     *
     * @param number $nbTextMax
     */
    public function setNbTextMax($nbTextMax)
    {
        $this->nbTextMax = $nbTextMax;
    }

    /**
     *
     * @param multitype: $texteIndexsSelected
     */
    public function setTexteIndexsSelected($texteIndexsSelected)
    {
        $this->texteIndexsSelected = $texteIndexsSelected;
    }

    /**
     *
     * @param multitype: $textesGenerated
     */
    public function setTextesGenerated($textesGenerated)
    {
        $this->textesGenerated = $textesGenerated;
    }
}

