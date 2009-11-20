<?php if($_SESSION['logged'] == TRUE): ?>
	<div class="box-squadra column last">
		<h3><a href="<?php echo $this->linksObj->getLink('trasferimenti',array('squadra'=>$_SESSION['idSquadra'])); ?>">Trasferimenti</a></h3>	
		<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'transfert-other.png'; ?>" title="Trasferimenti" />
		<div>Vedi i trasferimenti effettuati dalla tua e dalle altre squadre e seleziona il tuo acquisto</div>
	</div>
	<div class="box-squadra column last">
		<h3><a href="<?php echo $this->linksObj->getLink('giocatoriLiberi'); ?>">Giocatori Liberi</a></h3>	
		<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'freeplayer-other.png'; ?>" title="Giocatori liberi" />
		<div>Guarda quì i giocatori liberi e le loro statistiche come la media voto e le partite giocate</div>
	</div>
	<div class="box-squadra column last">
		<h3><a href="<?php echo $this->linksObj->getLink('premi'); ?>">Premi</a></h3>
		<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'premi-other.png'; ?>" title="Premi" />
		<div>Consulta i premi che sono in palio per il vincitore e per i primi  quattro posti</div>
	</div>
	<div class="box-squadra column last">
		<h3><a href="<?php echo $this->linksObj->getLink('download'); ?>">Download</a></h3>
		<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'download.png'; ?>" title="Download" />
		<div>Scarica i voti di ogni giornata per controllare da te il punteggio ottenuto</div>
	</div>
	<?php if(!STAGIONEFINITA): ?>
	<div class="box-squadra column last">
		<h3><a href="<?php echo $this->linksObj->getLink('formazione'); ?>">Formazione</a></h3>
		<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'formazione-other.png'; ?>" title="Formazione" />
		<div>Imposta la squadra per la prossima giornata o vedi quella dei tuoi avversari</div>
	</div>
	<?php endif; ?>
<?php endif; ?>
<div class="box-squadra column last">
	<h3><a href="<?php echo $this->linksObj->getLink('contatti'); ?>">Contatti</a></h3>
	<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'contatti-other.png'; ?>" title="Contatti" />	
	<div>Chiedi maggiori informazioni agli sviluppatori del FantaManajer attraverso le mail</div>
</div>
<div class="box-squadra column last">
	<h3><a target="_blank" href="http://trac2.assembla.com/fantamanajer/wiki">Progetto</a></h3>	
	<img class="logo column last" alt="->" src="<?php echo IMGSURL . 'progetto-other.png'; ?>" title="Progetto" />
	<div>Per saperne di più sul FantaManajer, e per aiutarci a migliorarlo</div>
</div>