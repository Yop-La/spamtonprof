$(document).ready(function(){



	function printPriceBox() { 

		if( $('input[name=maths]').is(':checked') && !$('input[name=physique]').is(':checked') && !$('input[name=francais]').is(':checked')){
			$('.francais').hide();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').hide();
			$('.maths').show();
			$('.pas-dispo').hide();
			$('.zero-matiere').hide();
		}else if( !$('input[name=maths]').is(':checked') && $('input[name=physique]').is(':checked') && !$('input[name=francais]').is(':checked')){
			$('.francais').hide();
			$('.physique-chimie').show();
			$('.physique-chimie-maths').hide();
			$('.maths').hide();
			$('.pas-dispo').hide();
			$('.zero-matiere').hide();
		}else if( !$('input[name=maths]').is(':checked') && !$('input[name=physique]').is(':checked') && $('input[name=francais]').is(':checked')){

			$('.francais').show();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').hide();
			$('.maths').hide();
			$('.pas-dispo').hide();
			$('.zero-matiere').hide();
		}else if( $('input[name=maths]').is(':checked') && $('input[name=physique]').is(':checked') && !$('input[name=francais]').is(':checked')){

			$('.francais').hide();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').show();
			$('.maths').hide();
			$('.pas-dispo').hide();
			$('.zero-matiere').hide();
		}else if( $('input[name=maths]').is(':checked') && $('input[name=physique]').is(':checked') && $('input[name=francais]').is(':checked')){

			$('.francais').hide();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').hide();
			$('.maths').hide();
			$('.pas-dispo').show();
			$('.zero-matiere').hide();
		}else if( $('input[name=maths]').is(':checked') && !$('input[name=physique]').is(':checked') && $('input[name=francais]').is(':checked')){

			$('.francais').hide();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').hide();
			$('.maths').hide();
			$('.pas-dispo').show();
			$('.zero-matiere').hide();
		}else if( !$('input[name=maths]').is(':checked') && $('input[name=physique]').is(':checked') && $('input[name=francais]').is(':checked')){

			$('.francais').hide();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').hide();
			$('.maths').hide();
			$('.pas-dispo').show();
			$('.zero-matiere').hide();

		}else if( !$('input[name=maths]').is(':checked') && !$('input[name=physique]').is(':checked') && !$('input[name=francais]').is(':checked')){
			$('.francais').hide();
			$('.physique-chimie').hide();
			$('.physique-chimie-maths').hide();
			$('.maths').hide();
			$('.pas-dispo').hide();
			$('.zero-matiere').show();
		}
	}

	 $('input[name=maths]').on( "click",function(){printPriceBox();});
	 $('input[name=physique]').on( "click",function(){printPriceBox();});
	 $('input[name=francais]').on( "click",function(){printPriceBox();});


});