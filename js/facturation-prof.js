var ajaxEnCours = 0;

idFormFactuProf = 87;

jQuery(document).ready(function ($) {


	var mySubmitController = Marionette.Object.extend( {

		initialize: function() {
			this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
		},

		actionSubmit: function( response ) {



			if(response.data.form_id == idFormFactuProf){



				fields = response.data.fields;


				champs = {};

				Object.values(fields).forEach(function(field){

					champs[field.label] = field.submitted_value;
					if(typeof field.submitted_value =='undefined' || field.submitted_value == ""){
						champs[field.label] = field.value;
					}


				})

				console.log("champs");
				console.log(champs);

				jQuery("#loading_screen").removeClass("hide");
				jQuery(".content").addClass("hide");

				hideMessage();

				// soumission ajax des champs du form pour création inscription
				ajaxEnCours++;
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajax_facturation_par_prof',
							'fields' : JSON.stringify(champs),
							'test_mode' : testMode
						})
						.done(function(retour){

							console.log("retour");
							console.log(retour);

							error = retour.error;
							message = retour.error_type;

							if(error){



								ajaxEnCours--;
								if(ajaxEnCours == 0){


									if(message == "format_montant"){

										showMessage("Oops : le montant saisi est incorrect " );
									}

									if(message == "format_email"){


										showMessage("Oops : l'email saisi est incorrect " );

									}

									if(message == "format_objet"){

										showMessage("Oops : l'objet saisi est incorrect " );
									}
								}

							}else{

								redirectTo('facturation-prof'  ,"Super. La facture vient d'être envoyé !" );



							}


						})
						.fail(function(err){
							console.log("erreur ajax");
							console.log(err);
							showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								jQuery("#loading_screen").addClass("hide");
								jQuery(".content").removeClass("hide");
							}
						})
						.always(function() {
							if(ajaxEnCours == 0){
								jQuery("#loading_screen").addClass("hide");
								jQuery(".content").removeClass("hide");
							}
						});




			}			
		}

	});


	if(typeof loggedProf !== 'undefined'){

		waitForEl('.email_prof', function() {
			$('.email_prof').val(loggedProf.email_stp).change()

		});



	}else{

		$('#nf-form-87-cont').addClass("hide");
	}


	new mySubmitController();



});