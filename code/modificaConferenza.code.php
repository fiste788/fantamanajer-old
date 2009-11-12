<?php 
require_once(INCDIR . "articolo.db.inc.php");
require_once(INCDIR . "evento.db.inc.php");
require_once(INCDIR . "emoticon.inc.php");

$articoloObj = new articolo();
$eventoObj = new evento();
$emoticonObj = new emoticon();

$filterAction = NULL;
$filterId = NULL;
if(isset($_GET['a']))
	$filterAction = $_GET['a'];
if(isset($_GET['id']))
	$filterId = $_GET['id'];

if($filterAction == 'edit' || $filterAction == 'cancel')
{
	$articoloObj->setidArticolo($filterId);
	$articolo = $articoloObj->select($articoloObj,'=','*');
	$contenttpl->assign('articolo',$articolo);
}

if(isset($_POST['submit']))
{
	if($filterAction == 'cancel')
	{
		$articoloObj->delete($articoloObj);
		$eventoObj->deleteEventoByIdExternalAndTipo($filterId,'1');
		$message['level'] = 0;
		$message['text'] = 'Cancellazione effettuata con successo';
		$_SESSION['message'] = $messaggio;
		header("Location: ".$contenttpl->linksObj->getLink('conferenzeStampa'));
	}

	if($filterAction == 'new' || $filterAction == 'edit')
	{
		//INSERISCO NEL DB OPPURE SEGNALO I CAMPI NON RIEMPITI
		if ( (isset($_POST['title'])) && (!empty($_POST['title'])) && (isset($_POST['text'])) && (!empty($_POST['text'])) )
		{
			$articoloObj = new articolo();
			$articoloObj->settitle(addslashes(stripslashes($_POST['title'])));
			$articoloObj->setabstract(addslashes(stripslashes($_POST['abstract'])));
			$articoloObj->settext(addslashes(stripslashes($_POST['text'])));
			if($filterAction == 'new')
			{
				$articoloObj->setinsertdate(date("Y-m-d H:i:s"));
				$articoloObj->setidgiornata(GIORNATA);
			}
			else
			{
				$articoloObj->setinsertdate($articolo[0]['insertDate']);
				$articoloObj->setidgiornata($articolo[0]['idGiornata']);
			}
			$articoloObj->setidsquadra($_SESSION['idSquadra']);
			$articoloObj->setidlega($_SESSION['idLega']);
			if($filterAction == 'new')
			{
				$idArticolo = $articoloObj->add($articoloObj);
				$message['level'] = 0;
				$message['text'] = "Inserimento completato con successo!";
				$eventoObj->addEvento('1',$_SESSION['idSquadra'],$_SESSION['idLega'],$idArticolo);
			}
			else
			{
				$articoloObj->setidArticolo($filterId);
				$articoloObj->update($articoloObj);
				$message['level'] = 0;
				$message['text'] = "Modifica effettuata con successo!";
			}
			$_SESSION['message'] = $message;
			header("Location: ". $contenttpl->linksObj->getLink('conferenzeStampa'));
		}
		else
		{
			$messaggio['level'] = 1;
			$messaggio['text'] = "Non hai compilato correttamente tutti i campi";
			$layouttpl->assign('message',$message);
		}
	}
}
$title = "";
$abstract = "";
$text = "";
if(isset($articolo))
	$title = $articolo[0]['title'];
if(isset($_POST['title']))
	$title = $_POST['title'];
if(isset($articolo))
	$abstract = $articolo[0]['abstract'];
if(isset($_POST['abstract']))
	$abstract = $_POST['abstract'];
if(isset($articolo))
	$text = $articolo[0]['text'];
if(isset($_POST['text']))
	$text = $_POST['text'];
switch($filterAction)
{
	case 'cancel': $button = 'Rimuovi'; break;
	case 'edit': $button = 'Modifica'; break; 
	case 'new': $button = 'Inserisci'; break;
	default: $button = 'Errore';break;
}
$goTo = array('a'=>$filterAction);
if($filterId != NULL) 
	$goTo['id'] = $filterId;

$contenttpl->assign('action',$filterAction);
$contenttpl->assign('title',$title);
$contenttpl->assign('abstract',$abstract);
$contenttpl->assign('text',$text);
$contenttpl->assign('emoticons',$emoticonObj->emoticon);
$contenttpl->assign('button',$button);
$contenttpl->assign('goTo',$goTo);
?>
