<?php 
$firePHP->group("Notifiche");
if ($_SESSION['logged']) {
	require_once(INCDBDIR . 'giocatore.db.inc.php');
	require_once(INCDBDIR . 'trasferimento.db.inc.php');
    require_once(INCDBDIR . 'formazione.db.inc.php');

	if(!STAGIONEFINITA) {
    	$formazione = Formazione::getFormazioneBySquadraAndGiornata($_SESSION['idUtente'],GIORNATA);
    	if(empty($formazione))
    	    $notifiche[] = new Notify(Notify::LEVEL_MEDIUM,'Non hai ancora impostato la formazione per questa giornata',Links::getLink('formazione'));
	}

    $giocatoriInattivi = Giocatore::getGiocatoriInattiviByIdUtente($_SESSION['idUtente']);
	if(!empty($giocatoriInattivi) && count(Trasferimento::getTrasferimentiByIdSquadra($_SESSION['idUtente'])) < $_SESSION['datiLega']->numTrasferimenti )
        $notifiche[] = new Notify(Notify::LEVEL_HIGH,'Un tuo giocatore non � pi� nella lista!',Links::getLink('trasferimenti'));
}
$firePHP->groupEnd();
$navbarTpl->assign('entries',$pages);
?>
