/*
 * script chargé sur toutes les pages pour montrer des messages aux visiteurs
 */



//début jquery
jQuery( document ).ready( function( $ ) {
	var urlsParameters = false;

	if(message !== "false"){
		showMessage(message);
	}





});


/* partie de gestion des cookies */

//https://spamtonprof.com/?utm_source=bhm&utm_medium=cloacking_direct&utm_campaign=mars

var utm_source = getParameterByName("utm_source",window.location.href);
var utm_medium = getParameterByName("utm_medium",window.location.href);
var utm_campaign = getParameterByName("utm_campaign",window.location.href);

if(utm_source !== null && getCookie("utm_source_stp") === null){
	setCookie("utm_source_stp",utm_source,10);
}

if(utm_medium !== null && getCookie("utm_medium_stp") === null){
	setCookie("utm_medium_stp",utm_medium,10);
}

if(utm_campaign !== null && getCookie("utm_campaign_stp") === null){
	setCookie("utm_campaign_stp",utm_campaign,10);
}

param = getParameterByName("param2",window.location.href);
console.log(param);






