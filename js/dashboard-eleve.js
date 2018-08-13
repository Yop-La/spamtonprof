

ajaxEnCours = 0;

// .row-essai
//.prenom-eleve
//.nom-formule
//.statut-essai
//.date-essai

jQuery( document ).ready( function( $ ) {
	
	
	waitForEl(".row-essai", function() {
		
		nbAbosEssai = abosEssai.length;
		
		for(var i = 0; i< nbAbosEssai ; i++){
			
			abo = abosEssai[i];
			
			rowEssai = $(".row-essai").clone();
			rowEssai.insertAfter(".row-essai");
		
			
			rowEssai.find(".prenom-eleve").html(abo.eleve.prenom);
			rowEssai.find(".nom-formule").html(abo.formule.formule);
//			rowEssai.find(".statut-essai").html(abo.eleve.prenom);
//			rowEssai.find(".date-essai").html(abo.eleve.prenom);
			
			rowEssai.removeClass("hide");
			
			
		}
		
		
		
	});

	
	
	
	
//	// on récupère les formules d'essai lié au compte
//	ajaxEnCours++;
//	jQuery.post(
//			ajaxurl,
//			{
//				'action' : 'ajaxAfterSubmissionEssai'
//			})
//			.done(function(retour){ 
//				
//				error = retour.error;
//				message = retour.message;
//				
//				if(error){
//					
//					showMessage("Il y a un problème. Contacter l'équipe et donner leur ce message d'erreur : ".concat(message));
//					ajaxEnCours--;
//					if(ajaxEnCours == 0){
//						$("#loadingSpinner").addClass("hide");
//						$(".hide_loading").removeClass("hide");
//					}
//				}else{
//					
//					if(message == "compte_existe_deja"){
//						redirect("connexion" ,info = "Vous avez déjà un compte. Connectez vous ! " );
//					}else{
//						redirect("remerciement-eleve" ,"Félicitations. Tu pourras démarrer la semaine de découverte dans 1 jour !" );
//					}
//					
//				}
//				
//				
//			})
//			.fail(function(err){
//				console.log("erreur ajax");
//				console.log(err);
//				showMessage("Il y a un problème. Veuillez raffraichir la page et contacter l'équipe si le problème persiste");
//				ajaxEnCours--;
//				if(ajaxEnCours == 0){
//					$("#loadingSpinner").addClass("hide");
//					$(".hide_loading").removeClass("hide");
//				}
//			});
	
	
	
});