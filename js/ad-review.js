





var ajaxEnCours = 0;
var idChoixClient = 1133;
if(domain == 'localhost'){
	idChoixClient = 1133;
}

var idAdReview = '81';

var idCfgClient = '82';

var actionClient = null;

var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {


		if(response.data.form_id == idCfgClient){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");

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


			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxUpdateCfgClient',
						'fields' : JSON.stringify(champs),
					})
					.done(function(retour){ 

						console.log(retour);

						error = retour.error;
						message = retour.message;

						if(error){
							ajaxEnCours--;
							if(ajaxEnCours == 0){
								redirectTo("ad-review" ,"Oops : Il y a eu une erreur :/" );
							}
						}


						redirectTo("ad-review" ,"La conf du client est bien mise à jour !" );

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});
		}


		if(response.data.form_id == idAdReview){
			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");



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

			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxAdsReview',
						'fields' : JSON.stringify(champs),
					})
					.done(function(retour){ 

						console.log(retour);

						
						
						error = retour.error;
						message = retour.message;
						ads = retour.ads;

						if(error){


							ajaxEnCours--;
							if(ajaxEnCours == 0){



								redirectTo("ad-review" ,"Oops : Il y a eu une erreur :/" );

							}

						}


						jQuery('#csvTitles1').empty();
						jQuery("#titleTable1 tr").remove();

						jQuery('#csvTextes2').empty();
						jQuery("#texteTable2 tr").remove();

						jQuery('#csvImages').empty();
						jQuery("#imageTable tr").remove();

						jQuery('#csvCommunes').empty();
						jQuery("#communeTable tr").remove();
						var i = 1;
						ads.forEach(function(ad){

							console.log(ad);
							ad.title;
							ad.text;
							ad.commune;


							var table = document.getElementById("titleTable1");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = 'n° '.concat(i, ' : ',ad.title);

							var table = document.getElementById("texteTable1");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = 'n° '.concat(i, ' : ',ad.text.texte.replace(/(?:\r\n|\r|\n)/g, '<br>'));

							var table = document.getElementById("communeTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = 'n° '.concat(i, ' : ',ad.commune);

							
							
							if(i<=5){
								jQuery('#ad_img_'.concat(i)).attr('src',ad.image);
							}else{
								var table = document.getElementById("imageTable");
								var row = table.insertRow(0);
								var cell1 = row.insertCell(0);
								cell1.innerHTML = 'n° '.concat(i, ' : ',ad.image);
								
							}
							i++;
						});






						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".content").removeClass("hide");
							jQuery('.imgs_ads').removeClass('hide');
						}

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
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

		console.log('view');
		console.log(view);

		value = view.attributes.value;
		label = view.attributes.label;


		if(label == 'type_titre' && value != ''){


			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTitlesByRef',
						'ref_type_titre' : value,
					})
					.done(function(titles){ 


						jQuery('#csvTitles').empty();

						jQuery("#titleTable tr").remove();

						jQuery.each(titles.titles, function (i, item) {


							var table = document.getElementById("titleTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = item.titre;



						});






						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".content").removeClass("hide");
						}

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});





		}

		if(label == 'type_texte' && value != ''){



			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");

			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTextesByRef',
						'ref_type_texte' : value,
						'limit' : 10
					})
					.done(function(textes){ 



						jQuery('#csvTextes').empty();

						jQuery("#texteTable tr").remove();

						var indiceText = 1;
						jQuery.each(textes.textes, function (i, item) {

							var table = document.getElementById("texteTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = item.texte.replace(/(?:\r\n|\r|\n)/g, '<br>');

						});


						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".content").removeClass("hide");
						}

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});



		}

		if(label == 'client_action'){

			actionClient = value;


		}

		if(label == 'choisir_client'){




			jQuery("#loadingSpinner").removeClass("hide");
			jQuery(".content").addClass("hide");



			// soumission ajax des champs du form pour création inscription
			ajaxEnCours++;
			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetConfClient',
						'ref_client' : value,
					})
					.done(function(retour){ 

						console.log(retour);
						error = retour.error;
						message = retour.message;


						if(error){


							ajaxEnCours--;
							if(ajaxEnCours == 0){



								redirectTo("ad-review" ,"Oops : Il y a eu une erreur :/" );

							}

						}

						conf = retour.conf;
						client = conf.client

						jQuery('.msg_type_titre').text(conf.messagetypeTitre);
						jQuery('.msg_type_texte').text(conf.messagetypeTexte);

						jQuery('.prenom').val(conf.client.prenom_client).change();
						jQuery('.nom').val(conf.client.nom_client).change();
						jQuery('.folder_img').val(conf.client.img_folder).change();
						jQuery('.domain').val(conf.client.domain).change();

						if(conf.typeTexte){
							jQuery('.type_texte').val(conf.typeTexte.ref_type).change();
						}else{
							jQuery('.type_texte').val('').change();

							jQuery('#csvTitles').empty();

							jQuery("#titleTable tr").remove();

						}
						if(conf.typeTitre){
							jQuery('.type_titre').val(conf.typeTitre.ref_type).change();
						}else{
							jQuery('.type_titre').val('').change();

							jQuery('#csvTextes').empty();

							jQuery("#texteTable tr").remove();
						}

						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".content").removeClass("hide");
						}

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
						ajaxEnCours--;
						if(ajaxEnCours == 0){
							jQuery("#loadingSpinner").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						}
					});




		}




//		var el = jQuery( view.el ).find( '.nf-element' );

//		var index = 0;
//		for(var i=1;i<=10;i++){
//		if(jQuery(el).hasClass('matiere'.concat(i))){
//		index = i;
//		break;
//		}
//		}
//		if(index == 0){
//		return(false);
//		}

//		console.log('matiere : ')
//		console.log(index);



//		nomMatiere = formuleChoisie.matieres[index-1].matiere_complet.toLowerCase()

//		placeholder = jQuery(el[0]).attr('placeholder')
//		console.log('placeholder');
//		console.log(placeholder);
//		placeholder = placeholder.replace(new RegExp('matiere'.concat(index), 'g'),nomMatiere);

//		jQuery(el[0]).attr('placeholder',placeholder)


		// Do stuff.
	}

});




jQuery( document ).ready( function( jQuery ) {



	new mySubmitController();

	new myCustomFieldController();


});