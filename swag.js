/**
 * fonctiond e construction de l'URL
 * @param {*} filterby colonne sur laquelle filtrer
 * @param {*} filterval valeur par laquelle filtrer
 * @returns l'url construite au format string
 */
function buildUrl(filterby, filterval){
	var url = "http://localhost:8888/td11/api.php"; // l'url de base à laquelle on veut ajouter des parmaètres comme ?sort=name

	if (filterby == "gender"){
		url += "?filterby=" + filterby + "&filterval=" + filterval;
	}

	return url;
}

async function loadJSONDoc(filterby, filterval){
	// URL (c'est-dire adresse) de l'appel AJAX (asynchrone)
	// nous utilisons la fonction buildUrl() est dédiée à construire l'adresse avec les paramètres dans l'URL
	// (pour rappel un paramètre d'url est par exemple : http://gguibon.fr?nomduparametre1=valeurduparametre1&nomduparametre2=valeurduparametre2 )
	var AJAXurl = buildUrl(filterby, filterval);
	console.log('ajax url ', AJAXurl);

	// on fait une requête HTTP à l'aide de la fonction fetch() vers l'URL construite
	// vous noterez que le prefixe "await" n'est disponible que dans les fonctions asynchrones (préfixées de async)
	// await permet d'attendre la réponse. Concrètement, cela permet de laisser le navigateur continue les autres processus en attendant (await en anglais) un retour de cette requête. Dès que le retour arrive, la réponse est alors mise dans la variable "response"
	let response = await fetch(AJAXurl);
	// la réponse possède plusieurs éléments: header, statut, contenu, etc. Nous la transformons ainsi en JSON. Mais vous vous demanderez surement pourquoi cette traduction de la réponse au format JSON avec .json() a besoin d'être asynchrone à l'aide de await ? C'est parce que cette analyse peut parfois prendre du temps et n'a donc pas besoin d'être bloquante pour le navigateur (ce qui aurait pour effet de bloquer toute action dans la page web en attendant la fin de l'analyse)
	let dataFromServer = await response.json();
	console.log(dataFromServer);
	// maintenant que le contenu est mis au format JSON dans une variable nous passons à la vérification du statut de la réponse. Les statuts sont normalement des codes comme 200 pour OK, 404 pour NOT FOUND, 500 pour erreur serveur, etc. etc. 
	// on peut directement vérifier si ok est true ou false (un booléen donc)
	if(response.ok){
		// SI le statut de la réponse est OK (200) aLORS
		// on fait toute notre panoplie de modification de la page à partir du contenu
		processContent(dataFromServer);
	}else{
		// SINON (donc si le statut n'est pas OK (200)) ALORS
		// on ajoute une entrée au journal de la console
		console.log('error');
		// nous pourrions aussi être très méchant et afficher un popup d'alerte comme ceci
		alert('ERROR 😱😱😱😱😱😱😱😱');
	}
}

/**
 * fonction asynchrone pour modifier la base de données
 * ici, en une fonction, on traite tout type de modification
 * cela sera encoyé en requête POST au serveur PHP
 * la clé "action" indiquera au serveur quel type d'action effectuer
 * @param {*} data données contenant en plus, une clé "action"
 */
async function modif(data){
	var myHeaders = new Headers();
	myHeaders.append('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');

	var config = {
		method: 'POST',
		headers: myHeaders,
		'body': JSON.stringify(data)
	};

	let response = await fetch("http://localhost:8888/td11/api.php", config);
	
	if(response.ok){
		let dataFromServer = await response.json();
		console.log("dataFromServer", dataFromServer);
		// SI le statut de la réponse est OK (200) aLORS
		// on fait toute notre panoplie de modification de la page à partir du contenu
		processContent(dataFromServer);
	}else{
		// SINON (donc si le statut n'est pas OK (200)) ALORS
		// on ajoute une entrée au journal de la console
		console.log('error');
		// nous pourrions aussi être très méchant et afficher un popup d'alerte comme ceci
		alert('ERROR 😱😱😱😱😱😱😱😱');
	} 
}

/**
 * fonction qui parcourt le JSON analysé et met à jour le tableau HTML en fonction de son contenu
 * @param {*} resp paramètre prenant le contenu de la réponse JSON (ce qui rend cette fonction réutilisable)
 */
function processContent(resp){
	var targetDiv = document.getElementById("produitsAffichage");

	targetDiv.innerHTML = "";

	// avec notre fonction d'insertion nous insérons désormais tout le nouveau contenu
	// cette logique est la même que pour le jeu de serpents : on efface le contenu puis on remet toutes les nouvelles infos
	insert(resp, targetDiv);
}

/**
 * fonction d'insertion du nouveau contenu au format HTML
 * @param {*} contenu tableau de tableaux associatifs. Le contenu issu de la BDD, le JSON qui api.php a renvoyé avec echo json_encode()
 * @param {*} cible balise cible dans laquelle mettre tout ce nouveau contenu au final
 */
function insert(contenu, cible){
	for (var i = 0; i < contenu.length; i++){

		var col = document.createElement("div");
		col.classList.add("col-sm-4");

		var card = document.createElement("div");
		card.className = "card text-bg-light";

		var img = document.createElement("img");
		img.className = "card-img-top";
		img.setAttribute("src", "img/vetements/" + contenu[i]['id'] + ".jpg");

		var cardBody = document.createElement("div");
		cardBody.className = "card-body";

		var h4 = document.createElement("h4");
		h4.className = "card-title";
		h4.innerText = contenu[i]['productDisplayName'];

		var p = document.createElement("p");
		p.className = "card-text";

		var spanType = document.createElement("span");
		spanType.className = "badge bg-secondary";
		spanType.innerText = contenu[i]['articleType'];
		
		var spanColour = document.createElement("span");
		spanColour.className = "badge bg-secondary";
		spanColour.innerText = contenu[i]['baseColour'];
		
		var spanYear = document.createElement("span");
		spanYear.className = "badge bg-dark";
		spanYear.innerText = contenu[i]['year'];
		

		var genderIcon = "";
		if (contenu[i]['gender'] == "Women"){ genderIcon = "<i class='bi bi-gender-female'></i>"; }
		else{ genderIcon = '<i class="bi bi-gender-male"></i>';  }
		var btn = document.createElement("button");
		btn.className = "btn btn-secondary btn-sm";
		btn.innerHTML = genderIcon;
		btn.setAttribute("onclick", "filterByGender(" + contenu[i]["gender"] + ")" );

		var btnRemove = document.createElement("btn");
		btnRemove.className = "btn btn-danger btn-sm";
		btnRemove.setAttribute("onclick", "remove("+ contenu[i]["id"] + ")");
		btnRemove.innerHTML = "<i class='bi bi-trash3-fill'></i>";

		// on ordonne toute la hiérarchie des balises
		p.appendChild(spanType);
		p.appendChild(spanColour);
		p.appendChild(spanYear);
		p.appendChild(btn);
		p.appendChild(btnRemove);
		cardBody.appendChild(h4);
		cardBody.appendChild(p);
		card.appendChild(img);
		card.appendChild(cardBody);
		col.appendChild(card);

		cible.appendChild(col);

	}
}

/**
 * fonction pratique qui permet d'utiliser loadJSONDoc directement pour filtrer sur la colonne "gender"
 * permet de simplifier ce que nous avons mis en onclick dans le HTML
 * @param {*} value valeur sur laquelle filtrer
 */
function filterByGender(value) {
	console.log("yo", value);
	loadJSONDoc("gender", value);
}

/**
 * fonction pour supprimer. Oui, cette fonction s'appelle "remove" et non "delete" car delete est un mot clé de Javascript !
 * @param {*} idx l'identifiant de l'élément à supprimer (car c'est la clé primaire dans la BDD)
 */
function remove(idx) {
	console.log("hey");
	modif({id:idx, action:'delete'});
}

document.addEventListener('DOMContentLoaded', function(){
	document.querySelector('.ajaxform').addEventListener('submit', function (e) {
		// empêche l'envoi auto du formulaire vers le serveur
		e.preventDefault();
		var data = Object.fromEntries(new FormData(e.target));

		data["action"] = "add";
		console.log(data);
		modif(data);
	  });
});

