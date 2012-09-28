<form class="form-inline" action="<?php echo Links::getLink('classifica'); ?>" method="post">
    <fieldset>
        <div class="control-group">
            <label for="giornata">Guarda la classifica alla giornata</label>
            <select id="giornata" name="giornata" onchange="this.form.submit();">
                <?php for ($j = $this->giornate; $j > 0; $j--): ?>
                    <option<?php echo ($this->getGiornata == $j) ? ' selected="selected"' : ''; ?>><?php echo $j; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </fieldset>
</form>
