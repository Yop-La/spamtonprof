<?php
namespace spamtonprof\stp_api;

class StpFormule implements \JsonSerializable
{

    protected $formule, $ref_formule, $matieres, $plans, $classes, $ref_product_stripe_test, $ref_product_stripe, $defaultPlan, $from_tool, $ref_prof, $prof;

    /**
     *
     * @return mixed
     */
    public function getProf()
    {
        return $this->prof;
    }

    /**
     *
     * @param mixed $prof
     */
    public function setProf($prof)
    {
        $this->prof = $prof;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    /**
     *
     * @param mixed $ref_prof
     */
    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getFrom_tool()
    {
        return $this->from_tool;
    }

    /**
     *
     * @param mixed $from_tool
     */
    public function setFrom_tool($from_tool)
    {
        $this->from_tool = $from_tool;
    }

    /**
     *
     * @return mixed
     */
    public function getDefaultPlan()
    {
        return $this->defaultPlan;
    }

    /**
     *
     * @param mixed $defaultPlan
     */
    public function setDefaultPlan($defaultPlan)
    {
        $this->defaultPlan = $defaultPlan;
    }

    /**
     *
     * @return mixed
     */
    public function getClasses()
    {
        if (gettype($this->classes) == "string") {
            $this->classes = pgArrayToArray($this->classes);
        }
        return $this->classes;
    }

    /**
     *
     * @param mixed $classes
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }

    public function __construct(array $donnees = array())

    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)

    {
        foreach ($donnees as $key => $value) {

            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {

                $this->$method($value);
            }
        }
    }

    /**
     *
     * @return mixed
     */
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     *
     * @param mixed $formule
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    /**
     *
     * @param mixed $ref_formule
     */
    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    public static function cast($formule): \spamtonprof\stp_api\StpFormule
    {
        return ($formule);
    }

    /**
     *
     * @return mixed
     */
    public function getMatieres()
    {
        if (gettype($this->matieres) == "string") {
            $this->matieres = pgArrayToArray($this->matieres);
        }
        return $this->matieres;
    }

    /**
     *
     * @param mixed $matieres
     */
    public function setMatieres($matieres)
    {
        $this->matieres = $matieres;
    }

    // pour retourner le custom field matieres de getresponse ( sequence essai )
    public function toGetResponse()
    {
        $matieres = $this->getMatieres();
        for ($i = 0; $i < count($matieres); $i ++) {
            $matiere = $matieres[$i];
            if ($matiere == "francais") {
                $matieres[$i] = "français";
            }
        }
        $matieres = implode("_", $matieres);
        return (utf8_encode($matieres));
    }

    /**
     *
     * @return mixed
     */
    public function getPlans()
    {
        return $this->plans;
    }

    /**
     *
     * @param mixed $plans
     */
    public function setPlans($plans)
    {
        $this->plans = $plans;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_product_stripe_test()
    {
        return $this->ref_product_stripe_test;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_product_stripe()
    {
        return $this->ref_product_stripe;
    }

    /**
     *
     * @param mixed $ref_product_stripe_test
     */
    public function setRef_product_stripe_test($ref_product_stripe_test)
    {
        $this->ref_product_stripe_test = $ref_product_stripe_test;
    }

    /**
     *
     * @param mixed $ref_product_stripe
     */
    public function setRef_product_stripe($ref_product_stripe)
    {
        $this->ref_product_stripe = $ref_product_stripe;
    }
    
    public function __toString()
    {
        $return = "Formule: " . $this->formule . "\n";
        return($return);
    }
}

