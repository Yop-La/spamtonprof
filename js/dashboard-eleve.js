
ajaxEnCours = 0;
montant = 20;
emailCheckout = "alex@gmx.fr";
aboClique = null;
popupArret = "18626";

var popProlongerInterruption= 27975;
var popFinInterruption= 27976;

var currentInterruption=false;


idFormInterruption = 89;
idFormProlongerInterruption = 88;

if(domain != 'spamtonprof'){
	popProlongerInterruption = 24793;
	idFormInterruption = 88;
	idFormProlongerInterruption = 89;

	popProlongerInterruption= 24793;
	popFinInterruption= 24885;


}




jQuery( document ).ready( function( $ ) {


	goToTab(onglet);

	$(".prolonger").click(function(){

		console.log("click prolonger");

		rowBreak = jQuery(this).parents(".row-break");//.find(".ref-abo").val();




		ref_interruption = jQuery(rowBreak).find(".ref_interruption").val();

		$(".ref_interruption_nf").val( ref_interruption ).trigger( 'change' );

		console.log("ref_interruption");
		console.log(ref_interruption);

		PUM.open(popProlongerInterruption);




	});

	$(".btn-end-interruption").click(function(){

		jQuery("#loading_screen").removeClass("hide");
		jQuery(".hide_loading").addClass("hide");

		PUM.close(popFinInterruption);


		// soumission ajax des champs du form pour création inscription
		ajaxEnCours++;
		jQuery.post(
				ajaxurl,
				{
					'action' : 'stopInterruption',
					'ref_interruption' : currentInterruption.ref_interruption,
				})
				.done(function(retour){ 

					console.log(retour);

					error = retour.error;
					message = retour.message;

					if(error){


						ajaxEnCours--;
						if(ajaxEnCours == 0){


						}

					}else{



						redirectTo('dashboard-eleve?onglet=2',message);

					}


				})
				.fail(function(err){

					ajaxEnCours--;
					if(ajaxEnCours == 0){
						jQuery("#loading_screen").addClass("hide");
						jQuery(".hide_loading").removeClass("hide");
					}

					console.log("erreur ajax");
					console.log(err);
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

				});


	});

	$(".stop").click(function(){

		console.log("click stop");

		rowBreak = jQuery(this).parents(".row-break");//.find(".ref-abo").val();




		ref_interruption = jQuery(rowBreak).find(".ref_interruption").val();

		$(".ref_interruption_nf").val( ref_interruption ).trigger( 'change' );

		console.log("ref_interruption");
		console.log(ref_interruption);



		interruptions.forEach(function(el){
			if(el.ref_interruption == ref_interruption){
				currentInterruption=el;
			}
		})

		$('.delete-interruption').addClass('hide');
		$('.fin-interruption').addClass('hide');

		if(currentInterruption.statut=='running'){
			$('.fin-interruption').removeClass('hide');
			$(".btn-end-interruption").text("Arrêter maintenant l'interruption");
		}else{
			$('.delete-interruption').removeClass('hide');
			$(".btn-end-interruption").text("Supprimer maintenant l'interruption");
		}

		PUM.open(popFinInterruption);




	});

	waitForEl(".row-break-template",function(){

//		$(".row-break-template").addClass('hide');


		interruptions.forEach(function(interruption){

			rowBreak = jQuery(".row-break-template").clone(true);
			rowBreak.insertAfter(".row-break-template");
			jQuery(rowBreak).removeClass("row-break-template");

			jQuery(rowBreak).find(".date_debut").text(interruption.debut);
			jQuery(rowBreak).find(".date_fin").text(interruption.fin);
			jQuery(rowBreak).find(".ref_interruption").val(interruption.ref_interruption);
			jQuery(rowBreak).find(".abonnement").text(interruption.abo.eleve.prenom.concat('-',interruption.abo.formule.formule));
//			jQuery(rowEssai).find().text(coupon.description);


			statutInterruption="erreur";
			classStatutInterruption="bg-red";
			removeCmdButtons = false;

			if(interruption.statut == 'scheduled'){

				classStatutInterruption="bg-orange";
				statutInterruption='programmé';
				jQuery(rowBreak).find(".stop").text('Supprimer');

			}

			if(interruption.statut == 'running'){
				classStatutInterruption="bg-blue";
				statutInterruption='en cours';
			}

			if(interruption.statut == 'done'){
				classStatutInterruption="bg-green";
				statutInterruption='terminé';
				removeCmdButtons = true;
			}

			if(interruption.statut == 'stopping'){
				statutInterruption="En cours d'arrêt";
				classStatutInterruption="bg-red";
				removeCmdButtons = true;
			}

			jQuery(rowBreak).find(".statut").text(statutInterruption);
			jQuery(rowBreak).find(".statut").removeClass().addClass("statut").addClass("simple-button").addClass(classStatutInterruption);

			if(removeCmdButtons){

				jQuery(rowBreak).find('.cmd').addClass("hide");

			}



		});


		$(".row-break-template").addClass('hide');


	});





	console.log('jquery');


	var mySubmitController = Marionette.Object.extend( {

		initialize: function() {
			this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
		},

		actionSubmit: function( response ) {



			console.log('soumis');

			jQuery("#loading_screen").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");
			jQuery("#res_recherche").addClass('hide');
			jQuery("#no_res").addClass('hide');



			fields = response.data.fields;

			console.log('fields');
			console.log(fields);

			champs = {};

			Object.values(fields).forEach(function(field){

				champs[field.label] = field.submitted_value;
				if(typeof field.submitted_value =='undefined' || field.submitted_value == ""){
					champs[field.label] = field.value;
				}


			})

			console.log('champs')
			console.log(champs)

			// pour ajouter une interruption
			if(response.data.form_id == idFormInterruption){

				hideMessage();
				console.log('add interruption');

				// soumission ajax des champs du form pour création inscription
				ajaxEnCours++;
				jQuery.post(
						ajaxurl,
						{
							'action' : 'addInterruption',
							'fields' : JSON.stringify(champs),
						})
						.done(function(retour){ 

							console.log(retour);

							error = retour.error;
							message = retour.message;

							if(error){


								ajaxEnCours--;
								if(ajaxEnCours == 0){


								}

							}else{



								redirectTo('dashboard-eleve?onglet=2',message);

							}


						})
						.fail(function(err){

							ajaxEnCours--;
							if(ajaxEnCours == 0){
								jQuery("#loading_screen").addClass("hide");
								jQuery(".hide_loading").removeClass("hide");
							}

							console.log("erreur ajax");
							console.log(err);
							showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

						});


			}


			if(response.data.form_id == idFormProlongerInterruption){

				PUM.close(popProlongerInterruption);
				hideMessage();

				// soumission ajax des champs du form pour création inscription
				ajaxEnCours++;
				jQuery.post(
						ajaxurl,
						{
							'action' : 'updateInterruption',
							'fields' : JSON.stringify(champs),
						})
						.done(function(retour){ 

							console.log(retour);

							ajaxEnCours--;

							error = retour.error;
							message = retour.message;

							if(error){


								if(ajaxEnCours == 0){


								}

							}else{


								redirectTo('dashboard-eleve?onglet=2',message);


							}


						})
						.fail(function(err){

							ajaxEnCours--;
							if(ajaxEnCours == 0){
								jQuery("#loading_screen").addClass("hide");
								jQuery(".hide_loading").removeClass("hide");
							}

							console.log("erreur ajax");
							console.log(err);
							showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

						});


			}


		}

	});




	new mySubmitController();





	waitForEl('#table_1', function() {

		console.log('table existe');

		// récupérer la donnée

		// charger la donnée dans la table

//		table4 = jQuery('#table_4').DataTable( {
//		data: data.tab4,
//		columns: [
//		{ title: "Domain name" },
//		{ title: "Mail provider" },
//		{ title: "Nb account" },
//		{ title: "Nb ads","searchable": false },
//		{ title: "Disabled","searchable": false },
//		],
//		"order": [[ 3, "desc" ]],
//		"autoWidth": false
//		} );

	} );



	waitForEl(".row-essai", function() {

		// pour charger et remplir les lignes d'essai 
		nbAbosEssai = abosEssai.length;


		if(nbAbosEssai == 0){
			jQuery(".bloc-essai").addClass("hide");
		}


		for(var i = 0; i< nbAbosEssai ; i++){


			/*
			 * 
			 * 
			 * total
			 * coupon_details
			 * coupon_row
			 * 
			 */

			abo = abosEssai[i];




			rowEssai = jQuery(".row-essai-template").clone();
			rowEssai.insertAfter(".row-essai-template");
			rowEssai.removeClass("row-essai-template");

			console.log("abo")
			console.log(abo)
			rowEssai.find(".prenom-eleve").html(abo.eleve.prenom.capitalize());
			rowEssai.find(".nom-formule").html(abo.formule.formule.split("|")[0]. concat(" - ",abo.eleve.niveau.niveau));

			montantAbo = abo.plan.tarif;
			montant = montantAbo

			if(abo.coupon){

				coupon = abo.coupon
				jQuery(rowEssai).find("#coupon_name").text(coupon.name);
				jQuery(rowEssai).find("#coupon_description").text(coupon.description);

				montant = montant * (1-coupon.pct_off/100)


			}else{
				jQuery(rowEssai).find("#coupon_row").addClass('hide');

			}
			jQuery(rowEssai).find("#total").text(montant);
			jQuery(rowEssai).find("#montant").text(montantAbo);


			if(abo.first_prof_assigned != null && abo.first_prof_assigned != false){

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

//		var handler = StripeCheckout.configure({
//		key: publicStripeKey,
//		image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
//		locale: 'auto',
//		allowRememberMe: false,
//		token: function(token) {

//		aboClique = abosEssai[indiceAbo];

//		createSubscription(aboClique.ref_abonnement, token.id, testMode);

//		}
//		});

		// pour attacher la popup de paiement cb au bouton payer 
		jQuery('.payer').click(function(e) {

			indiceAbo = jQuery(this).parents(".row-essai").find(".ref-abo").val();
			aboClique = abosEssai[indiceAbo];


			// faire appel ajax pour création de de la session

			console.log("indiceAbo:");
			console.log(indiceAbo);

			abo = aboClique;

			console.log('abo pour le paiement');	
			console.log(abo);


			jQuery("#loading_screen").removeClass("hide");
			jQuery(".hide_loading").addClass("hide");

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action': 'ajaxCreateCheckoutSession',
						'ref_abonnement' : abo.ref_abonnement,
						'testMode' : testMode
					}
			)
			.done(function(retour) {
				if(retour.error){
					showMessage('Ooops : il y a eu un problème : '.concat(retour.message,'. Veuillez réessayer ou contacter l\'équipe.'));	
					ajaxEnCours  --;
					if(ajaxEnCours == 0){
						jQuery(".hide_loading").removeClass("hide");
						jQuery("#loading_screen").addClass("hide");
					}



				}else{

					var stripe = Stripe(publicStripeKey);

					stripe.redirectToCheckout({
						// Make the id field from the Checkout Session creation API response
						// available to this file, so you can provide it as parameter here
						// instead of the {{CHECKOUT_SESSION_ID}} placeholder.
						sessionId: retour.session_id
					}).then(function (result) {

						result.error.message = 'Oops : il y a eu un problème. Veuillez réessayer ou contacter l\'équipe. ';


						// If `redirectToCheckout` fails due to a browser or network
						// error, display the localized error message to your customer
						// using `result.error.message`.
					});


				}
			})
			.fail(function() {
				showMessage('Oops : il y a eu un problème. Veuillez réessayer ou contacter l\'équipe. ');
				ajaxEnCours  --;
				if(ajaxEnCours == 0){
					jQuery(".hide_loading").removeClass("hide");
					jQuery("#loading_screen").addClass("hide");
				}
			});



			/** fin formulaire de paiement stripe **/

		});
	});









	/* lignes des abonnements */

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


			rowAbo.find(".prenom-eleve").html(abo.eleve.prenom.capitalize());
			rowAbo.find(".nom-formule").html(abo.formule.formule);

			rowAbo.find(".prof").html(abo.prof.prenom.concat(" ",abo.prof.nom));
			rowAbo.find(".adresse-prof").html(abo.prof.email_stp);

			rowAbo.find(".ref-abo").val(i);

			rowAbo.removeClass("hide");


		}


		/** début de mise à jour de de cb **/




		waitForEl(".updatecb", function() {


			jQuery('.updatecb').click(function(e) {


				if(sessionidCb === "false"){
					showMessage("Veuillez d'abord faire un premier paiement avant de mettre à jour votre carte")
				}else{

					var stripe = Stripe(publicStripeKey);


					stripe.redirectToCheckout({

						sessionId: sessionidCb
					}).then(function (result) {
						result.error.message = 'Il y a une erreur. Veuillez recommencer.'
					});

				}


			});
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


			rowAbo.find(".prenom-eleve").html(abo.eleve.prenom.capitalize());
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


	waitForEl(".row-eleve", function() {


		// pour charger et remplir les lignes d'abonnement
		nbEleves = eleves.length;

		console.log(nbEleves)
		console.log(eleves)


		for(var i = 0; i< nbEleves ; i++){

			eleve = eleves[i];

			rowEleve = jQuery(".row-eleve-template").clone();
			rowEleve.insertAfter(".row-eleve-template");
			rowEleve.removeClass(".row-eleve-template");

			rowEleve.find(".famille-prenom-eleve").html(eleve.prenom.capitalize().concat(' ',eleve.nom.capitalize()));
			rowEleve.find(".famille-classe-eleve").html(eleve.niveau.niveau);
			rowEleve.find(".famille-eleve-email").html(eleve.email);
			rowEleve.find(".famille-eleve-telephone").html(eleve.telephone);



			rowEleve.removeClass("hide");


		}

//		// pour attacher la popup d'annulation ou d'interruption au bouton d'annulation
//		jQuery('.pause').click(function(e) {



//		console.log("fr");

//		e.preventDefault();
//		});

//		jQuery('.arreter').click(function(e) {

//		indiceAbo = jQuery(this).parents(".row-abo").find(".ref-abo").val();
//		jQuery("#popmake-".concat(popupArret," .ref-abo")).val(indiceAbo);



//		console.log("fr");

//		e.preventDefault();
//		});





	});


	function resilierAbonnement(abo){


		jQuery("#loading_screen").removeClass("hide");
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
					jQuery("#loading_screen").addClass("hide");
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
				jQuery("#loading_screen").addClass("hide");
			}
		});
	}




	function createSubscription(refAbonnement, source, testMode){

		jQuery("#loading_screen").removeClass("hide");
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
					jQuery("#loading_screen").addClass("hide");
				}
			}else{
				redirectTo('dashboard-eleve','Félicitations : le paiement est passé. L\'inscription est bien validé.');
			}
		})
		.fail(function(retour) {
			showMessage('Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l\'équipe. ');
			ajaxEnCours--;
			if(ajaxEnCours == 0){
				jQuery(".hide_loading").removeClass("hide");
				jQuery("#loading_screen").addClass("hide");
			}
		});
	}

	function updateCb(refCompte, source, testMode){

		jQuery("#loading_screen").removeClass("hide");
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
				jQuery("#loading_screen").addClass("hide");
			}
		});
	}


	function goToTab(num){


		selector = ".wpb_tabs_nav a:nth(".concat(num,")");



		setTimeout(function(){

			console.log(selector);	
			jQuery(selector).click();

			tabActivated = jQuery(selector).hasClass("active-tab");
			if(!tabActivated){
				console.log("retry")
				goToTab(num);
			}

		}, 500); 




//		window.addEventListener('load', function () {

//		});




	}



});