





var ajaxEnCours = 0;

var idFormUpdateText = 84;
if(domain != "spamtonprof"){
	idFormUpdateText = 83;
}



var textes = null;
var texteCourant = null;
var indiceCourant = 0;

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {


		if(response.data.form_id == idFormUpdateText){

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

			if(champs.lbc_text == ''){
				showMessage("Veuillez renseigner un texte pas vide !")
				return;
			}
			if(champs.ref_text == ''){
				showMessage("Veuillez d'abord charger des textes");
				return;
			}

			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxUpdateLbcText',
						'fields' : JSON.stringify(champs),
					})
					.done(function(retour){ 

						console.log(retour);


					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

					}).always(function(err){
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".content").removeClass("hide");
						}
					});
		}

	}

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

		console.log('value');
		console.log(value);

		console.log('label');
		console.log(label);

		if(label == 'text_category'){

			textes = null;
			texteCourant = null;
			indiceCourant = 0;

			jQuery("#indexCourant").html('');
			jQuery("#indexFin").html('');

			jQuery(".text_lbc").val('');
			jQuery(".text_lbc").change();

			jQuery(".ref_text").val('');
			jQuery(".ref_text").change();

			if(value != ''){
				jQuery("#loadingSpinner").removeClass("hide");
				jQuery(".content").addClass("hide");

				ajaxEnCours++;
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxLoadTexts',
							'ref_type_texte' : value,
						})
						.done(function(retour){ 

							textes = retour.textes;
							indiceCourant = 0;
							updateIndice();




						})
						.fail(function(err){
							console.log("erreur ajax");
							console.log(err);
							showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

						}).always(function(err){
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								jQuery("#loadingSpinner").addClass("hide");
								jQuery(".content").removeClass("hide");
							}
						});


			}


		}
	}

});




jQuery( document ).ready( function( jQuery ) {


	new mySubmitController();

	new myCustomFieldController();



	waitForEl(".next", function() {

		jQuery(".next").click(function(){
			hideMessage()

			if(textes){

				if(indiceCourant == textes.length-1){
					showMessage("Oups, pas possible d'accéder au texte suivant car c'est le dernier.")
				}else{

					indiceCourant++;
					updateIndice();



				}


			}else{
				showMessage("Veuillez d'abord charger des textes !")
			}



		});

	});

	waitForEl(".previous", function() {

		jQuery(".previous").click(function(){
			hideMessage()

			if(textes){

				if(indiceCourant == 0){
					showMessage("Oups, pas possible d'accéder au texte précédent car c'est le premier.")
				}else{

					indiceCourant--;
					updateIndice();
				}


			}else{
				showMessage("Veuillez d'abord charger des textes !")
			}



		});

	});


});

function updateIndice(){
	texteCourant =  textes[indiceCourant];

	jQuery(".text_lbc").val(texteCourant.texte);
	jQuery(".text_lbc").change();
	jQuery(".ref_text").val(texteCourant.ref_texte);
	jQuery(".ref_text").change();

	waitForEl("#indexFin", function() {
		jQuery("#indexCourant").html(indiceCourant+1);
		jQuery("#indexFin").html(textes.length);
	});
}