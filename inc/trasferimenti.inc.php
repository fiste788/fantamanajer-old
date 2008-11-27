<?php 
class trasferimenti
{
	var $idTrasf;
	var $idGiocOld;
	var $idGiocNew;
	var $idSquadra;
	var $idGiornata;
	
	function getTrasferimentiByIdSquadra($idSquadra,$idGiornata = 0)
	{
		$q = "SELECT idGiocOld,t1.nome as nomeOld,t1.cognome as cognomeOld,idGiocNew,t2.nome as nomeNew,t2.cognome as cognomeNew, idGiornata 
				FROM giocatore t1 INNER JOIN (trasferimenti INNER JOIN giocatore t2 ON trasferimenti.idGiocNew = t2.idGioc) ON t1.idGioc = trasferimenti.idGiocOld 
				WHERE trasferimenti.idSquadra = '" . $idSquadra . "' AND idGiornata > '" . $idGiornata . "'";
		$exe = mysql_query($q) or die(MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q);
		$values = array();
		while($row = mysql_fetch_array($exe))
			$values[] = $row;
		if(!empty($values))
			return $values;
		else
			return FALSE;
	}
	
	function transfer($giocOld,$giocNew,$squadra,$idLega)
	{
		require_once(INCDIR.'squadre.inc.php');
		require_once(INCDIR.'formazione.inc.php');
		require_once(INCDIR.'schieramento.inc.php');
		require_once(INCDIR.'eventi.inc.php');
		$squadreObj = new squadre();
		$formazioneObj = new formazione();
		$schieramentoObj = new schieramento();
		$eventiObj = new eventi();
		$squadraOld = $squadreObj->getSquadraByIdGioc($giocNew,$idLega);
		mysql_query("START TRANSACTION");
		if($squadraOld == FALSE)
		{
			$q = "INSERT INTO squadre 
					VALUES ('" . $idLega . "','" . $squadra . "','". $giocNew . "')";
			$q2 = "DELETE 
					FROM squadre 
					WHERE idGioc = '". $giocOld . "' AND idLega = '" . $idLega . "'";
		}
		else
		{
			$q = "UPDATE squadre 
					SET idUtente = '" . $squadra . "' 
					WHERE idGioc = '". $giocNew . "' AND idLega = '" . $idLega . "'";
			$q2 = "UPDATE squadre 
					SET idUtente = '" . $squadraOld . "' 
					WHERE idGioc = '". $giocOld . "' AND idLega = '" . $idLega . "'";
		}
		mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q;
		mysql_query($q2) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q;
		$q = "INSERT INTO trasferimenti (idGiocOld,idGiocNew,idSquadra,idGiornata) 
				VALUES ('" . $giocOld . "' , '" . $giocNew . "' ,'" . $squadra . "','" . GIORNATA . "')";
		mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q;
		$q = "SELECT idTrasf 
						FROM trasferimenti
						WHERE idGiocOld = '" . $giocOld . "' AND idGiocNew = '" . $giocNew . "' AND idGiornata = '" . GIORNATA . "' AND idSquadra = '" . $squadra ."'";
		$exe = mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q;
		$idTrasferimento = mysql_fetch_row($exe);
		$eventiObj->addEvento('4',$squadra,$idLega,$idTrasferimento[0]);
		$formazione = $formazioneObj->getFormazioneBySquadraAndGiornata($squadra,GIORNATA);
		if($formazione != FALSE)
		{
			if(in_array($giocOld,$formazione['elenco']))
				$schieramentoObj->changeGioc($formazione['id'],$giocOld,$giocNew);
			if(in_array($giocOld,$formazione['cap']))
				$formazioneObj->changeCap($formazione['id'],$giocNew,array_search($giocOld,$formazione['cap']));
		}
		if($squadraOld != FALSE)
		{
			$formazioneOld = $formazioneObj->getFormazioneBySquadraAndGiornata($squadraOld,GIORNATA);
			if($formazioneOld != FALSE)
			{
				if(in_array($giocNew,$formazioneOld['elenco']))
					$schieramentoObj->changeGioc($formazioneOld['id'],$giocNew,$giocOld);
				if(in_array($giocNew,$formazioneOld['cap']))
					$formazioneObj->changeCap($formazioneOld['id'],$giocOld,array_search($giocNew,$formazioneOld['cap']));
			}
			$q = "INSERT INTO trasferimenti (idGiocOld,idGiocNew,idSquadra,idGiornata) 
					VALUES ('" . $giocNew . "' , '" . $giocOld . "' ,'" . $squadraOld . "','" . GIORNATA . "')";
			mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q;
			$q = "SELECT idTrasf 
						FROM trasferimenti
						WHERE idGiocOld = '" . $giocNew . "' AND idGiocNew = '" . $giocOld . "' AND idGiornata = '" . GIORNATA . "' AND idSquadra = '" . $squadraOld ."'";
			$exe = mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q;
			$idTrasferimento = mysql_fetch_row($exe);
			$eventiObj->addEvento('4',$squadraOld,$idLega,$idTrasferimento[0]);
		}
		if(isset($err))
		{
			mysql_query("ROLLBACK");
			die("Errore nella transazione: <br />" . $err);
		}
		else
			mysql_query("COMMIT");
	}
	
	function doTransfertBySelezione()
	{
		require_once(INCDIR.'selezione.inc.php');
		require_once(INCDIR.'squadre.inc.php');
		require_once(INCDIR.'eventi.inc.php');
		require_once(INCDIR.'formazione.inc.php');
		require_once(INCDIR.'schieramento.inc.php');
		$selezioneObj = new selezione();
		$squadreObj = new squadre();
		$eventiObj = new eventi();
		$formazioneObj = new formazione();
		$schieramentoObj = new schieramento();
		$selezioni = $selezioneObj->getSelezioni();
		if($selezioni != FALSE)
		{
			foreach($selezioni as $key => $val)
			{
				mysql_query("START TRANSACTION");
				$squadreObj->unsetSquadraByIdGioc($val['giocOld'],$val['idLega']);
				$squadreObj->setSquadraByIdGioc($val['giocNew'],$val['idLega'],$val['idSquadra']);
				$q = "INSERT INTO trasferimenti (idGiocOld,idGiocNew,idSquadra,idGiornata) 
				VALUES ('" . $val['giocOld'] . "' , '" . $val['giocNew'] . "' ,'" . $val['idSquadra'] . "','" . GIORNATA . "')";
				mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q);
				$formazione = $formazioneObj->getFormazioneBySquadraAndGiornata($val['idSquadra'],GIORNATA);
				if($formazione != FALSE)
				{
					if(in_array($val['giocOld'],$formazione['elenco']))
						$schieramentoObj->changeGioc($formazione['id'],$val['giocOld'],$val['giocNew']);
					if(in_array($val['giocOld'],$formazione['cap']))
						$formazioneObj->changeCap($formazione['id'],$val['giocNew'],array_search($val['giocOld'],$formazione['cap']));
				}
				$q = "SELECT idTrasf 
						FROM trasferimenti
						WHERE giocOld = '" . $val['giocOld'] . "' AND giocNew = '" . $val['giocNew'] . "' AND idGiornata = '" . GIORNATA . "' AND idSquadra = '" . $val['idSquadra'] ."'";
				$exe = mysql_query($q) or $err = MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q);
				$idTrasferimento = mysql_fetch_row($exe);
				$eventiObj->addEvento('4',$val['idSquadra'],$val['idLega'],$idTrasferimento[0]);
				if(isset($err))
				{
					mysql_query("ROLLBACK");
					die("Errore nella transazione: <br />" . $err);
				}
				else
					mysql_query("COMMIT");
			}
			$selezioneObj->svuota();
		}
	}
	
	function getTrasferimentoById($id)
	{
		$q = "SELECT * 
				FROM trasferimenti 
				WHERE idTrasf = '" . $id . "'";
		$exe = mysql_query($q) or die(MYSQL_ERRNO() . " - " . MYSQL_ERROR() . "<br />Query: " . $q);
		while($row = mysql_fetch_array($exe))
			return $row;
	}
}
?>