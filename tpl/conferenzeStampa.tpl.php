<?php $i=0; ?>
<div id="confStampa" class="main-content">
	<?php if(isset($this->articoli) && !empty($this->articoli)):?>
		<?php foreach($this->articoli as $key => $val): ?>
			<?php if($i%2 == 0): ?>
				<div class="riga column last">
			<?php endif; ?>
			<?php $i++; ?>		
			<div class="conf-stampa column<?php if($i%2 == 0) echo ' last'; ?>">
				<div class="box2-top-sx column last">
				<div class="box2-top-dx column last">
				<div class="box2-bottom-sx column last">
				<div class="box2-bottom-dx column last">
				<div class="conf-stampa-content column last">
					<?php if(isset($_SESSION['idSquadra']) && $_SESSION['idSquadra'] == $val['idSquadra']): ?>
						<a class="column last" href="<?php echo $this->linksObj->getLink('modificaConferenza',array('a'=>'edit','id'=>$val['idArticolo'])); ?>">
							<img src="<?php echo IMGSURL.'edit.png'; ?>" alt="m" title="Modifica" />
						</a>
						<a class="column" href="<?php echo $this->linksObj->getLink('modificaConferenza',array('a'=>'cancel','id'=>$val['idArticolo'])); ?>">
							<img src="<?php echo IMGSURL.'cancel.png'; ?>" alt="e" title="Cancella" />
						</a>
					<?php endif; ?>
					<em>
						<span class="column last"><?php echo $this->squadre[$val['idSquadra']]['username']; ?></span>
						<span class="right"><?php echo $val['insertDate']; ?></span>
					</em>
					<h3 class="title"><?php echo $val['title']; ?></h3>
					<?php if(isset($val['abstract'])): ?><div class="abstract"><?php echo $val['abstract']; ?></div><?php endif; ?>
					<div class="text"><?php echo nl2br($val['text']); ?></div>
				</div>
				</div>
				</div>
				</div>
				</div>
			</div>
			<?php if($i%2 == 0 || $i == count($this->articoli)): ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<div>&nbsp;</div>
	<?php else: ?>
		Non sono presenti articoli
	<?php endif; ?>
</div>