/*
 * script chargé toutes les pages - sert à faire des redirections si il y en a
 */

console.log("begin : trying to redirect");

//début jquery
jQuery( document ).ready( function( $ ) {

	
	if(redirection != 0){

		waitForEl("#hidden-form",function(){

			if("slug" in redirection){
				redirect(redirection.slug, redirection.message );
			}

		});

	}


});



