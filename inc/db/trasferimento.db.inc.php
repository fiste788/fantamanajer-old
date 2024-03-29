<?php

require_once(TABLEDIR . 'Trasferimento.table.db.inc.php');

class Trasferimento extends TrasferimentoTable {

    public function save($parameters = NULL) {
        self::startTransaction();
        if (($id = parent::save()) != FALSE) {
            require_once(INCDBDIR . 'squadra.db.inc.php');
            require_once(INCDBDIR . 'formazione.db.inc.php');
            require_once(INCDBDIR . 'evento.db.inc.php');

            $idLega = $this->getUtente()->getIdLega();
            Squadra::unsetSquadraByIdGioc($this->getIdGiocatoreOld(), $idLega);
            Squadra::setSquadraByIdGioc($this->getIdGiocatoreNew(), $idLega, $this->getIdUtente());
            $formazione = Formazione::getFormazioneBySquadraAndGiornata($this->getIdUtente(), GIORNATA);
            if ($formazione != FALSE) {
                foreach($formazione->giocatori as $schieramento) {
                    if($this->getIdGiocatoreOld() == $schieramento->getIdGiocatore())
                        $giocatoriIds[] = $this->getIdGiocatoreNew();
                    else
                        $giocatoriIds[] = $schieramento->getIdGiocatore();
                }
                /*$schieramento = Schieramento::getByIdAndGiocatore($formazione->getId(), $this->getIdGiocatoreOld());
                if ($schieramento != FALSE) {
                    $schieramento->setIdGiocatore($this->getIdGiocatoreNew());
                    $schieramento->save();
                }*/
                if ($this->getIdGiocatoreOld() == $formazione->getIdCapitano())
                    $formazione->setIdCapitano($this->getIdGiocatoreNew());
                if ($this->getIdGiocatoreOld() == $formazione->getIdVCapitano())
                    $formazione->setIdVCapitano($this->getIdGiocatoreNew());
                if ($this->getIdGiocatoreOld() == $formazione->getIdVVCapitano())
                    $formazione->setIdVVCapitano($this->getIdGiocatoreNew());
                $titolari = array_splice($giocatoriIds,0, 11);
                $formazione->save(array('titolari'=>$titolari,'panchinari'=>$giocatoriIds));
            }
            $evento = new Evento();
            $evento->setTipo(Evento::TRASFERIMENTO);
            $evento->setIdUtente($this->getIdUtente());
            $evento->setIdLega($idLega);
            $evento->setIdExternal($id);
            if ($evento->save() != FALSE)
                return TRUE;
        }
        self::commit();
    }

    public function check($array, $message) {
        $post = (object) $array;
        $trasferimenti = self::getByField('idUtente', $post->idUtente);
        $numTrasferimenti = count($trasferimenti);

        if ($numTrasferimenti >= $_SESSION['datiLega']->numTrasferimenti) {
            $message->error("Hai raggiunto il limite di trasferimenti");
            return FALSE;
        }
        if (empty($post->idGiocatoreNew) || empty($post->idGiocatoreOld)) {
            $message->error("Non hai compilato correttamente tutti i campi");
            return FALSE;
        }
        $giocatoreAcquistato = Giocatore::getById($post->idGiocatoreNew);
        $giocatoreLasciato = Giocatore::getById($post->idGiocatoreOld);
        if ($giocatoreAcquistato->getRuolo() != $giocatoreLasciato->getRuolo()) {
            $message->error("I giocatori devono avere lo stesso ruolo");
            return FALSE;
        }
        return TRUE;
    }

    public static function getTrasferimentiByIdSquadra($idUtente, $idGiornata = 0) {
        $q = "SELECT trasferimento.*,t1.nome as nomeOld,t1.cognome as cognomeOld,t2.nome as nomeNew,t2.cognome as cognomeNew
				FROM giocatore t1 INNER JOIN (trasferimento INNER JOIN giocatore t2 ON trasferimento.idGiocatoreNew = t2.id) ON t1.id = trasferimento.idGiocatoreOld
				WHERE trasferimento.idUtente = '" . $idUtente . "' AND idGiornata > '" . $idGiornata . "'";
        $exe = mysql_query($q) or self::sqlError($q);
        FirePHP::getInstance()->log($q);
        $values = array();
        while ($row = mysql_fetch_object($exe, __CLASS__))
            $values[] = $row;
        return $values;
    }

}

?>
