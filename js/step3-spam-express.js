
ajaxEnCours = 0;







jQuery( document ).ready( function( $ ) {




	main_url = "https://spamtonprof.com"
		if(domain=="localhost"){
			main_url = "http://localhost/spamtonprof"
		}


	jQuery('.previous a').attr("href",main_url.concat("/step2-spam-express/?param=",param_encrypted))

	var stripe = Stripe(publicStripeKey);


	offers = cmd.offres


	idx = 0
	jQuery('.price-table').each(function(){

		offer = offers[idx]

		console.log(offer);

		jQuery(this).find(".elementor-price-table__integer-part").text(offer.price)

		jQuery(this).find(".elementor-price-table__heading").text(offer.title)

		jQuery(this).find(".elementor-price-table__subheading").text(offer.name)

		matiere = cmd.pole.name.split(':')[1]

		jQuery(this).find(".elementor-price-table__period").html(offer.title.concat('<br>En ',matiere))
		jQuery(this).find(".elementor-button").data('ref_offre', offer.ref_offre)

		jQuery(this).find(".elementor-button").click(function(){

			showLoader()

			console.log("offer.ref_offre");
			console.log(offer.ref_offre);

			params_ajax = {
					'action' : 'process_step_3',
					"ref_cmd_encrypted" : param_encrypted,
					"ref_cmd" : cmd.ref_cmd,
					"ref_offre": jQuery(this).data('ref_offre'),
					'test_mode': testMode
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

					//					console.log("redirecting to checkout")
					//					console.log(retour.checkout_session_id)

					hideLoader();

					stripe.redirectToCheckout({
						// Make the id field from the Checkout Session creation API response
						// available to this file, so you can provide it as parameter here
						// instead of the {{CHECKOUT_SESSION_ID}} placeholder.
						sessionId: retour.checkout_session_id
					}).then(function (result) {

						result.error.message = 'Oops : il y a eu un problème. Veuillez réessayer ou contacter l\'équipe. ';


						// If `redirectToCheckout` fails due to a browser or network
						// error, display the localized error message to your customer
						// using `result.error.message`.
					});


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







		})






		//		data = jQuery(this).find(".elementor-button").data('checkout_id')
		//		console.log(data)

		offer.name 
		offer.title
		offer.price

		idx = idx + 1
	})


	//	jQuery(".nf-response-msg").addClass("hide");



});