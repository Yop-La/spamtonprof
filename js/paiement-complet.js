
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
		$("#info").val(info);
		$("#hidden-form").submit();
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
			var $loading = $('.loadingDiv').show();
			var $formLoadForm = $('#nf-form-48-cont').hide();
			var $valider = $('.valider').hide();
			var $formPaiement = $('#nf-form-49-cont');
			var $payer = $('.payer').hide();

			var choix1 = $(".choix-formule li:nth-of-type(1)").hide();
			var choix2 = $(".choix-formule li:nth-of-type(2)").hide();
			var choix3 = $(".choix-formule li:nth-of-type(3)").hide();
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
					$formLoadForm.show();
					$valider.show();
					var modelID       = model.get( 'id' );
					var errorID       = 'custom-field-error';
					var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
					var fieldsChannel = Backbone.Radio.channel( 'fields' );
					fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
					/* message pas de compte trouvé  - refaire la recherche */    
				}else{
					console.log(comptes);
					$formPaiement.show();
					$payer.show();
					$(".fields-to-hide").hide(); //cacher les champs adresse_mail, tarifs et ref plan paiement stripe
					var indiceCompte = 0;
					comptes.forEach(function(compte) {
						var choixCourant = choix[indiceCompte];
						indiceCompte = indiceCompte+1;

						$label = "<span class = 'nom-formule'> Formule " + compte.planPaiement.formule.formule + "</span><br>";
						$label += "<span class = 'details-formule'>Pour " + compte.eleve.prenom + " - " + compte.eleve.classe + "<br>";
						$label += compte.planPaiement.tarif + " € par semaine</span>"; 


						$(choixCourant).find("label").html($label);
						$(choixCourant).find("input").attr("email-value",compte.proche.adresse_mail);
						$(choixCourant).find("input").attr("tarif",compte.planPaiement.tarif);
						if(testMode == "true"){
							$(choixCourant).find("input").attr("ref_plan_stripe",compte.planPaiement.ref_plan_stripe_test);
							$(choixCourant).find("input").attr("ref_plan_paypal",compte.planPaiement.ref_paypal_test);							
						}else{
							$(choixCourant).find("input").attr("ref_plan_stripe",compte.planPaiement.ref_plan_stripe);
							$(choixCourant).find("input").attr("ref_plan_paypal",compte.planPaiement.ref_paypal_prod);							
						}

						$(choixCourant).find("input").attr("ref_compte",compte.ref_compte);
						$(choixCourant).find("input").attr("prenom_eleve",compte.eleve.prenom);
						$(choixCourant).find("input").val(compte.ref_compte);


						$(choixCourant).find("input").change(
								function(){
									if ($(this).is(':checked')) {
										montant = $(this).attr('tarif');
										emailCheckout = $(this).attr('email-value');
										refCompteCheckout = $(this).attr('ref_compte');
										planStripe = $(this).attr('ref_plan_stripe');
										planPaypal = $(this).attr('ref_plan_paypal');
										prenomEleve = $(this).attr('prenom_eleve');
									}
								});


						if(indiceCompte == 1){
							//initialisation des choix de compte et de moyen de paiement du formulaire
							$(choixCourant).find("input").change();
							$(".choix-paiement li:nth-of-type(1)").find("input").prop("checked", true);

						}

						choixCourant.show();
					});

				}

			})
			.fail(function() {
				console.log("fail");
				$formLoadForm.show();
				$valider.show();
				var modelID       = model.get( 'id' );
				var errorID       = 'custom-field-error';
				var errorMessage  = 'Aucun compte n’a été trouvé. Veuillez refaire la recherche ou contacter notre équipe.';
				var fieldsChannel = Backbone.Radio.channel( 'fields' );
				fieldsChannel.request( 'add:error', modelID, errorID, errorMessage );
				/* message pas de compte trouvé - refaire la recherche */
			})
			.always(function() {
				$loading.hide();
			});

		}
	}
});


jQuery( document ).ready( function( $ ) {
	console.log("js chargé");

	var $loading = $('.loadingDiv').hide();
	var $formLoadForm = $('#nf-form-48-cont');
	var $formPaiement = $('#nf-form-49-cont').hide();
	$(".payer").hide();
	$('#paypal-button').hide();
	// $('#paypal-button').hide();
	// mettre le paiement cb par défaut
	waitForEl(".payer", function() {    
		setPaiementCb();
	});


	new myCustomFieldController(); // pour contrôler le champs de saisie de l'email

	waitForEl(".choix-paiement li", function() {
		var choixPaiement = $(".choix-paiement li");
		var choixCb = choixPaiement[0];
		var choixPaypal = choixPaiement[1];
		var choixSEPA = choixPaiement[2];
		
		// pour cacher le choix SEPA avant d'avoir l'idenfiant créancer todostp se procuer l'identifiant de créancier SEPA et enelever ce hide
		$(choixSEPA).hide();

		$(choixCb).click(function(){
			$(".payer").show();
			$('#paypal-button').hide();
			$( ".payer").unbind( "click" );
			setPaiementCb();
		});


		$(choixPaypal).click(function(){
			$(".payer").hide();
			$('#paypal-button').show();
		});

		$(choixSEPA).click(function(){
			$(".payer").show();
			$('#paypal-button').hide();
			$( ".payer").unbind( "click" );
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
				var $loading = $('.loadingDiv').show();
				var $formLoadForm = $('#nf-form-48-cont').hide();
				var $formPaiement = $('#nf-form-49-cont').hide();
				$('#paypal-button').hide();
				var $payer = $('.payer').hide();
				
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
		$('.payer').click(function(e) {
			$(".email-sepa").html(emailCheckout);
			$(".payer-sepa i").html('Payer '.concat(montant,' €'));
			$(".abo-sepa").html('Abonnement de '.concat(montant,' € par semaine'));
			PUM.open(17218);

			jQuery("#pum-17218").on('pumAfterClose',function(){
				$(".payer-sepa i").removeClass("fa fa-spinner fa-spin fas fa-check");
				$(".payer-sepa i").html('Payer '.concat(montant,' €'));
				$("#prenom").val("");
				$("#nom").val("");
				$("#iban").val("");
			});
		});
	}



	waitForEl(".payer-sepa", function() {




		$('.payer-sepa').click(function(e){


			$(".payer-sepa i").addClass("fa fa-spinner fa-spin");
			$(".payer-sepa i").html("");

			var stripe = Stripe('pk_test_QyCZobSFqpynjCtZNqdYDVie');
			stripe.createSource({
				type: 'sepa_debit',
				sepa_debit: {
					iban: $("#iban").val(),
				},
				currency: 'eur',
				owner: {
					name: $("#prenom").val().concat(" ").concat($("#nom").val()),
				},
			}).then(function(result) {
				var errorFields = false;

				if(result.error != null){
					errorFields = true;
					$(".iban-label-sepa span").attr('style', 'color: red !important; font-weight: 900');
				}else{  
					$(".iban-label-sepa span").attr('style', 'color: #ff7400 !important; font-weight: 100');
				}    

				if($("#prenom").val() == ""){
					errorFields =true;  
					$(".prenom-label-sepa span").attr('style', 'color: red !important; font-weight: 900');
				}else{
					$(".prenom-label-sepa span").attr('style', 'color: #ff7400 !important; font-weight: 100');
				}

				if($("#nom").val() == ""){
					errorFields =true;
					$(".nom-label-sepa span").attr('style', 'color: red !important; font-weight: 900');
				}else{
					$(".nom-label-sepa span").attr('style', 'color: #ff7400 !important; font-weight: 100');
				}        

				if(errorFields){
					$( "#popmake-17218" ).effect( "shake" );
					$(".payer-sepa i").removeClass("fa fa-spinner fa-spin");
					$(".payer-sepa i").html('Payer '.concat(montant,' €'));
				}else{
					$(".payer-sepa i").removeClass("fa fa-spinner fa-spin").addClass("fas fa-check");
					$(".payer-sepa").attr('style', 'background-color: #81d742 !important');
					$(".payer-sepa i").attr('style', 'font-size: 30px !important');
					$(".payer-sepa i").html("");
					$( "#popmake-17218" ).hide( "drop", { direction: "up" }, 1000 );
					PUM.close(17218);

					var $loading = $('.loadingDiv').show();
					var $formLoadForm = $('#nf-form-48-cont').hide();
					var $formPaiement = $('#nf-form-49-cont').hide();
					var $payer = $('.payer').hide();
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

			var $loading = $('.loadingDiv').show();
			var $formLoadForm = $('#nf-form-48-cont').hide();
			var $formPaiement = $('#nf-form-49-cont').hide();
			var $payer = $('.payer').hide();

			doStripeSubscription(refCompteCheckout, token.id, emailCheckout, planStripe, testMode, prenomEleve,ajaxurl);

		}
	});

	// pour attacher la popup de paiement cb au bouton valider 
	function setPaiementCb(){ 
		$('.payer').click(function(e) {
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