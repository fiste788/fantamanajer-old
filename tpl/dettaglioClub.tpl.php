<?php $r = 'Por.'; ?>
<div id="headerClub">
	<img class="logo left" alt="<?php echo $this->clubDett->id; ?>" src="<?php echo CLUBSURL . $this->request->get('club') . '.png'; ?>" title="Logo <?php echo $this->clubDett->nome; ?>" />
	<h2><?php echo $this->clubDett->nome; ?></h2>
</div>
<?php if(!empty($this->giocatori)): ?>
<div class="clear">
	<h3>Giocatori</h3>
	<table class="table">
		<thead>
			<tr>
				<th>Nome</th>
				<th class="center">Ruolo</th>
				<th class="center">Club</th>
				<th class="center">PG</th>
				<th class="center">MVoti</th>
				<th class="center">MPunti</th>
				<th class="center">Gol</th>
				<th class="center">Gol subiti</th>
				<th class="center">Assist</th>
				<th class="center">Ammonizioni</th>
				<th class="center">Esplusioni</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($this->giocatori as $val): ?>
			<tr class="tr <?php if(empty($val->idClub)) echo 'rosso'; ?>">
				<td title="" class="name<?php if($val->ruolo != $r) echo ' ult'; ?>">
					<a href="<?php echo Links::getLink('dettaglioGiocatore',array('edit'=>'view','id'=>$val->id)); ?>"><?php echo $val->cognome . ' ' . $val->nome; ?></a>
				</td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $val->ruolo; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->nomeClub)) ? strtoupper(substr($val->nomeClub,0,3)) : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $val->presenze . " (" . $val->presenzeVoto . ")"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->avgVoti)) ? $val->avgVoti : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->avgPunti)) ? $val->avgPunti : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->gol)) ? $val->gol : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->golSubiti)) ? $val->golSubiti : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->assist)) ? $val->assist : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->ammonizioni)) ? $val->ammonizioni : "&nbsp;"; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo (!empty($val->espulsioni)) ? $val->espulsioni : "&nbsp;"; ?></td>
			</tr>
			<?php $r = $val->ruolo; ?>
			<?php endforeach; ?>
			<tr>
				<td colspan="4">Totali</td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->avgVoti; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->avgPunti; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->totaleGol; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->totaleGolSubiti; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->totaleAssist; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->totaleAmmonizioni; ?></td>
				<td class="tdcenter<?php echo ($val->ruolo != $r) ? ' ult' : ''; ?>"><?php echo $this->clubDett->totaleEspulsioni; ?></td>
			</tr>
		</tbody>
	</table>
</div>
<?php endif;?>
