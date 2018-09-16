



var mySubmitController = Marionette.Object.extend( {

	initialize: function() {
		this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
	},

	actionSubmit: function( response ) {
		console.log("form submitted");
		typeTitle = response.data.fields["886"].value;
		console.log(typeTitle);

		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetTitles',
					'typeTitle' : typeTitle 
				})
				.done(function(titleTypes){ 
					console.log("okay");
					
					$("#titleTable tr").remove();
					
					$.each(titleTypes, function (i, item) {

						
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




	},

});


jQuery( document ).ready( function( $ ) {

	waitForEl('#nf-field-888', function() {

		jQuery.post(
				ajaxurl,
				{
					'action' : 'ajaxGetAddsTexte'
				})
				.done(function(texteTypes){ 

					$.each(texteTypes, function (i, item) {
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


