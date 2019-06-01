jQuery(document).ready(function ($) {


	var ajaxEnCours = 0;
	var idPaiementStage = 86;
	var token_stripe = false;


	var mySubmitController = Marionette.Object.extend( {

		initialize: function() {
			this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
		},

		actionSubmit: function( response ) {



			if(response.data.form_id == idPaiementStage){



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
				popCheckout(formule);


			}			
		}

	});




	function formInit() {





		waitForEl(".qui-eleve", function () {

			jQuery('.qui-eleve').text("Qui va faire le stage ?");

		});

		if (isLogged != "true" || isAdmin == "true") {

			waitForEl(".prospect_checkbox", function () {

				jQuery('.prospect_checkbox').prop('checked', true).change()

			});

			waitForEl(".parent_required_checkbox", function () {

				$('.parent_required_checkbox').prop('checked', true).change()

			});            

		} else {
			waitForEl(".prospect_checkbox", function () {

				jQuery('.prospect_checkbox').prop('checked', false).change()

			});



		}


		waitForEl(".ref_formule", function () {

			$('.ref_formule').val(formule.ref_formule);

		});

		waitForEl(".js-example-basic-single", function () {

			$('.js-example-basic-single').select2();

		});
	}

	/** début formulaire de paiement stripe **/

	var handler = StripeCheckout.configure({
		key: publicStripeKey,
		image: 'https://spamtonprof.com/wp-content/uploads/2018/03/logo-stripe.png',
		locale: 'auto',
		allowRememberMe: false,
		token: function(token) {

			

			jQuery("#loading_screen").removeClass("hide");
			jQuery(".content").addClass("hide");
			
			console.log("token stripe");
			console.log(token);



			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'paiement_inscription',
						'fields' : JSON.stringify(champs),
						'token_stripe' : token.id,
						'test_mode' : testMode
					})
					.done(function(retour){

						console.log(retour);

						error = retour.error;
						message = retour.message;

						if(error){


							ajaxEnCours--;
							if(ajaxEnCours == 0){



								if(message == "compte_existe_deja"){

									redirectTo("semaine-decouverte" ,"Oops : vous avez déjà un compte. Connectez vous pour faire une autre inscription ! " );
								}else if(message == "essai_deja_fait"){
									redirectTo("semaine-decouverte" ,"Oops : vous avez déjà fait un essai pour cette matière ! Venez en parler avec nous." );
								}else if(message == "deja_2_essai"){

									redirectTo("semaine-decouverte" ,"Oops : il y a déjà 2 essais en cours ! Revenez quand au moins un essai sera fini" );
								}else if(message == "eleve_deja_essai"){

									redirectTo("semaine-decouverte" ,"Oops : tu es déjà entrain de faire un essai. Reviens quand tu auras fini." );
								}else if(message == "eleve_existe_deja"){

									redirectTo("semaine-decouverte" ,"Oops : l'élève renseigné a déjà un compte. Sélectionnez le lors de l'inscription." );
								}else if(message == "parent_pas_eleve"){

									redirectTo("semaine-decouverte" ,"Oops : un parent ne peux pas s'inscrire en tant qu'élève. Venez en discuter avec nous." );
								}

							}

						}else{

							showMessage("done")
//							redirectTo("remerciement-eleve" ,"Félicitations. Tu pourras démarrer la semaine de découverte dans 1 jour !" );



						}


					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loading_screen").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});



		}
	});

	// pour afficher la popup de paiement cb
	function popCheckout(formule) {


		console.log("valider clique")

		montant = formule.defaultPlan.tarif

		description = 'Paiement de '.concat(montant,' € en 2 fois')

		emailCheckout = "alexandre@spamtonprof.com";
		if(jQuery(".parent_required_checkbox").is(':checked')){
			emailCheckout = jQuery(".mail_responsable").val()
		}else{
			emailCheckout = jQuery(".email_eleve").val();
		}

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
	};


//	// Close Checkout on page navigation:
//	window.addEventListener('popstate', function() {
//	handler.close();
//	});

	/** fin formulaire de paiement stripe **/




	formInit();

	new mySubmitController();



});