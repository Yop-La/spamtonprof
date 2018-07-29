/*
 * script chargé sur la page dont le slug est temoigner
 */


//début jquery
jQuery( document ).ready( function( $ ) {

	buttonEcritId = ".temoignage-ecrit";
	buttonVideoId = ".temoignage-video";

	waitForEl(buttonEcritId,function(){

		$(buttonEcritId).click(function(){

			if(!$( ".video" ).hasClass( "hide" )){

				console.log("click ecrit");
				
				$("#video-row").addClass("hide");
				$("#ecrit-row").removeClass("hide");
			
//				$(".titre-ecrit").get(0).scrollIntoView();
			}
		});

	});

	waitForEl(buttonVideoId,function(){

		$(buttonVideoId).click(function(){

			if(!$( ".ecrit" ).hasClass( "hide" )){

				console.log("click video");
				
				$("#ecrit-row").addClass("hide");
				$("#video-row").removeClass("hide");

//				$(".titre-video").get(0).scrollIntoView();

			}

		});

	});




});