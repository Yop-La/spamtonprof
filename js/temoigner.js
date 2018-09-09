/*
 * script chargé sur la page dont le slug est temoigner
 */


//début jquery
jQuery( document ).ready( function( $ ) {

	buttonEcritId = ".temoignage-ecrit";
	buttonVideoId = ".temoignage-video";

	waitForEl(buttonEcritId,function(){

		jQuery(buttonEcritId).click(function(){

			if(!jQuery( ".video" ).hasClass( "hide" )){

				console.log("click ecrit");
				
				jQuery("#video-row").addClass("hide");
				jQuery("#ecrit-row").removeClass("hide");
			
//				jQuery(".titre-ecrit").get(0).scrollIntoView();
			}
		});

	});

	waitForEl(buttonVideoId,function(){

		jQuery(buttonVideoId).click(function(){

			if(!jQuery( ".ecrit" ).hasClass( "hide" )){

				console.log("click video");
				
				jQuery("#ecrit-row").addClass("hide");
				jQuery("#video-row").removeClass("hide");

//				jQuery(".titre-video").get(0).scrollIntoView();

			}

		});

	});




});