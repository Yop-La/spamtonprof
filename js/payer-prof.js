
ajaxEnCours = 0;
montant = 20;
emailCheckout = "alex@gmx.fr";
aboClique = null;
popupArret = "18626";




jQuery( document ).ready( function( $ ) {

	/** début formulaire de paiement stripe **/

	var handler = StripeCheckout.configure({
		key: publicStripeKey,
		image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
		locale: 'auto',
		allowRememberMe: false,
		token: function(token) {

			executerPaiement( token.id, testMode, montant);

		}
	});
	waitForEl(".valider", function() {

		// pour attacher la popup de paiement cb au bouton payer 
		jQuery('.valider').click(function(e) {

			console.log('salut')

			montant = jQuery('.montant').val();
			montant = parseFloat(montant.replace(',','.'))

			

			description = 'Montant de '.concat(montant, " € ") 
			emailCheckout = jQuery('.email').val()
				

			// Open Checkout with further options:
			handler.open({
				name: 'SpamTonProf',
				description: description,
				zipCode: false,
				amount: montant*100,
				email : emailCheckout,
				currency: 'EUR',
				locale: 'auto',
				'panel-label': "Payer {{amount}}"
			});
			e.preventDefault();
		});


		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler.close();
		});

	});
	
	function executerPaiement( source, testMode, montant){

		jQuery("#fountainTextG").removeClass("hide");
		jQuery(".hide_loading").addClass("hide");

		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action': 'ajax_payement_seb',
					'source': source,
					'montant': montant,
					'testMode' : testMode
				}
		)
		.done(function(retour) {
			if(retour.error){
				showMessage('Ooops : il y a eu un problème : '.concat(retour.message,'. Veuillez réessayer ou contacter l\'équipe.'));
				ajaxEnCours--;
				if(ajaxEnCours == 0){
					jQuery(".hide_loading").removeClass("hide");
					jQuery("#fountainTextG").addClass("hide");
				}
			}else{
				redirectTo('payer-prof','Félicitations : le paiement est bien passé !');
			}
		})
		.fail(function(retour) {
			showMessage('Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. ');
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				jQuery(".hide_loading").removeClass("hide");
				jQuery("#fountainTextG").addClass("hide");
			}
		});
	}

	/** fin formulaire de paiement stripe **/


});



