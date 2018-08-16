
ajaxEnCours = 0;
montant = 20;
emailCheckout = "alex@gmx.fr";
aboClique = null;


jQuery( document ).ready( function( $ ) {


	waitForEl(".row-essai", function() {

		// pour charger et remplir les lignes d'essai 
		nbAbosEssai = abosEssai.length;

		for(var i = 0; i< nbAbosEssai ; i++){

			abo = abosEssai[i];

			rowEssai = $(".row-essai").clone();
			rowEssai.insertAfter(".row-essai");


			rowEssai.find(".prenom-eleve").html(abo.eleve.prenom);
			rowEssai.find(".nom-formule").html(abo.formule.formule);
//			rowEssai.find(".statut-essai").html(abo.eleve.prenom);
			debut = new Date(abo.debut_essai);
			fin = new Date(abo.fin_essai);
			debut = debut.toLocaleString("fr-FR", {year: 'numeric', month: '2-digit', day: '2-digit'})
			fin = fin.toLocaleString("fr-FR", {year: 'numeric', month: '2-digit', day: '2-digit'})
			rowEssai.find(".date-essai").html("Du ".concat(debut, " au ",fin));
			rowEssai.find(".ref-abo").val(i);

			rowEssai.removeClass("hide");


		}

		/** début formulaire de paiement stripe **/

		var handler = StripeCheckout.configure({
			key: publicStripeKey,
			image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
			locale: 'auto',
			allowRememberMe: false,
			token: function(token) {
				
				aboClique = abosEssai[indiceAbo];
				
				createSubscription(aboClique.ref_abonnement, token.id, testMode);
				
			}
		});

		// pour attacher la popup de paiement cb au bouton payer 
		$('.payer').click(function(e) {

			indiceAbo = $(this).parents(".row-essai").find(".ref-abo").val();
			aboClique = abosEssai[indiceAbo];

			montant = aboClique.plan.tarif;

			emailCheckout = "alexandre@spamtonprof.com";
			if(aboClique.eleve.ref_profil == 4){
				emailCheckout = aboClique.eleve.email;
			}else{
				emailCheckout = aboClique.proche.email;
			}

			// Open Checkout with further options:
			handler.open({
				name: 'SpamTonProf',
				description: 'Abonnement de '.concat(montant,' € par semaine'),
				zipCode: false,
				amount: montant*100,
				email : emailCheckout,
				currency: 'EUR'
			});
			e.preventDefault();
		});


		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler.close();
		});

		/** fin formulaire de paiement stripe **/


	});




	function createSubscription(refAbonnement, source, testMode){

		$("#fountainTextG").removeClass("hide");
		$(".hide_loading").addClass("hide");

		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action': 'ajaxCreateSubscription',
					'ref_abonnement' : refAbonnement,
					'source': source,
					'testMode' : testMode
				}
		)
		.done(function(retour) {
			if(retour.error){
				showMessage('Ooops : il y a eu un problème : '.concat(retour.message,'. Veuillez réessayer ou contacter l\'équipe.'));
			}else{
				showMessage('Félicitations : le paiement est passé. L\'inscription est bien validé.');
			}
		})
		.fail(function() {
			showMessage('Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. ');
		})
		.always(function(err){
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				$(".hide_loading").removeClass("hide");
				$("#fountainTextG").addClass("hide");
			}
		});
	}





});