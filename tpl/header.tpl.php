<div class="span4 hidden-phone">
    <a title="Home" href="<?php echo Links::getLink('home'); ?>">
        <h1>FantaManajer</h1>
    </a>
</div>
<?php if(!PARTITEINCORSO && !STAGIONEFINITA): ?>
	<div class="hidden-phone" id="countdown">Tempo rimanente per la formazione<br />
		<div><?php echo $this->dataFine['year'] . '-' . ($this->dataFine['month'] - 1) . '-' . $this->dataFine['day'] . ' ' . $this->dataFine['hour'] . ':' . $this->dataFine['minute'] . ':' . $this->dataFine['second']; ?></div>
	</div>
	<script type="text/javascript">
		// <![CDATA[
		var d = new Date();
		d.setFullYear(<?php echo $this->dataFine['year'] . ',' . ($this->dataFine['month'] - 1) . ',' . $this->dataFine['day']; ?>);
		d.setHours(<?php echo $this->dataFine['hour'] . ',' . $this->dataFine['minute'] . ',' . $this->dataFine['second']; ?>);
		// ]]>
	</script>
<?php endif; ?>
