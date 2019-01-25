jQuery( document ).ready( function( jQuery ) {

	
	
	showMessage("Psss, c'est <a class = 'link_msg' href = 'spamtonprof.com/semaine-decouverte' >là qu'il faut aller pour profiter de 7 jours offerts</a>")

//	//debut timer essai	
//
//
//	var timerEssai = localStorage.getItem("timerEssai");
//	console.log('timerEssai');
//	console.log(timerEssai);
//
//	var printTimer = true;
//
//	if(!timerEssai){
//
//		var timerEssai = new Date().getTime() + 5*24*60*60*1000;	
//		localStorage.setItem("timerEssai", timerEssai);
//
//	}
//
//	jQuery("#band-message").html('<div id = "promo_essai">7 jours d\'essai offerts dans la matière de votre choix </div><br> <div id = "expiration_essai"> Expire dans <span id = "counter-essai"></span><div>');
//
//	jQuery("#counter-essai").countdown(timerEssai, {elapse: true})
//	.on('update.countdown', function(event) {
//		var el = jQuery(this);
//		if (event.elapsed) {
//			hideMessage();
//		} else {
//			el.html(event.strftime('%D jours %H h %M min %S s'));
//			if(printTimer){
//				jQuery("#top-message").removeClass("hide");
//				window.scrollTo(0, 0);
//				printTimer = false;
//			}
//
//		}
//	});
//
//	//	fin timer essai




});