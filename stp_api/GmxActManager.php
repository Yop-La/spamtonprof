<?php
namespace spamtonprof\stp_api;

class GmxActManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(GmxAct $gmxAct)
    {
        $q = $this->_db->prepare('insert into gmx_act(password, mail) values( :password,:mail)');
        $q->bindValue(':password', $gmxAct->getPassword());
        $q->bindValue(':mail', $gmxAct->getMail());
        $q->execute();

        $gmxAct->setRef_gmx_act($this->_db->lastInsertId());

        return ($gmxAct);
    }

    public function updateHasRedirection(GmxAct $gmxAct)
    {
        $q = $this->_db->prepare('update gmx_act set has_redirection = :has_redirection where ref_gmx_act = :ref_gmx_act');
        $q->bindValue(':has_redirection', $gmxAct->getHas_redirection(),\PDO::PARAM_BOOL);
        $q->bindValue(':ref_gmx_act', $gmxAct->getRef_gmx_act());
        $q->execute();

        return ($gmxAct);
    }

    public function get($info)
    {
        $q = NULL;
        if (array_key_exists('mail', $info)) {
            $mail = $info['mail'];
            $q = $this->_db->prepare('select * from gmx_act where mail = :mail');
            $q->bindValue(":mail", $mail);
        }

        if (array_key_exists('ref_gmx_act', $info)) {
            $ref_gmx_act = $info['ref_gmx_act'];
            $q = $this->_db->prepare('select * from gmx_act where ref_gmx_act = :ref_gmx_act');
            $q->bindValue(":ref_gmx_act", $ref_gmx_act);
        }
        
        if (array_key_exists('ref_compte_lbc', $info)) {
            
            $ref_compte_lbc = $info['ref_compte_lbc'];
            $q = $this->_db->prepare('select * from gmx_act where ref_compte_lbc = :ref_compte_lbc');
            $q->bindValue(":ref_compte_lbc", $ref_compte_lbc);
        }
        
        if (in_array('virgin', $info)) {
            
            $q = $this->_db->prepare('select * from gmx_act where ref_compte_lbc is null');

        }
        
        
        
        
        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\GmxAct($data));
        } else {
            return (false);
        }
    }
}
