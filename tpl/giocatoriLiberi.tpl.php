<?php $ruoli = array('P' => 'Portieri', 'D' => 'Difensori', 'C' => 'Centrocampisti', 'A' =>'Attaccanti') ?>
<div class="titolo-pagina">
	<div class="column logo-tit">
		<img align="left" src="<?php echo IMGSURL.'freeplayer-big.png'; ?>" alt="->" />
	</div>
	<h2 class="column">Giocatori liberi</h2>
</div>
<div id="freeplayer" class="main-content">
	<?php if($this->appo): ?>
	<?php if(TIMEOUT != '0' || TIMEOUT != FALSE): ?><form name="acq" action="<?php echo $this->linksObj->getLink('trasferimenti',array('squad'=>$_SESSION['idSquadra'])); ?>" method="post"><?php endif; ?>
	<table cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<?php if(TIMEOUT != '0' && GIORNATA != 1 && $_SESSION['legaView'] == $_SESSION['idLega']): ?><th class="check">Acq.</th><?php endif; ?>
				<th class="tableimg">&nbsp;</th>
				<th class="cognome"><a href="<?php echo $this->link['cognome'] ?>">Cognome</a></th>
				<th class="nome"><a href="<?php echo $this->link['nome'] ?>">Nome</a></th>
				<th class="club"><a href="<?php echo $this->link['club'] ?>">Club</a></th>
				<th class="club"><a href="<?php echo $this->link['voti'] ?>">M. p.ti</a></th>
				<th class="club"><a href="<?php echo $this->link['votiEff'] ?>">M. voti</a></th>
				<th class="club"><a href="<?php echo $this->link['partiteGiocate'] ?>">Partite</a></th>
			</tr>
			<?php foreach($this->freeplayer as $key => $val): ?>
			<tr>
				<?php if(TIMEOUT != '0' && GIORNATA != 1 && $_SESSION['legaView'] == $_SESSION['idLega']): ?><td class="check"><input class="radio" type="radio" name="acquista" value="<?php echo $val['idGioc']; ?>" /></td><?php endif; ?>
				<td class="tableimg">
					<a href="<?php echo $this->linksObj->getLink('dettaglioGiocatore',array('edit'=>'view','id'=>$val['idGioc'])) ?>">
				<?php if($val['voti'] >= $this->suff && $val['partiteGiocate'] >= $this->partite ||GIORNATA == 1): ?>
					<img alt="Verde" title="Verde" src="<?php echo IMGSURL.'player-tit.png' ?>"/>
				<?php elseif($val['voti'] >= $this->suff || $val['partiteGiocate'] >= $this->partite): ?>
					<img alt="Giallo" title="Giallo" src="<?php echo IMGSURL.'player-panch.png' ?>"/>
				<?php else: ?>
					<img alt="Rosso" title="Rosso" src="<?php echo IMGSURL.'player-rosso.png' ?>"/>
				<?php endif; ?>
					</a>
				</td>
				<td><?php echo $val['cognome']; ?></td>
				<td><?php if(!empty($val['nome'])) echo $val['nome']; else echo "&nbsp;" ?></td>
				<td><?php echo strtoupper(substr($val['club'],0,3)); ?></td>
				<td<?php if(!empty($val['votiAll'])) echo ' title="' . $val['votiAll'] . '"'; ?><?php if($val['voti'] >= $this->suff && GIORNATA != 1) echo ' class="verde"'; elseif(GIORNATA != 1) echo ' class="rosso"'; ?>><?php if(!empty($val['voti'])) echo $val['voti']; else echo "&nbsp;" ?></td>
				<td<?php if(!empty($val['votiEffAll'])) echo ' title="' . $val['votiEffAll'] . '"'; ?><?php if($val['voti'] >= $this->suff && GIORNATA != 1) echo ' class="verde"'; elseif(GIORNATA != 1) echo ' class="rosso"'; ?>><?php if(!empty($val['votiEff'])) echo $val['votiEff']; else echo "&nbsp;" ?></td>
				<td<?php if($val['partiteGiocate'] >= $this->partite && GIORNATA != 1) echo ' class="verde"'; elseif(GIORNATA != 1) echo ' class="rosso"'; ?>><?php echo $val['partiteGiocate']; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if(TIMEOUT != '0' || TIMEOUT != FALSE && $_SESSION['legaView'] == $_SESSION['idLega']): ?><p>Se clicchi sul bottone sottostante selezionerai il giocatore per l'acquisto che comunque non avverrà subito e che può essere annullato. Nella pagina che ti apparirà dopo aver cliccato sul bottone ci sono altre informazioni</p>
	<input type="submit" class="submit dark" value="Acquista" />
	</form><?php endif; ?>
<?php else: ?>
Parametri non validi
<?php endif; ?>
</div>
<div id="squadradett" class="messaggio column last">
	<div class="box2-top-sx column last">
	<div class="box2-top-dx column last">
	<div class="box2-bottom-sx column last">
	<div class="box2-bottom-dx column last">
	<div class="box-content column last">
		<?php if($_SESSION['logged'] == TRUE): ?>
			<?php require (TPLDIR.'operazioni.tpl.php'); ?>
		<?php endif; ?>
		<form id="freeplayeropt" class="column last" name="ruolo_form" action="<?php echo $this->linksObj->getLink('giocatoriLiberi'); ?>" method="post">
			<fieldset class="no-margin fieldset">
				<input type="hidden" name="p" value="<?php echo $_GET['p'];?>" />
				<input type="hidden" name="order" value="<?php echo $this->getorder ;?>" />
				<input type="hidden" name="v" value="<?php echo $this->getv;?>" />
				<h3 class="no-margin">Seleziona il ruolo:</h3>
				<select name="ruolo" onchange="document.ruolo_form.submit();">
					<?php foreach($ruoli as $key => $val): ?>
						<option <?php if($this->ruolo == $key) echo "selected=\"selected\"" ?> value="<?php echo $key?>"><?php echo $val; ?></option>
					<?php endforeach ?>
				</select>
				<div class="field column last">
					<label>Soglia sufficienza:</label>
					<input maxlength="3" name="suff" type="text" class="text" value="<?php echo $this->suff; ?>" />
				</div>
				<div class="field column last">
					<label>Soglia partite:</label>
					<input maxlength="2" name="partite" type="text" class="text" value="<?php echo $this->partite; ?>" />
					</div>
					<input class="submit" type="submit" value="OK"/>
			</fieldset>
		</form>
	</div>
	</div>
	</div>
	</div>
	</div>
</div>
