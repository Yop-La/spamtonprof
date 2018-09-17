
var montant = 20; // le montant du checkout par défaut
var emailCheckout = "alexandre@spamtonprof.com" ; // l'adresse mail par défaut du checkout
var refCompteCheckout = 9999999999 ; // la ref compte par défaut du checkout
var planStripe = "" ; // le plan stripe par défaut du checkout
var planPaypal = "" ; // le plan paypal par défaut du checkout
var prenomEleve = "Cannelle" ; // le prénom retourné après soumisssion du checkout
var clientToken;
var message;


function reloadPage(info = ""){
	if(info != ""){
		jQuery("#info").val(info);
		jQuery("#hidden-form").submit();
	}
}



function doStripeSubscription(refCompteCheckout, source, emailCheckout, planStripe, testMode, prenomEleve,ajaxurl){
	jQuery.post(
			ajaxurl,
			{
				'action': 'ajaxStripeDoSubscription',
				'ref_compte' : refCompteCheckout,
				'source': source,
				'email_parent' : emailCheckout,
				'plan_stripe' : planStripe,
				'testMode' : testMode
			}
	)
	.done(function(retour) {
		if(retour == "done"){
			message = 'Félicitations : le paiement est passé. <span class = "prenom"> '.concat(prenomEleve,' </span> est bien inscrit(e). Y a t&apos;il une autre inscription à faire ?');
		}else{
			message = 'Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l&apos;équipe. ';
		}
	})
	.fail(function() {
		message = 'Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l&apos;équipe. ';
	})
	.always(function() {
		reloadPage(message);
	});
}


//Create a new object for custom validation of a custom field.
var myCustomFieldController = Marionette.Object.extend( {
	initialize: function() {

		// on the Field's model value change...
		var fieldsChannel = Backbone.Radio.channel( 'fields' );
		this.listenTo( fieldsChannel, 'change:modelValue', this.validateOnChange );


		// On the Form Submission's field validaiton...
		var submitChannel = Backbone.Radio.channel( 'submit' );
		this.listenTo( submitChannel, 'validate:field', this.validateSubmit );

	},

	validateOnChange: function( model ) {
		var modelID       = model.get( 'id' );
		var errorID       = 'custom-field-error';
		var fieldsChannel = Backbone.Radio.channel( 'fields' );

		// Add Error
		fieldsChannel.request( 'remove:error', modelID, errorID );

		if(model.get( 'type' ) == "email" && model.get( 'id' ) == "824" && isEmail(model.get( 'value' ).trim())){
			var jQueryloading = jQuery('.loadingDiv').show();
			var jQueryformLoadForm = jQuery('#nf-form-48-cont').hide();
			var jQueryvalider = jQuery('.valider').hide();
			var jQueryformPaiement = jQuery('#nf-form-49-cont');
			var jQuerypayer = jQuery('.payer').hide();

			var choix1 = jQuery(".choix-formule li:nth-of-type(1)").hide();
			var choix2 = jQuery(".choix-formule li:nth-of-type(2)").hide();
			var choix3 = jQuery(".choix-formule li:nth-of-type(3)").hide();
			var choix = [choix1,choix2,choix3];

			jQuery.post(
					ajaxurl,
					{
						'action': 'ajaxGetTrialAccountComplet',
						'email': model.get( 'value' ).trim()
					}
			)
			.done(function(comptes) {
				if(comptes.length == 0){
					console.log("pas de compte trouvé");
					/* afficher message pas de comptes - refaire la recherche */
					jQueryformLoadForm.show();
					jQueryvalider.show();
					var modelID       = model.get( 'id' );
					var errorID       = 'custom-field-error';
					var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
					var fieldsChannel = Backbone.Radio.channel( 'fields' );
					fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
					/* message pas de compte trouvé  - refaire la recherche */    
				}else{
					console.log(comptes);
					jQueryformPaiement.show();
					jQuerypayer.show();
					jQuery(".fields-to-hide").hide(); //cacher les champs adresse_mail, tarifs et ref plan paiement stripe
					var indiceCompte = 0;
					comptes.forEach(function(compte) {
						var choixCourant = choix[indiceCompte];
						indiceCompte = indiceCompte+1;

						jQuerylabel = "<span class = 'nom-formule'> Formule " + compte.planPaiement.formule.formule + "</span><br>";
						jQuerylabel += "<span class = 'details-formule'>Pour " + compte.eleve.prenom + " - " + compte.eleve.classe + "<br>";
						jQuerylabel += compte.planPaiement.tarif + " € par semaine</span>"; 


						jQuery(choixCourant).find("label").html(jQuerylabel);
						jQuery(choixCourant).find("input").attr("email-value",compte.proche.adresse_mail);
						jQuery(choixCourant).find("input").attr("tarif",compte.planPaiement.tarif);
						if(testMode == "true"){
							jQuery(choixCourant).find("input").attr("ref_plan_stripe",compte.planPaiement.ref_plan_stripe_test);
							jQuery(choixCourant).find("input").attr("ref_plan_paypal",compte.planPaiement.ref_paypal_test);							
						}else{
							jQuery(choixCourant).find("input").attr("ref_plan_stripe",compte.planPaiement.ref_plan_stripe);
							jQuery(choixCourant).find("input").attr("ref_plan_paypal",compte.planPaiement.ref_paypal_prod);							
						}

						jQuery(choixCourant).find("input").attr("ref_compte",compte.ref_compte);
						jQuery(choixCourant).find("input").attr("prenom_eleve",compte.eleve.prenom);
						jQuery(choixCourant).find("input").val(compte.ref_compte);


						jQuery(choixCourant).find("input").change(
								function(){
									if (jQuery(this).is(':checked')) {
										montant = jQuery(this).attr('tarif');
										emailCheckout = jQuery(this).attr('email-value');
										refCompteCheckout = jQuery(this).attr('ref_compte');
										planStripe = jQuery(this).attr('ref_plan_stripe');
										planPaypal = jQuery(this).attr('ref_plan_paypal');
										prenomEleve = jQuery(this).attr('prenom_eleve');
									}
								});


						if(indiceCompte == 1){
							//initialisation des choix de compte et de moyen de paiement du formulaire
							jQuery(choixCourant).find("input").change();
							jQuery(".choix-paiement li:nth-of-type(1)").find("input").prop("checked", true);

						}

						choixCourant.show();
					});

				}

			})
			.fail(function() {
				console.log("fail");
				jQueryformLoadForm.show();
				jQueryvalider.show();
				var modelID       = model.get( 'id' );
				var errorID       = 'custom-field-error';
				var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
				var fieldsChannel = Backbone.Radio.channel( 'fields' );
				fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
				/* message pas de compte trouvé - refaire la recherche */
			})
			.always(function() {
				jQueryloading.hide();
			});

		}
	}
});


jQuery( document ).ready( function( $ ) {
	console.log("js chargé");

	var jQueryloading = jQuery('.loadingDiv').hide();
	var jQueryformLoadForm = jQuery('#nf-form-48-cont');
	var jQueryformPaiement = jQuery('#nf-form-49-cont').hide();
	jQuery(".payer").hide();
	jQuery('#paypal-button').hide();
	// jQuery('#paypal-button').hide();
	// mettre le paiement cb par défaut
	waitForEl(".payer", function() {    
		setPaiementCb();
	});


	new myCustomFieldController(); // pour contrôler le champs de saisie de l'email

	waitForEl(".choix-paiement li", function() {
		var choixPaiement = jQuery(".choix-paiement li");
		var choixCb = choixPaiement[0];
		var choixPaypal = choixPaiement[1];
		var choixSEPA = choixPaiement[2];
		
		// pour cacher le choix SEPA avant d'avoir l'idenfiant créancer todostp se procuer l'identifiant de créancier SEPA et enelever ce hide
		jQuery(choixSEPA).hide();

		jQuery(choixCb).click(function(){
			jQuery(".payer").show();
			jQuery('#paypal-button').hide();
			jQuery( ".payer").unbind( "click" );
			setPaiementCb();
		});


		jQuery(choixPaypal).click(function(){
			jQuery(".payer").hide();
			jQuery('#paypal-button').show();
		});

		jQuery(choixSEPA).click(function(){
			jQuery(".payer").show();
			jQuery('#paypal-button').hide();
			jQuery( ".payer").unbind( "click" );
			setPaiementSEPA();
		}); 
	});

	/** début paiement paypal **/


	waitForEl("#paypal-button", function() {

		if(testMode == "true"){
			mode = "sandbox";
		}else{
			mode = "production";
		}

		paypal.Button.render({

			env: mode, 

			commit: true, // Show a 'Pay Now' button,
			
			locale: 'fr_FR',
			
		    style: {
		        size: 'responsive',
		        color: 'gold',
		        shape: 'pill',
		        label: 'checkout'
		    },

			payment: function() {
				return new paypal.Promise(function(resolve, reject) {

					jQuery.post(
							ajaxurl,
							{
								'action': 'getBillingAgreement',
								'planPaypal' : planPaypal,
								'testMode' : testMode
							}
					)
					.done(function(data) {
						resolve(data); 
					})
					.fail(function(err)  {
						// todostp mieux gérer l'erreur
						console.log("error dans payement");
						message = 'Oops : il y a eu un problème avec le paiement paypal. Veuillez réessayer ou contacter l&apos;équipe. ';
						reloadPage(message);
					})
				});
			},

			onAuthorize: function(data) {
				paiementFait = false;
				console.log("data autorisation :");
				var paiementToken = data.paymentToken;
				
				// pour afficher la page de chargement
				var jQueryloading = jQuery('.loadingDiv').show();
				var jQueryformLoadForm = jQuery('#nf-form-48-cont').hide();
				var jQueryformPaiement = jQuery('#nf-form-49-cont').hide();
				jQuery('#paypal-button').hide();
				var jQuerypayer = jQuery('.payer').hide();
				
				jQuery.post(
						ajaxurl,
						{
							'action': 'exeBillingAgreement',
							'paiementToken': paiementToken,
							'testMode' : testMode,
							'refCompteCheckout' : refCompteCheckout,
							'emailCheckout' : emailCheckout
						}
				)
				.done(function(data) {
					
					if(data == "fail"){
						paiementFait = false;
					
					}else{
						paiementFait = true;
					}
					
				})
				.fail(function(err)  {
					paiementFait = false;
				})
				.always(function(){
					if(paiementFait){

						jQuery.post(
								ajaxurl,
								{
									'action': 'updateAccountAndGrAfterSubscription',
									'refCompteCheckout': refCompteCheckout,
								}
						)
						.done(function(data) {
							console.log("done sub");
							message = 'Félicitations : le paiement est passé. <span class = "prenom"> '.concat(prenomEleve,' </span> est bien inscrit(e). Y a t&apos;il une autre inscription à faire ?');
						})
						.fail(function(err)  {
							
							message = 'Oops : il y a eu un problème. Le paiement est bien passé mais pas l&apos;inscription. Veuillez contacter l&apos;équipe. ';
							console.log("fail sub");
							console.log(err);
						})
						.always(function(){
							reloadPage(message);
						});
					}else{
						
						message = 'Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l&apos;équipe. ';
						reloadPage(message);
						
					}

				});




			},

			onError: function(err) {
				message = 'Oops : il y a eu un problème avec le paiement. Veuillez réessayer ou contacter l&apos;équipe. ';
				reloadPage(message);
			}

		}, '#paypal-button');
	});

	/** fin paiement paypal **/

	/** début payement IBAN **/

	// pour attacher la popup de paiement SEPA au bouton valider 
	function setPaiementSEPA(){ 
		jQuery('.payer').click(function(e) {
			jQuery(".email-sepa").html(emailCheckout);
			jQuery(".payer-sepa i").html('Payer '.concat(montant,' €'));
			jQuery(".abo-sepa").html('Abonnement de '.concat(montant,' € par semaine'));
			PUM.open(17218);

			jQuery("#pum-17218").on('pumAfterClose',function(){
				jQuery(".payer-sepa i").removeClass("fa fa-spinner fa-spin fas fa-check");
				jQuery(".payer-sepa i").html('Payer '.concat(montant,' €'));
				jQuery("#prenom").val("");
				jQuery("#nom").val("");
				jQuery("#iban").val("");
			});
		});
	}



	waitForEl(".payer-sepa", function() {




		jQuery('.payer-sepa').click(function(e){


			jQuery(".payer-sepa i").addClass("fa fa-spinner fa-spin");
			jQuery(".payer-sepa i").html("");

			var stripe = Stripe('pk_test_QyCZobSFqpynjCtZNqdYDVie');
			stripe.createSource({
				type: 'sepa_debit',
				sepa_debit: {
					iban: jQuery("#iban").val(),
				},
				currency: 'eur',
				owner: {
					name: jQuery("#prenom").val().concat(" ").concat(jQuery("#nom").val()),
				},
			}).then(function(result) {
				var errorFields = false;

				if(result.error != null){
					errorFields = true;
					jQuery(".iban-label-sepa span").attr('style', 'color: red !important; font-weight: 900');
				}else{  
					jQuery(".iban-label-sepa span").attr('style', 'color: #ff7400 !important; font-weight: 100');
				}    

				if(jQuery("#prenom").val() == ""){
					errorFields =true;  
					jQuery(".prenom-label-sepa span").attr('style', 'color: red !important; font-weight: 900');
				}else{
					jQuery(".prenom-label-sepa span").attr('style', 'color: #ff7400 !important; font-weight: 100');
				}

				if(jQuery("#nom").val() == ""){
					errorFields =true;
					jQuery(".nom-label-sepa span").attr('style', 'color: red !important; font-weight: 900');
				}else{
					jQuery(".nom-label-sepa span").attr('style', 'color: #ff7400 !important; font-weight: 100');
				}        

				if(errorFields){
					jQuery( "#popmake-17218" ).effect( "shake" );
					jQuery(".payer-sepa i").removeClass("fa fa-spinner fa-spin");
					jQuery(".payer-sepa i").html('Payer '.concat(montant,' €'));
				}else{
					jQuery(".payer-sepa i").removeClass("fa fa-spinner fa-spin").addClass("fas fa-check");
					jQuery(".payer-sepa").attr('style', 'background-color: #81d742 !important');
					jQuery(".payer-sepa i").attr('style', 'font-size: 30px !important');
					jQuery(".payer-sepa i").html("");
					jQuery( "#popmake-17218" ).hide( "drop", { direction: "up" }, 1000 );
					PUM.close(17218);

					var jQueryloading = jQuery('.loadingDiv').show();
					var jQueryformLoadForm = jQuery('#nf-form-48-cont').hide();
					var jQueryformPaiement = jQuery('#nf-form-49-cont').hide();
					var jQuerypayer = jQuery('.payer').hide();
					doStripeSubscription(refCompteCheckout, result.source.id, emailCheckout, planStripe, testMode, prenomEleve,ajaxurl);

				}


			});




		});
	});



	/** fin formulaire IBAN **/

	/** début formulaire de paiement stripe **/

	var handler = StripeCheckout.configure({
		key: publicStripeKey,
		image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
		locale: 'auto',
		allowRememberMe: false,
		token: function(token) {

			var jQueryloading = jQuery('.loadingDiv').show();
			var jQueryformLoadForm = jQuery('#nf-form-48-cont').hide();
			var jQueryformPaiement = jQuery('#nf-form-49-cont').hide();
			var jQuerypayer = jQuery('.payer').hide();

			doStripeSubscription(refCompteCheckout, token.id, emailCheckout, planStripe, testMode, prenomEleve,ajaxurl);

		}
	});

	// pour attacher la popup de paiement cb au bouton valider 
	function setPaiementCb(){ 
		jQuery('.payer').click(function(e) {
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
	}

	// Close Checkout on page navigation:
	window.addEventListener('popstate', function() {
		handler.close();
	});

	/** fin formulaire de paiement stripe **/


});