
ajaxEnCours = 0;

idFormCmdSpamExpress = 91;

if(domain != 'spamtonprof'){
	idFormCmdSpamExpress = 91;

}


var myCustomFieldController = Marionette.Object.extend( {

	initialize: function() {

		// Listen to the render:view event for a field type. Example: Textbox field.
		this.listenTo( nfRadio.channel( 'fields' ), 'render:view', this.renderViewFields );


	},

	renderViewFields: function( view ) {


		var el = jQuery( view.el ).find( '.nf-element' );


		if (typeof cmd === 'undefined') {
			console.log(' cmd undefined')
			return
		}


		if(jQuery(el).hasClass('demande')){

			if (cmd.ref_offre != null) {

				console.log('demande');

				console.log(cmd.lead.email)
				jQuery(el).val( cmd.ref_offre ).trigger( 'change' );
			}

		}

		if(jQuery(el).hasClass('remarque')){
			if (cmd.remarque != null) {

				console.log('remarque');

				jQuery(el).val( cmd.remarque ).trigger( 'change' );
			}

		}
	}



});




var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {




		if(response.data.form_id == idFormCmdSpamExpress){

			showLoader()

			fields = response.data.fields;

			champs = {};

			Object.values(fields).forEach(function(field){


				champs[field.label] = field.submitted_value;
				if(typeof field.submitted_value =='undefined' || field.submitted_value == ""){
					champs[field.label] = field.value;
				}


			})

			console.log('champs')
			console.log(champs)




			params_ajax = {
				'action' : 'process_step_2',
				'fields' : JSON.stringify(champs),
				"ref_cmd_encrypted" : param_encrypted,
				"ref_cmd" : cmd.ref_cmd
			}

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					params_ajax
			)
			.done(function(retour){ 

				console.log("retour.error")
				console.log(retour)

				if(retour.error){
					hideLoader();
					showMessage(retour.message);
				}


				if(!retour.error){
					console.log("redirecting")
					console.log("step3-spam-express?param=".concat(param_encrypted));
					redirectTo("step3-spam-express?param=".concat(param_encrypted));
				}


			})
			.fail(function(err){
				console.log("erreur ajax");
				console.log(err);
				showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
				ajaxEnCours--;
				if(ajaxEnCours == 0){
					hideLoader();
				}

			}).always(function(err){

			});
		}

	}

});




jQuery( document ).ready( function( $ ) {

	main_url = "https://spamtonprof.com"
		if(domain=="localhost"){
			main_url = "http://localhost/spamtonprof"
		}


	jQuery('.previous a').attr("href",main_url.concat("/step1-spam-express/?param=",param_encrypted))

	new myCustomFieldController();


	new mySubmitController();


//	jQuery(".nf-response-msg").addClass("hide");



});