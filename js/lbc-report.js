

console.log('ok !!');


jQuery( document ).ready( function( jQuery ) {


	var data = null

	jQuery.post(
			ajaxurl,
			{
				'action' : 'lbcReport',
			})
			.done(function(retour){ 

				console.log(retour);
				data = retour;





				waitForEl('#table_1', function() {




					jQuery('#table_1').DataTable( {
						data: data.tab1,
						columns: [
							{ title: "Prénom" },
							{ title: "Ref client" },
							{ title: "Nb annonces","searchable": false }
							],
							"order": [[ 2, "desc" ]],
							"autoWidth": false
					} );






				});

				waitForEl('#table_2', function() {





					jQuery('#table_2').DataTable( {
						data: data.tab2,
						columns: [
							{ title: "Prénom" },
							{ title: "Nb ads" },
							{ title: "Date création" }
							],
							"order": [[ 2, "desc" ]],
							"autoWidth": false
					} );





				});


				waitForEl('#table_3', function() {



					jQuery('#table_3').DataTable( {
						data: data.tab3,
						columns: [
							{ title: "Ref client" },
							{ title: "Prénom client" },
							{ title: "Domain","searchable": false },
							{ title: "Sum","searchable": false },
							],
							"order": [[ 3, "desc" ]],
							"autoWidth": false
					} );





				});




				waitForEl('#table_4', function() {


					table4 = jQuery('#table_4').DataTable( {
						data: data.tab4,
						columns: [
							{ title: "Domain name" },
							{ title: "Mail provider" },
							{ title: "Nb account" },
							{ title: "Nb ads","searchable": false },
							{ title: "Disabled","searchable": false },
							],
							"order": [[ 3, "desc" ]],
							"autoWidth": false
					} );


					jQuery('#table_4 tbody').on( 'click', 'td', function () {


						var cell = table4.cell( this );
						var rowIdx = cell.index().row;


						var data = table4.rows( rowIdx ).data()[0];


						var disabled = data[4];
						disabled = !disabled;
						console.log(disabled)

						jQuery("#loading_screen").removeClass("hide");
						jQuery(".content").addClass("hide");



						jQuery.post(
								ajaxurl,
								{
									'action' : 'updateDomain',
									'domain_name' :  data[0],
									'disabled' :  disabled
								})
								.done(function(retour){ 


									console.log(retour.disabled);
									var cell = table4.cell(rowIdx, 4);
									cell.data(disabled).draw();

									alert("Domaine bien mis à jour")



									jQuery("#loading_screen").addClass("hide");
									jQuery(".content").removeClass("hide");

								})
								.fail(function(err){

									alert("Echec de la mise à jour du domaine")

								});




					} );




				});



				waitForEl('#table_5', function() {



					table5 = jQuery('#table_5').DataTable( {
						data: data.tab5,
						columns: [
							{ title: "Ref_compte" ,"searchable": false },
							{ title: "mail"  },
							{ title: "nb_online","searchable": false },
							{ title: "date_creation","searchable": false },
							{ title: "date_publication","searchable": false },
							{ title: "controle_date","searchable": false },
							{ title: "user_id","searchable": false },
							{ title: "prenom","searchable": false },
							{ title: "disabled","searchable": false },
							{ title: "Ref client"},
							],
							"order": [[ 4, "desc" ]],
							"autoWidth": false
					} );


					jQuery('#table_5 tbody').on( 'click', 'td', function () {

						var cell = table5.cell( this );
						var rowIdx = cell.index().row;


						var data = table5.rows( rowIdx ).data()[0];


						var user_id = data[6];

						$link = 'https://www.leboncoin.fr/profil/'.concat(user_id,'/offres');

						var win = window.open($link, '_blank');
						if (win) {
							//Browser has allowed it to be opened
							win.focus();
						} else {
							//Browser has blocked it
							alert('Please allow popups for this website');
						}

					});


				});






				jQuery("#loading_screen").addClass("hide");
				jQuery(".content").removeClass("hide");


			})
			.fail(function(err){

				console.log('fail');

			});


});