jQuery(document).ready(function ($) {


	var ajaxEnCours = 0;
	var idPaiementStage = 86;
	var token_stripe = false;
	var plan_choisi ;
	var plan_loadding_done = false

	var nameSpaceController = Marionette.Object.extend( {

		initialize: function() {
			this.listenTo( nfRadio.channel( 'form' ), 'render:view', this.doCustomStuff );
		},

		doCustomStuff: function( view ) {

			if(!plan_loadding_done){
				plan_loadding_done = true;
				console.log('plan')
				console.log(formule.plans);

				options = nfRadio.channel('form-86').request('get:form').getFieldByKey('plan_1559631886435' ).get('options');



				order_option = -1
				formule.plans.forEach(function(plan) {

					new_option = clone(options[0])

					if(order_option == -1){
						order_option = new_option.order
					}
					order_option = order_option + 1


					new_option.order = order_option
					new_option.value = plan.ref_plan
					new_option.label = plan.nom

					options.push(new_option)

				});

				nfRadio.channel('form-86').request('get:form').getFieldByKey('plan_1559631886435' ).set('options',options)

				nfRadio.channel('form-86').request('get:form').getFieldByKey('plan_1559631886435' ).trigger('reRender');

			}


		},

	});

	//Create a new object for custom validation of a custom field.
	var myCustomFieldController = Marionette.Object.extend( {

		initialize: function() {

			// Listen to the render:view event for a field type. Example: Textbox field.
			this.listenTo( nfRadio.channel( 'listselect' ), 'change:modelValue', this.renderViewListSelect );

		},

		renderViewListSelect: function( view ) {


			value = view.attributes.value;
			label = view.attributes.label;


			if(label == 'plan' && value != ''){

				new_options = [];


				ref_plan = value

				plans = formule.plans;
				plans.forEach(function(plan){
					if(plan.ref_plan == ref_plan){
						plan_choisi = plan
					}
				})

				waitForEl(".label_plan", function () {

					jQuery('.label_plan').text(plan_choisi.label_installment)

				});


				options = nfRadio.channel('form-86').request('get:form').getFieldByKey('date_stage_1559397531716' ).get('options');

				new_options.push(options[0]);

				order_option = -1
				dates_formule.forEach(function(date_formule){

					if(date_formule.ref_plan == value){

						new_option = clone(new_options[0])

						if(order_option == -1){
							order_option = new_option.order
						}
						order_option = order_option + 1

						new_option.order = order_option
						new_option.value = date_formule.ref_date_formule
						new_option.label = date_formule.libelle

						new_options.push(new_option)

					}
				});

				console.log('new_options');
				console.log(new_options);

				nfRadio.channel('form-86').request('get:form').getFieldByKey('date_stage_1559397531716' ).set('options',new_options)

				nfRadio.channel('form-86').request('get:form').getFieldByKey('date_stage_1559397531716' ).trigger('reRender');



			}




		}

	});



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

			hideMessage();

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
						redirect_url = "stage-ete/paiement?ref_formule=".concat(formule.ref_formule)

						if(error){



							ajaxEnCours--;
							if(ajaxEnCours == 0){


								if(message == "compte_existe_deja"){

									redirectTo(redirect_url ,"Oops : vous avez déjà un compte. Connectez vous pour faire une autre inscription ! " );
								}else if(message == "essai_deja_fait"){
									redirectTo(redirect_url ,"Oops : vous avez déjà fait un essai pour cette matière ! Venez en parler avec nous." );
								}else if(message == "deja_2_essai"){

									redirectTo(redirect_url ,"Oops : il y a déjà 2 essais en cours ! Revenez quand au moins un essai sera fini" );
								}else if(message == "eleve_deja_essai"){

									redirectTo(redirect_url ,"Oops : tu es déjà entrain de faire un essai. Reviens quand tu auras fini." );
								}else if(message == "eleve_existe_deja"){

									redirectTo(redirect_url ,"Oops : l'élève renseigné a déjà un compte. Sélectionnez le lors de l'inscription." );
								}else if(message == "parent_pas_eleve"){

									redirectTo(redirect_url ,"Oops : un parent ne peux pas s'inscrire en tant qu'élève. Venez en discuter avec nous." );
								}else if(message == "erreur_paiement"){

									redirectTo(redirect_url ,"Oops : Le paiement n'est pas passé. Veuillez réessayer ou venez en discuter avec nous." );
								}								

							}

						}else{


							redirectTo(redirect_url  ,"Félicitations. On vient d'envoyer un email avec tout les détails." );



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
					})
					.always(function() {
						if(ajaxEnCours == 0){
							jQuery("#loading_screen").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});


		}
	});

	// pour afficher la popup de paiement cb
	function popCheckout(formule) {




		montant = plan_choisi.tarif



		description = plan_choisi.label_installment

		emailCheckout = "alexandre@spamtonprof.com";


		// user loggé
		if (typeof userType !== 'undefined') {

			if (typeof proche !== 'undefined') {
				emailCheckout = proche.email;
			}else{
				emailCheckout = eleves[0].email
			}
		}else{

			if(jQuery(".parent_required_checkbox").is(':checked')){
				emailCheckout = jQuery(".mail_responsable").val()
			}else{
				emailCheckout = jQuery(".email_eleve").val();
			}
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


	// Close Checkout on page navigation:
	window.addEventListener('popstate', function() {
		handler.close();
	});

	/** fin formulaire de paiement stripe **/




	formInit();

	new nameSpaceController();

	new myCustomFieldController();

	new mySubmitController();



});