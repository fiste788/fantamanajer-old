$("#ricerca select").change(function () {
		if(this.value != "")
		{
			$.ajax({
				url: 'dettaglioGiocatore/edit/' + this.value + '.html',
				type: "post",
				cache: false,
				dataType: "xml",
				complete: function(xml,text){
					dettaglio = $("#dettaglioGiocatore",xml.responseText);
					$("#dettaglioGiocatore").empty();
					$("#dettaglioGiocatore").html($(dettaglio).html());
					$("#upload").after('<input type="button" name="button" class="submit dark" value="Modifica" onclick="document.getElementById(\'formModifica\').submit()" />');
				}
			});
		}
	});
