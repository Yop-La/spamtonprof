jQuery( document ).ready( function( $ ) {



	function printPriceBox() { 

		if( jQuery('input[name=maths]').is(':checked') && !jQuery('input[name=physique]').is(':checked') && !jQuery('input[name=francais]').is(':checked')){
			jQuery('.francais').hide();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').show();
			jQuery('.pas-dispo').hide();
			jQuery('.zero-matiere').hide();
		}else if( !jQuery('input[name=maths]').is(':checked') && jQuery('input[name=physique]').is(':checked') && !jQuery('input[name=francais]').is(':checked')){
			jQuery('.francais').hide();
			jQuery('.physique-chimie').show();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').hide();
			jQuery('.zero-matiere').hide();
		}else if( !jQuery('input[name=maths]').is(':checked') && !jQuery('input[name=physique]').is(':checked') && jQuery('input[name=francais]').is(':checked')){

			jQuery('.francais').show();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').hide();
			jQuery('.zero-matiere').hide();
		}else if( jQuery('input[name=maths]').is(':checked') && jQuery('input[name=physique]').is(':checked') && !jQuery('input[name=francais]').is(':checked')){

			jQuery('.francais').hide();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').show();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').hide();
			jQuery('.zero-matiere').hide();
		}else if( jQuery('input[name=maths]').is(':checked') && jQuery('input[name=physique]').is(':checked') && jQuery('input[name=francais]').is(':checked')){

			jQuery('.francais').hide();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').show();
			jQuery('.zero-matiere').hide();
		}else if( jQuery('input[name=maths]').is(':checked') && !jQuery('input[name=physique]').is(':checked') && jQuery('input[name=francais]').is(':checked')){

			jQuery('.francais').hide();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').show();
			jQuery('.zero-matiere').hide();
		}else if( !jQuery('input[name=maths]').is(':checked') && jQuery('input[name=physique]').is(':checked') && jQuery('input[name=francais]').is(':checked')){

			jQuery('.francais').hide();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').show();
			jQuery('.zero-matiere').hide();

		}else if( !jQuery('input[name=maths]').is(':checked') && !jQuery('input[name=physique]').is(':checked') && !jQuery('input[name=francais]').is(':checked')){
			jQuery('.francais').hide();
			jQuery('.physique-chimie').hide();
			jQuery('.physique-chimie-maths').hide();
			jQuery('.maths').hide();
			jQuery('.pas-dispo').hide();
			jQuery('.zero-matiere').show();
		}
	}

	 jQuery('input[name=maths]').on( "click",function(){printPriceBox();});
	 jQuery('input[name=physique]').on( "click",function(){printPriceBox();});
	 jQuery('input[name=francais]').on( "click",function(){printPriceBox();});


});