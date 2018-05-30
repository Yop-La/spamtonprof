
//id des champs du formulaire ( dépend du formulaire )

var prenomEleveId = "356";
var nomEleveId = "357";
var classeId = "358";
var emailEleveId= "359";
var telephoneEleveId = "360";

var chapitreMathsId = "361";
var difficulteMathsId = "362";
var matieresId = "363";
var procheId = "364";
var noteMathsId = "365";

var html1Id = "366";
var html2Id = "367";
var html3Id = "368";
var html4Id = "374";
var html5Id = "377";
var html6Id = "692";

var chapitrePhysiqueId = "369";
var difficultePhysiqueId = "370";
var notePhysiqueId = "371";
var prenomParentId = "372";
var emailParentId = "373";

var nomProcheId = "375";
var telephoneParentId = "376";

var cgvId = "378";
var remarqueId = "379";
var submitButtonId = "380";
var tarifId = "509";
var codeParrainId = "648";

//variables pour contrôler chaque étape du formulaire

var fieldsFirstStep = [];
var nbFieldsFirstStep = 5; 


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
		var modelType       = model.get( 'type' ); //email par exemple
		var modelValue       = model.get( 'value' );
		var errorID       = 'custom-field-error';
		var fieldsChannel = Backbone.Radio.channel( 'fields' );

		if(modelID == emailEleveId){
			if(isEmail(modelValue)){
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxHasToLogEmailEleve',
							'email' : modelValue.trim() 
						})
						.done(function(hasToLog){ 
							if(hasToLog){
								redirect("abonnement-apres-essai","Vous avez déjà profité de la semaine d&#39;essai. Retrouvez votre compte pour reprendre le soutien");
							}
						})
						.fail(function(err){
							console.log(err);
							redirect(currentSlug,"Il y a un problème. Veuillez raffraichir la page et contacter l&#39;équipe si le problème persiste");
						});

			}

		}

		if(modelID == emailParentId){
			if(isEmail(modelValue)){
				console.log("dedans");
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxAccountLimit',
							'email' : modelValue.trim() 
						})
						.done(function(accountLimit){
							console.log(accountLimit);
							if(accountLimit){
								Backbone.Radio.channel( 'fields' ).request( 'add:error', model.get( 'id' ), 'custom-field-error', 'Deux comptes sont déjà associés à cette adresse' );
							}else{
								Backbone.Radio.channel( 'fields' ).request( 'remove:error', model.get( 'id' ), 'custom-field-error' );
							}
						})
						.fail(function(err){
							redirect(currentSlug,"Il y a un problème. Veuillez raffraichir la page et contacter l&#39;équipe si le problème persiste");
						});

			}

		}


	}
});


jQuery( document ).ready( function( $ ) {

	new myCustomFieldController(); 


});