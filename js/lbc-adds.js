/*
 * script chargé sur la page dont le slug est lbc-adds
 */



var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {
		console.log("form submitted");
		console.log(response);
		
		// titles form
		if(response.data.form_id == "55"){

			typeTitle = response.data.fields["886"].value;
			console.log(typeTitle);

			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTitles',
						'typeTitle' : typeTitle 
					})
					.done(function(titles){ 
						console.log("okay");
						
						$('#csvTitles').empty();
						$('#csvTitles').append('<a href="' .concat(titles.csvPath,'">Download as csv</a>'));

						$("#titleTable tr").remove();

						$.each(titles.titles, function (i, item) {


							var table = document.getElementById("titleTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = item.titre;



						});

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
					});

		}

		// textes form
		if(response.data.form_id == "56"){
			console.log("textes form submitted");
			typeTexte = response.data.fields["888"].value;
			console.log(typeTexte);

			jQuery.post(
					ajaxurl,
					{
						'action' : 'ajaxGetTextes',
						'typeTexte' : typeTexte 
					})
					.done(function(textes){ 
						console.log("okay");
						
						$('#csvTextes').empty();
						$('#csvTextes').append('<a href="' .concat(textes.csvPath,'">Download as csv</a>'));

						$("#texteTable tr").remove();

						$.each(textes.textes, function (i, item) {

							console.log(item);
							var table = document.getElementById("texteTable");
							var row = table.insertRow(0);
							var cell1 = row.insertCell(0);
							cell1.innerHTML = item.texte;

						});

					})
					.fail(function(err){
						console.log("erreur ajax");
						console.log(err);
						showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
					});
		}

	},

});


jQuery( document ).ready( function( $ ) {


	// chargement des options du formulaire des titres
	waitForEl('#nf-field-886', function() {

		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetAddsTitle'
				})
				.done(function(titleTypes){ 

					$.each(titleTypes, function (i, item) {
						$('#nf-field-886').append($('<option>', { 
							value: item,
							text : item 
						}));

					});
					$('#nf-field-886').prepend("<option value='' selected='selected'></option>");

				})
				.fail(function(err){
					console.log("erreur ajax");
					console.log(err);
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
				});



	});
	
	// chargement des options du formulaire des textes
	waitForEl('#nf-field-888', function() {

		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetAddsTexteType'
				})
				.done(function(titleTypes){ 

					$.each(titleTypes, function (i, item) {
						$('#nf-field-888').append($('<option>', { 
							value: item,
							text : item 
						}));

					});
					$('#nf-field-888').prepend("<option value='' selected='selected'></option>");

				})
				.fail(function(err){
					console.log("erreur ajax");
					console.log(err);
					showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
				});

	});
	

	new mySubmitController();

});


