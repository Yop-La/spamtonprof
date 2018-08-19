
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
			$(".bloc-essai").addClass("hide");
		}


		for(var i = 0; i< nbAbosEssai ; i++){

			abo = abosEssai[i];

			rowEssai = $(".row-essai-template").clone();
			rowEssai.insertAfter(".row-essai-template");
			rowEssai.removeClass("row-essai-template");


			rowEssai.find(".prenom-eleve").html(abo.eleve.prenom);
			rowEssai.find(".nom-formule").html(abo.formule.formule);

			if(abo.debut_essai != null){
				
				$(".essai-off").addClass("hide");
				debut = new Date(abo.debut_essai);
				fin = new Date(abo.fin_essai);
				debut = debut.toLocaleString("fr-FR", {year: 'numeric', month: '2-digit', day: '2-digit'})
				fin = fin.toLocaleString("fr-FR", {year: 'numeric', month: '2-digit', day: '2-digit'})
				rowEssai.find(".date-essai").html("Du ".concat(debut, " au ",fin));
			}else{
				$(".essai-on").addClass("hide");
				rowEssai.find(".prof").html(abo.prof.prenom.concat(" ",abo.prof.nom));
				rowEssai.find(".adresse-prof").html(abo.prof.email_stp);
				
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

	waitForEl(".row-abo", function() {

		console.log("dedans");

		// pour charger et remplir les lignes d'abonnement
		nbAbos = abosActif.length;

		if(nbAbos == 0){
			$(".bloc-actif").addClass("hide");
		}

		for(var i = 0; i< nbAbos ; i++){

			abo = abosActif[i];

			rowAbo = $(".row-abo-template").clone();
			rowAbo.insertAfter(".row-abo-template");
			rowAbo.removeClass("row-abo-template");


			rowAbo.find(".prenom-eleve").html(abo.eleve.prenom);
			rowAbo.find(".nom-formule").html(abo.formule.formule);

			rowAbo.find(".prof").html(abo.prof.prenom.concat(" ",abo.prof.nom));
			rowAbo.find(".adresse-prof").html(abo.prof.email_stp);

			rowAbo.find(".ref-abo").val(i);

			rowAbo.removeClass("hide");


		}

		// pour attacher la popup d'annulation ou d'interruption au bouton d'annulation
		$('.pause').click(function(e) {



			console.log("fr");

			e.preventDefault();
		});

		$('.arreter').click(function(e) {

			indiceAbo = $(this).parents(".row-abo").find(".ref-abo").val();
			$("#popmake-".concat(popupArret," .ref-abo")).val(indiceAbo);



			console.log("fr");

			e.preventDefault();
		});


		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler.close();
		});


	});


	waitForEl(".confirmer-arret", function() {

		$(".confirmer-arret").click(function(){
			indiceAbo = $(this).parents("#popmake-".concat(popupArret)).find(".ref-abo").val();
			aboClique = abosActif[indiceAbo];

			console.log(aboClique);

			resilierAbonnement(aboClique);


		});

	});

	waitForEl(".row-abo-fini", function() {


		// pour charger et remplir les lignes d'abonnement
		nbAbos = abosTermine.length;

		if(nbAbos == 0){
			$(".bloc-resilie").addClass("hide");
		}

		for(var i = 0; i< nbAbos ; i++){

			abo = abosTermine[i];

			rowAbo = $(".row-abo-fini-template").clone();
			rowAbo.insertAfter(".row-abo-fini-template");
			rowAbo.removeClass("row-abo-fini-template");


			rowAbo.find(".prenom-eleve").html(abo.eleve.prenom);
			rowAbo.find(".nom-formule").html(abo.formule.formule);
			rowAbo.find(".date-resiliation").html(abo.dateDernierStatut);

			rowAbo.find(".ref-abo").val(i);

			rowAbo.removeClass("hide");


		}

		// pour attacher la popup d'annulation ou d'interruption au bouton d'annulation
		$('.pause').click(function(e) {



			console.log("fr");

			e.preventDefault();
		});

		$('.arreter').click(function(e) {

			indiceAbo = $(this).parents(".row-abo").find(".ref-abo").val();
			$("#popmake-".concat(popupArret," .ref-abo")).val(indiceAbo);



			console.log("fr");

			e.preventDefault();
		});


		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler.close();
		});


	});


	function resilierAbonnement(abo){


		$("#fountainTextG").removeClass("hide");
		$(".hide_loading").addClass("hide");
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
					$(".hide_loading").removeClass("hide");
					$("#fountainTextG").addClass("hide");
				}
			}else{
				redirectTo('dashboard-eleve','L\'abonnement va être bientôt résilié (un mail de confirmation va être envoyé)');
			}
		})
		.fail(function() {
			showMessage('Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. ');
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				$(".hide_loading").removeClass("hide");
				$("#fountainTextG").addClass("hide");
			}
		});
	}




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
				ajaxEnCours--;
				if(ajaxEnCours == 0){
					$(".hide_loading").removeClass("hide");
					$("#fountainTextG").addClass("hide");
				}
			}else{
				redirectTo('dashboard-eleve','Félicitations : le paiement est passé. L\'inscription est bien validé.');
			}
		})
		.fail(function() {
			showMessage('Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. ');
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				$(".hide_loading").removeClass("hide");
				$("#fountainTextG").addClass("hide");
			}
		});
	}





});