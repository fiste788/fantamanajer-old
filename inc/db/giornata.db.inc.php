<?php

require_once(TABLEDIR . 'Giornata.table.db.inc.php');

class Giornata extends GiornataTable {

    /**
     *
     * @var bool
     */
    public $partiteInCorso;

    /**
     *
     * @var bool
     */
    public $stagioneFinita;


    /**
     *
     * @return bool
     */
    public function getPartiteInCorso() {
        return (bool) $this->partiteInCorso;
    }

    /**
     *
     * @return bool
     */
    public function getStagioneFinita() {
        return (bool) $this->stagioneFinita;
    }


    /**
     *
     * @param Bool $partiteInCorso
     */
    public function setPartiteInCorso($partiteInCorso) {
        $this->partiteInCorso = (bool) $partiteInCorso;
    }

    /**
     *
     * @param bool $stagioneFinita
     */
    public function setStagioneFinita($stagioneFinita) {
        $this->stagioneFinita = (bool) $stagioneFinita;
    }


    /**
     *
     * @return Giornata
     */
    public static function getCurrentGiornata() {
        $minuti = isset($_SESSION['datiLega']) ? $_SESSION['datiLega']->minFormazione : 0;
        $q = "SELECT id
				FROM giornata
				WHERE NOW() BETWEEN dataInizio AND dataFine - INTERVAL :minuti MINUTE";
        $exe = ConnectionFactory::getFactory()->getConnection()->prepare($q);
        $exe->bindValue(":minuti", $minuti, PDO::PARAM_INT);
        $exe->execute();
        $valore = $exe->fetchObject(__CLASS__);
        if ($valore != FALSE)
            $valore->setPartiteInCorso(FALSE);
        else {
            $q = "SELECT MIN( id - 1 ) as id
				FROM giornata
				WHERE NOW() < dataFine - INTERVAL :minuti MINUTE";
            $exe = ConnectionFactory::getFactory()->getConnection()->prepare($q);
            $exe->bindValue(":minuti", $minuti, PDO::PARAM_INT);
            $exe->execute();
            $valore = $exe->fetchObject(__CLASS__);
            $valore->setPartiteInCorso(TRUE);
        }
        $valore->setStagioneFinita($valore->getId() > (self::getNumberGiornate() - 1));
        return $valore;
    }

    public static function isWeeklyScriptDay() {
        $now = new DateTime();
        $now->modify("-1 day");
        $previous = self::getById(GIORNATA - 1);
        return ($previous->getDataFine()->format("Y-m-d") == $now->format("Y-m-d") && $now->format("H") > 17);
    }

    public static function isDoTransertDay() {
        $now = new DateTime();
        $previous = self::getCurrentGiornata();
        return ($previous->getDataFine()->format("Y-m-d") == $now->format("Y-m-d"));
    }

    public static function isSendMailDay() {
        $now = new DateTime();
        $previous = self::getCurrentGiornata();
        return ($previous->getDataFine()->format("Y-m-d") == $now->format("Y-m-d"));
    }

    public static function checkDay($day, $type = 'dataInizio', $offset = 1) {
        $q = "SELECT dataInizio,dataFine,id
				FROM giornata
				WHERE '" . $day . "' BETWEEN dataInizio AND dataFine";
        $exe = ConnectionFactory::getFactory()->getConnection()->query($q);
        $exe->fetch(PDO::FETCH_ASSOC);
        if ($value != FALSE) {
            $array = explode(" ", $value[$type]);
            $data = explode("-", $array[0]);
            $dataConfronto = date("Y-m-d", mktime(0, 0, 0, $data[1], $data[2] + ($offset), $data[0]));
            if ($day == $dataConfronto)
                return $value['id'];
            else
                return FALSE;
        } else
            return FALSE;
    }

    public static function getNumberGiornate() {
        $q = "SELECT COUNT(id) as numeroGiornate
				FROM giornata";
        $exe = ConnectionFactory::getFactory()->getConnection()->query($q);
        return $exe->fetchColumn();
    }

    public static function getTargetCountdown() {
        $minuti = isset($_SESSION['datiLega']) ? $_SESSION['datiLega']->minFormazione : 0;
        $q = "SELECT MAX(dataFine) - INTERVAL :minuti MINUTE as dataFine
				FROM giornata
				WHERE NOW() > dataInizio";
        $exe = ConnectionFactory::getFactory()->getConnection()->prepare($q);
        $exe->bindValue(":minuti", $minuti, PDO::PARAM_INT);
        $exe->execute();
        return $exe->fetchObject(__CLASS__)->getDataFine();
    }

    public static function updateGiornate($giornate) {
        try {
            ConnectionFactory::getFactory()->getConnection()->beginTransaction();
            foreach ($giornate as $key => $val) {
                foreach ($val as $key2 => $val2) {
                    $q = "UPDATE giornata SET " . $key2 . " = :val WHERE id = :id";
                    $exe = ConnectionFactory::getFactory()->getConnection()->prepare($q);
                    $exe->bindValue(":val", $val);
                    $exe->bindValue(":id", $key, PDO::PARAM_INT);
                    $exe->execute();
                }
            }
            ConnectionFactory::getFactory()->getConnection()->commit();
        } catch (PDOException $e) {
            ConnectionFactory::getFactory()->getConnection()->rollBack();
            FirePHP::getInstance()->error($e->getMessage());
            return FALSE;
        }
        return TRUE;
    }

    private static function getArrayOrari($giornata) {
        require_once(INCDIR . 'fileSystem.inc.php');

        $orari = FileSystem::scaricaOrariGiornata($giornata);
        $calendario = array();
        $calendario[$giornata]['dataFine'] = date('Y-m-d H:i:s', $orari['inizioPartite']);
        $calendario[$giornata + 1]['dataInizio'] = date('Y-m-d H:i:s', $orari['finePartite'] + (2 * 3600));
        return $calendario;
    }

    public static function updateOrariGiornata($giornata = GIORNATA) {
        return self::updateGiornate(self::getArrayOrari($giornata));
    }

    public static function updateCalendario() {
        require_once(INCDIR . 'fileSystem.inc.php');

        $calendario[1]['dataInizio'] = "2012-08-01 00:00:00";
        for ($giornata = 1; $giornata <= 38; $giornata++) {
            $appo = self::getArrayOrari($giornata);
            $calendario[$giornata] = array_merge($calendario[$giornata], $appo[$giornata]);
            $calendario[$giornata + 1] = array_merge($calendario[$giornata], $appo[$giornata + 1]);
        }
        $calendario[39]['dataFine'] = "2013-07-31 23:59:59";
        return self::updateGiornate($calendario);
    }

    public static function getTimeDiff($t1, $t2 = NULL) {
        $t2 = (is_null($t2)) ? date("H:i:s") : $t2;
        $a1 = explode(":", $t1);
        $a2 = explode(":", $t2);
        $time1 = (($a1[0] * 60 * 60) + ($a1[1] * 60) + ($a1[2]));
        $time2 = (($a2[0] * 60 * 60) + ($a2[1] * 60) + ($a2[2]));
        $diff = abs($time1 - $time2);
        return $diff;
    }

}

?>
