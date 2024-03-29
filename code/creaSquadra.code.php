<?php
require_once(INCDIR . 'utente.db.inc.php');
require_once(INCDIR . 'squadra.db.inc.php');
require_once(INCDIR . 'giocatore.db.inc.php');
require_once(INCDIR . 'lega.db.inc.php');
require_once(INCDIR . 'mail.inc.php');

$mailContent = new Savant3();

$filterAction = NULL;
$filterId = NULL;
$filterLega = NULL;

if(isset($_GET['a']))
	$filterAction = $_GET['a'];
if(isset($_POST['a']))
	$filterAction = $_POST['a'];
if(isset($_GET['id']))
	$filterId = $_GET['id'];
if(isset($_POST['id']))
	$filterId = $_POST['id'];
if(isset($_GET['lega']) && !empty($_GET['lega']))
	$filterLega = $_GET['lega'];
if(isset($_POST['lega']))
	$filterLega = $_POST['lega'];
if($_SESSION['roles'] == '1')
	$filterLega = $_SESSION['idLega'];

if($filterLega != NULL && $filterAction != NULL && $filterId != NULL)
{
	if(!isset($_POST['button']))
	{
		if($filterAction == 'cancel' || $filterAction == 'edit')
		{
			$squadraDett = Utente::getSquadraById($filterId);
			$contentTpl->assign('giocatori',array_values(Giocatore::getGiocatoriByIdSquadra($filterId)));
			$contentTpl->assign('datiSquadra',$squadraDett);
		}
	}
	else
	{
		if($filterAction == 'cancel')
		{
			if(Utente::deleteSquadra($filterId))
			{
				Squadra::unsetSquadraGiocatoreByIdSquadra($filterId);
				$message->success("Cancellazione effettuata correttamente");
				unset($_POST);
			}
			else
				$message->warning("Hai già eliminato questa squadra");
		}
		elseif($filterAction == 'edit' || $filterAction == 'new')
		{
			foreach($_POST as $key=>$val)
			{
				if($key != 'id')
					if(empty($val))
						$message->error("Non hai compilato tutti i campi");
			}
			if(!$message->show)
			{
				if(isset($_POST['mail']))
				{
					if(!Mail::checkEmailAddress($_POST['mail']))
						$message->error("Mail non corretta");
					else
						$email = $_POST['mail'];
				}
				if(isset($_POST['nome']))
				{
					$nomeSquadra = addslashes(stripslashes(trim($_POST['nome'])));
					if(Utente::getSquadraByNome(addslashes(stripslashes(trim($_POST['nome']))),$filterId) != FALSE)
						$message->error("Il nome della squadra è già presente");
				}
				else
					$nomeSquadra = addslashes(stripslashes(trim($squadraDett->nome)));
			}
			$giocatori = array();
			foreach($_POST['giocatore'] as $key => $val)
			{
				if(!empty($val))
				{
					if(in_array($val,$giocatori))
					{
						$message->error("Hai immesso un giocatore più di una volta");
						break;
					}
					else
						$giocatori[] = $val;
				}
				else
					$message->error("Non hai compilato tutti i giocatori");
			}
			if(Utente::getSquadraByUsername(addslashes(stripslashes(trim($_POST['usernamenew']))),$filterId) != FALSE)
				$message->error("Un altro utente con questo username è già presente");
			if(!$message->show)
			{
				if(isset($_POST['amministratore']) && $_POST['amministratore'] == "on")
					$amministratore = 1;
				else
					$amministratore = 0;
				$abilitaMail = 1;
				$nome = addslashes(stripslashes(trim($_POST['nomeProp'])));
				$cognome = addslashes(stripslashes(trim($_POST['cognome'])));
				if($filterAction == 'edit')
				{
					Utente::changeData($nomeSquadra,$nome,$cognome,$email,$abilitaMail,"",$amministratore,$filterId);
					$giocatoriOld = array_keys(Giocatore::getGiocatoriByIdSquadra($filterId));
					if(!empty($giocatoriOld)) 
					{
						foreach($giocatori as $key => $val)
						{
							if(!in_array($val,$giocatoriOld))
								Squadra::updateGiocatore($val,$giocatoriOld[$key],$filterId);
						}
					}
					else
						Squadra::setSquadraGiocatoreByArray($filterLega,$giocatori,$filterId);
					unset($_POST);
					$contentTpl->assign('giocatori',array_values(Giocatore::getGiocatoriByIdSquadra($filterId)));
					$contentTpl->assign('datiSquadra',Utente::getSquadraById($filterId));
					$message->success("Squadra modificata correttamente");
				}
				else
				{
					$password = Utente::createRandomPassword();
					$dbObj->startTransaction();
					$squadra = Utente::addSquadra(addslashes(stripslashes(trim($_POST['usernamenew']))),$nomeSquadra,$nome,$cognome,$amministratore,$password,$email,$filterLega);
					Squadra::setSquadraGiocatoreByArray($filterLega,$giocatori,$squadra);
					$dbObj->commit();
					$filterId = $squadra;
					$message->success("Squadra creata correttamente");
					$mailContent->assign('username',$_POST['usernamenew']);
					$mailContent->assign('squadra',$_POST['nome']);
					$mailContent->assign('password',$password);
					$mailContent->assign('lega',Lega::getLegaById($filterLega));
					$mailContent->assign('autore',Utente::getSquadraById($_SESSION['idUtente']));
					$object = "Benvenuto nel FantaManajer!";
					//$mailContent->display(MAILTPLDIR.'mailBenvenuto.tpl.php');
					$mailObj->sendEmail($_POST['mail'],$mailContent->fetch(MAILTPLDIR . 'mailBenvenuto.tpl.php'),$object);
					unset($_POST);
				}
			}
		}
	}
}

if(isset($filterAction))
{
	switch($filterAction)
	{
		case 'new': $button = 'Crea'; break;
		case 'edit': $button = 'Modifica'; break; 
		case 'cancel': $button = 'Cancella'; break; 
	}
}
$contentTpl->assign('portieri',Giocatore::getFreePlayer('P',$filterLega));
$contentTpl->assign('difensori',Giocatore::getFreePlayer('D',$filterLega));
$contentTpl->assign('centrocampisti',Giocatore::getFreePlayer('C',$filterLega));
$contentTpl->assign('attaccanti',Giocatore::getFreePlayer('A',$filterLega));
$contentTpl->assign('lega',$filterLega);
$contentTpl->assign('id',$filterId);
$contentTpl->assign('action',$filterAction);
$goTo = array();
if($filterAction != NULL && $filterAction == 'cancel' || $filterAction == 'new')
	$goTo = array('a'=>'new','id'=>'0','lega'=>$filterLega);
elseif($filterAction != NULL)
	$goTo = array('a'=>'edit','id'=>$filterId,'lega'=>$filterLega);
$contentTpl->assign('goTo',$goTo);
$contentTpl->assign('button',$button);
if($filterLega != NULL)
	$operationTpl->assign('elencoSquadre',Utente::getElencoSquadreByLega($filterLega));
$operationTpl->assign('elencoLeghe',Lega::getLeghe());
$operationTpl->assign('lega',$filterLega);
$operationTpl->assign('id',$filterId);
$operationTpl->assign('action',$filterAction);
?>
