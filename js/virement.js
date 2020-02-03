
const ONE_DAY_IN_MS = 3600 * 24 * 1000;


var profLogged = false;
var  email_prof = loggedProf.email_stp;


moment.locale('fr-ch');
jQuery( document ).ready( function( $ ) {



	const search = instantsearch({
		indexName: 'stripe_transaction',
		searchClient: algoliasearch('3VXJH73YCI', '679e64fbe87fa37d0d43e1fbb19e45d8')
	});


	if (typeof loggedProf !== 'undefined') {
		var profLogged = true;

		search.addWidget(
				instantsearch.widgets.configure({
					filters: 'email_prof:'.concat(email_prof)
				})
		);
	}






	waitForEl('#refinement-list1', function() {


		if(!profLogged){

			search.addWidget(
					instantsearch.widgets.refinementList({
						container: '#refinement-list1',
						attribute: "email_prof",

					})
			);
		}

	});



	waitForEl('#hits', function() {

		search.addWidget(
				instantsearch.widgets.hits({
					container: '#hits',
					templates: {
						item: hit => {
							const { emails } = hit;
							return `
							<div class="grid-container">
							<div class="item1"> 
							Virement de ${hit.payout_amount} €

							</div>
							<div class="item2">
							Viré le: ${hit.payout_date}
							</div>
							<div class="item3">
							ID: ${instantsearch.highlight({ attribute: 'ref_payout', hit })}
							</div>
							<div class="item4">
							Paiement de ${hit.total_amount} €
							</div>
							<div class="item5">
							Reste après commission: ${hit.paid_amount} €
							</div>
							<div class="item6">
							ID: ${hit.ref_transaction}
							</div>
							<div class="item7">
							${hit.formule_name}<br>
							Du ${hit.start_week} au ${hit.end_week}<br>
							Ref facture: ${hit.invoice_id}
							</div>
							<div class="item8">


							${emails.map((email, index) =>
							instantsearch.highlight({
							attribute: `emails.${index}`,
							hit,
							})
							).join('\n')}<br>



							${hit.eleve}<br>
							avec ${hit.email_prof}
							</div>
							</div>
							`;
						},
					},
					transformItems(items) {

						return items.map(item => ({
							...item,
							payout_amount: item.payout_amount / 100,
							paid_amount: item.paid_amount / 100,
							total_amount: item.total_amount / 100,
							payout_date: item.payout_date.substr(0,10)
						}));
					},
				})
		);

	});



	search.addWidget(
			instantsearch.widgets.pagination({
				container: '#pagination',
			})
	);







	const makeRangeWidget = instantsearch.connectors.connectRange(

			(options, isFirstRendering) => {

				if (!isFirstRendering) return;

				const { refine } = options;

				new Calendar({
					element: $('#calendar'),
					locale: 'fr',
					same_day_range: true,
					callback: function() {
						const start = new Date(this.start_date).getTime();
						const end = new Date(this.end_date).getTime();
						const actualEnd = start === end ? end + ONE_DAY_IN_MS - 1 : end;

						console.log("start")
						console.log(start)

						refine([start/1000, actualEnd/1000]);
					},
					// Some good parameters based on our dataset:
					start_date: new Date('01/01/2018'),
					end_date: new Date(),
					earliest_date: new Date('01/01/2008'),
					latest_date: new Date(),
				});
			}
	);

	const dateRangeWidget = makeRangeWidget({
		attribute: 'payout_date_timestamp',
		attributeName: 'payout_date_timestamp',
//		container: document.querySelector('#calendar')
	});

	search.addWidget(dateRangeWidget);



	search.start();

	search.on('render', function() {



	});

	/* fin pour algolia */

});