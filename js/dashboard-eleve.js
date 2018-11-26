
ajaxEnCours = 0;
montant = 20;
emailCheckout = "alex@gmx.fr";
aboClique = null;
popupArret = "18626";


jQuery( document ).ready( function( $ ) {



	waitForEl(".row-essai", function() {

		// pour charger et remplir les lignes d'essai 
		nbAbosEssai = abosEssai.length;


		if(nbAbosEssai == 0){
			jQuery(".bloc-essai").addClass("hide");
		}


		for(var i = 0; i< nbAbosEssai ; i++){

			abo = abosEssai[i];

			rowEssai = jQuery(".row-essai-template").clone();
			rowEssai.insertAfter(".row-essai-template");
			rowEssai.removeClass("row-essai-template");

			console.log("abo")
			console.log(abo)
			rowEssai.find(".prenom-eleve").html(abo.eleve.prenom);
			rowEssai.find(".nom-formule").html(abo.formule.formule.split("|")[0]. concat(" - ",abo.eleve.niveau.niveau));

			if(abo.debut_essai != null){

				jQuery(".essai-off").addClass("hide");
				debut = new Date(abo.debut_essai);
				fin = new Date(abo.fin_essai);
				debut = debut.toLocaleString("fr-FR", {year: 'numeric', month: '2-digit', day: '2-digit'})
				fin = fin.toLocaleString("fr-FR", {year: 'numeric', month: '2-digit', day: '2-digit'})
				rowEssai.find(".date-essai").html("Du ".concat(debut, " au ",fin));
				rowEssai.find(".prof").html(abo.prof.prenom.concat(" ",abo.prof.nom));
				rowEssai.find(".adresse-prof").html(abo.prof.email_stp);

			}else{
				jQuery(".essai-on").addClass("hide");

			}
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
		jQuery('.payer').click(function(e) {

			indiceAbo = jQuery(this).parents(".row-essai").find(".ref-abo").val();
			aboClique = abosEssai[indiceAbo];

			montant = aboClique.plan.tarif;

			emailCheckout = "alexandre@spamtonprof.com";
			if(!aboClique.eleve.parent_required){
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

	waitForEl(".row-abo", function() {

		console.log("dedans");

		// pour charger et remplir les lignes d'abonnement
		nbAbos = abosActif.length;

		if(nbAbos == 0){
			jQuery(".bloc-actif").addClass("hide");
		}

		for(var i = 0; i< nbAbos ; i++){

			abo = abosActif[i];

			rowAbo = jQuery(".row-abo-template").clone();
			rowAbo.insertAfter(".row-abo-template");
			rowAbo.removeClass("row-abo-template");


			rowAbo.find(".prenom-eleve").html(abo.eleve.prenom);
			rowAbo.find(".nom-formule").html(abo.formule.formule);

			rowAbo.find(".prof").html(abo.prof.prenom.concat(" ",abo.prof.nom));
			rowAbo.find(".adresse-prof").html(abo.prof.email_stp);

			rowAbo.find(".ref-abo").val(i);

			rowAbo.removeClass("hide");


		}


		/** début formulaire de paiement stripe **/

		var handler2 = StripeCheckout.configure({
			key: publicStripeKey,
			image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
			locale: 'auto',
			allowRememberMe: false,
			token: function(token) {



				updateCb(compte.ref_compte, token.id, testMode);

			}
		});

		waitForEl(".updatecb", function() {


			jQuery('.updatecb').click(function(e) {
				var emailCb; 
				if (typeof proche !== 'undefined') {
					emailCb = proche.email;
				}else{
					emailCb = loggedEleve.email;
				}


				// Open Checkout with further options:
				handler2.open({
					name: 'SpamTonProf',
					description: 'Mise à jour de la carte bancaire',
					zipCode: false,
					email: emailCb,
					panelLabel: "Mettre à jour la CB"
				});
				e.preventDefault();


			});
		});

		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler2.close();
		});


		// pour attacher la popup d'annulation ou d'interruption au bouton d'annulation
		jQuery('.pause').click(function(e) {



			console.log("fr");

			e.preventDefault();
		});

		jQuery('.arreter').click(function(e) {

			indiceAbo = jQuery(this).parents(".row-abo").find(".ref-abo").val();
			jQuery("#popmake-".concat(popupArret," .ref-abo")).val(indiceAbo);



			console.log("fr");

			e.preventDefault();
		});


	});


	waitForEl(".confirmer-arret", function() {

		jQuery(".confirmer-arret").click(function(){
			indiceAbo = jQuery(this).parents("#popmake-".concat(popupArret)).find(".ref-abo").val();
			aboClique = abosActif[indiceAbo];

			console.log(aboClique);

			resilierAbonnement(aboClique);


		});

	});

	waitForEl(".row-abo-fini", function() {


		// pour charger et remplir les lignes d'abonnement
		nbAbos = abosTermine.length;

		if(nbAbos == 0){
			jQuery(".bloc-resilie").addClass("hide");
		}

		for(var i = 0; i< nbAbos ; i++){

			abo = abosTermine[i];

			rowAbo = jQuery(".row-abo-fini-template").clone();
			rowAbo.insertAfter(".row-abo-fini-template");
			rowAbo.removeClass("row-abo-fini-template");


			rowAbo.find(".prenom-eleve").html(abo.eleve.prenom);
			rowAbo.find(".nom-formule").html(abo.formule.formule);
			rowAbo.find(".date-resiliation").html(abo.dateDernierStatut);

			rowAbo.find(".ref-abo").val(i);

			rowAbo.removeClass("hide");


		}

		// pour attacher la popup d'annulation ou d'interruption au bouton d'annulation
		jQuery('.pause').click(function(e) {



			console.log("fr");

			e.preventDefault();
		});

		jQuery('.arreter').click(function(e) {

			indiceAbo = jQuery(this).parents(".row-abo").find(".ref-abo").val();
			jQuery("#popmake-".concat(popupArret," .ref-abo")).val(indiceAbo);



			console.log("fr");

			e.preventDefault();
		});





	});


	function resilierAbonnement(abo){


		jQuery("#fountainTextG").removeClass("hide");
		jQuery(".hide_loading").addClass("hide");
		PUM.close(popupArret);

		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action': 'ajaxStopSubscription',
					'ref_abonnement' : abo.ref_abonnement,
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
				redirectTo('dashboard-eleve','L\'abonnement va être bientôt résilié (un mail de confirmation va être envoyé)');
			}
		})
		.fail(function() {
			showMessage('Oops : il y a eu un problème avec la demande d\'arrêt. Veuillez réessayer ou contacter l\'équipe. ');
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				jQuery(".hide_loading").removeClass("hide");
				jQuery("#fountainTextG").addClass("hide");
			}
		});
	}




	function createSubscription(refAbonnement, source, testMode){

		jQuery("#fountainTextG").removeClass("hide");
		jQuery(".hide_loading").addClass("hide");

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
				ajaxEnCours--;
				if(ajaxEnCours == 0){
					jQuery(".hide_loading").removeClass("hide");
					jQuery("#fountainTextG").addClass("hide");
				}
			}else{
				redirectTo('dashboard-eleve','Félicitations : le paiement est passé. L\'inscription est bien validé.');
			}
		})
		.fail(function() {
			showMessage('Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. ');
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				jQuery(".hide_loading").removeClass("hide");
				jQuery("#fountainTextG").addClass("hide");
			}
		});
	}

	function updateCb(refCompte, source, testMode){

		jQuery("#fountainTextG").removeClass("hide");
		jQuery(".hide_loading").addClass("hide");

		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action': 'ajaxUpdateCb',
					'ref_compte' : refCompte,
					'source': source,
					'testMode' : testMode
				}
		)
		.done(function(retour) {
			console.log(retour);
			if(retour.error){
				showMessage('Ooops : il y a eu un problème : '.concat(retour.message,'. Veuillez réessayer ou contacter l\'équipe.'));

			}else{
				showMessage('Votre carte a été bien mise à jour !');	
			}
		})
		.fail(function() {
			showMessage('Oops : il y a eu un problème avec la mise à jour. Veuillez réessayer ou contacter l\'équipe. ');
		})
		.always(function(){
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				jQuery(".hide_loading").removeClass("hide");
				jQuery("#fountainTextG").addClass("hide");
			}
		});
	}





});