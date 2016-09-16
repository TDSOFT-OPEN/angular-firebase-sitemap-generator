var disabledStates = [
	"faker",
	"userView",
	"mainCategory",
	"subCategory",
	"popularSearch",
	"specificOffer",
	"confirm-post/:token",
	"refresh-post/:token",
	"prolong-post/:token",
	"activate-account/:token",
	"product",
	"editProduct",
	"moderator",
];

function generateURLs(disabledStates) {
	Refs.states.once("value", function(snap) {
	  	if (!snap.val()) {
		  	var states = $state.get();
		  	var urls = [];
		  	var urlsTemp = {};
		  	var currentTime = moment.utc().format("YYYY-MM-DD HH:mm:ss");

		  	/* Remove disabledStates from the states array */
		  	for (i = 0; i < disabledStates.length; i++) {
		  		for (ii = 0; ii < states.length; ii++) {
		  			if (states[ii].name === disabledStates[i]) {
		  				states.splice(ii, 1);
		  				break;
		  			}
		  		}
		  	}

		  	/* Push filtered states' urls to urls array */
		  	for (i = 0; i < states.length; i++) {
		  		if (states[i].url && states[i].url !== "/" && states[i].name && !urlsTemp[states[i].url]) {
		  			urlsTemp[states[i].url] = states[i].url;
		  			urls.push(states[i].url);
		  		}
		  	}

		  	urls['creation_time'] = currentTime;
		  	Refs.states.set(urls);
		  	}
		})
}

generateURLs(disabledStates);