
jQuery( document ).ready( function( $ ) {
	
	console.log("loaded");


	/* pour algolia */

	var search = instantsearch({
		// Replace with your own values
		appId: '3VXJH73YCI',
		apiKey: '679e64fbe87fa37d0d43e1fbb19e45d8', // search only API key, no ADMIN key
		indexName: 'abonnement',
		urlSync: true,
		searchParameters: {
			hitsPerPage: 10,
			filters: "stripeProdId : ".concat(loggedProf.stripe_id)
		}
	});

	waitForEl('#search-input', function() {


		search.addWidget(
				instantsearch.widgets.searchBox({
					container: '#search-input'
				})
		);

	});

	waitForEl('#hits', function() {

		search.addWidget(
				instantsearch.widgets.hits({
					container: '#hits',
					templates: {
						item: document.getElementById('hit-template').innerHTML,
						empty: "We didn't find any results for the search <em>\"{{query}}\"</em>"
					}
				})
		);

	});

	waitForEl('#pagination', function() {
		search.addWidget(
				instantsearch.widgets.pagination({
					container: '#pagination'
				})
		);

	});

//	waitForEl('#sort-by', function() {
//		search.addWidget(
//				instantsearch.widgets.sortBySelector({
//					container: '#sort-by',
//					autoHideContainer: true,
//					indices: [
//						{
//							name: 'support_client',
//							label: 'Nb de jours depuis dernier message'
//						},
//						{
//							name: 'support_client_by_nb_msg',
//							label: 'Nb de messages 7 derniers jours'
//						}
//						]
//				})
//		);
//	});
	search.start();

	search.on('render', function() {

		// pour mettre adresse email élève
		waitForEl('.update_eleve', function() {
			$(".update_eleve").click(function(){

				console.log("hello");
				refAbo  = jQuery(this).parents(".hit-content").find(".ref_abonnement").text();
				email  = jQuery(this).parents(".hit-content").find(".email").text();

				console.log(refAbo);
				console.log(email);
				jQuery("#fountainTextG").removeClass("hide");
				jQuery(".hide_loading").addClass("hide");
				jQuery.post(
						ajaxurl,
						{
							'action' : 'ajaxUpdateEleve',
							'email' : email,
							'refAbo' : refAbo
						})
						.done(function(retour){ 

							error = retour.error;
							message = retour.message;

							console.log("la");
							console.log(retour);

							if(error){

								showMessage("Il y a un problème. Contacter l'équipe et donner leur ce message d'erreur : ".concat(message));
								ajaxEnCours--;
								if(ajaxEnCours == 0){
									jQuery("#fountainTextG").addClass("hide");
									jQuery(".hide_loading").removeClass("hide");
								}
							}else{

								showMessage(message);

							}


						})
						.fail(function(err){
							console.log("erreur ajax");
							console.log(err);
							showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");

						})
						.always(function(){
							jQuery("#fountainTextG").addClass("hide");
							jQuery(".hide_loading").removeClass("hide");
						});

				// fin pour mettre adresse email élève



			});
		});


	});

	/* fin pour algolia */

});