
ajaxEnCours = 0;


idFormCmdSpamExpress = 90;

if(domain != 'spamtonprof'){
	idFormCmdSpamExpress = 90;

}









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
				'action' : 'process_step_1',
				'fields' : JSON.stringify(champs),
			}

			if (typeof cmd !== 'undefined') {
				params_ajax["ref_cmd"] = cmd.ref_cmd,
				params_ajax["ref_cmd_encrypted"] = param_encrypted
				params_ajax["is_update"] = "true"
			}

			console.log("params_ajax");
			console.log(params_ajax);






			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					params_ajax
			)
			.done(function(retour){ 

				console.log("retour.error")
				console.log(retour)

				if(!retour.error){
					console.log("redirecting")
					console.log("step2-spam-express?param=".concat(retour.param_next_page));
					redirectTo("step2-spam-express?param=".concat(retour.param_next_page));
				}

				if(retour.error){
					hideLoader();
					showMessage(retour.message);
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


		if(jQuery(el).hasClass('email')){
			console.log(cmd.lead.email)
			jQuery(el).val( cmd.lead.email ).trigger( 'change' );


		}

		if(jQuery(el).hasClass('prenom')){
			jQuery(el).val( cmd.lead.name ).trigger( 'change' );


		}

		if(jQuery(el).hasClass('pole')){
			jQuery(el).val( cmd.ref_pole ).trigger( 'change' );


		}

		if(jQuery(el).hasClass('niveau')){
			jQuery(el).val( cmd.ref_cat_scolaire ).trigger( 'change' );


		}
	}



});






jQuery( document ).ready( function( $ ) {


	new myCustomFieldController();

	new mySubmitController();


});