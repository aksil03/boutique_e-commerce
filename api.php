<?php

/**
 * fonction de connexion
 * Le fait d'en faire une fonction permet d'éviter les erreurs et rend le code plus propre
 * @return mysqli $conn instance de connexion  mysqli
 */
function connect() {
	$db_host = 'localhost';
	$db_user = 'root';
	$db_password = 'root';
	$db_db = 'produits';
	$db_port = 8889;

	$conn = new mysqli(
	$db_host,
	$db_user,
	$db_password,
	$db_db
	);

	if ($conn->connect_error) {
		echo 'Errno: '.$conn->connect_errno;
		echo '<br>';
		echo 'Error: '.$conn->connect_error;
		exit();
	}
	return $conn;
}

/**
 * désormais nous séparons les requêtes à la base de données du traitement des données pour les envoyer. Cette fonction sert à analyser (parse) les réusltats (results) provenant de la bdd et à les envoyer en tant que contenu JSON
 * 
 * cette fonction va donc envoyer une réponse au client web (javascript la prendra alors dans la variable "response" suite au await fetch())
 * 
 * @param $result objet de résultat de la bdd
 */
function parseResults($result) {
	header("Content-Type: application/json");
	if ($result->num_rows > 0) {
		$res = array();
		$idx = 0;

		while($row = $result->fetch_assoc()) {
			$res[$idx] = $row;
			$idx = $idx + 1;
		}
		echo json_encode($res);
		exit();
	} else {
		echo "0 results";
	}
}

/**
 * cette focntion findAll() est comme en TD8 mais elle possède moins de lignes car elle ne s'occupe désormais que de trouver les éléments. 
 * Le traitement du réusltat obtenu est exporté vers parseResults() qui sera alors utilisé dans plusieurs fonctions.
 * 
 * 
 * @param mysqli $conn objet de connexion mysqli
 */
function findAll($conn) {
	$sql = "SELECT * FROM vetements";
	$result = $conn->query($sql);
	parseResults($result);
}

/**
 * fonction findBy() dédiée ) trouver (find) dans la base de données par (by) un filtre. bien que nous utilisons un seule filtre sur la colonne "gender", cette fonction est assez abstraite pour permettre de s'adapter à toutes les colonnes :) C'est pouruqoi nous considérons les arguments du nom de colonne et de la valeur à chercher
 * @param mysqli $conn objet de connexion mysqli
 * @param string $filterColumn nom de la colonne sur laquelle filtrer
 * @param string $filterValue valeur servant à filtrer
 */
function findBy($conn, $filterColumn, $filterValue) {
	$statement = $conn->prepare("SELECT * FROM vetements WHERE $filterColumn = ? ;");
	$statement->bind_param("s", $filterValue);
	$statement->execute();
	$result = $statement->get_result();
	parseResults($result);
}

/**
 * fonction dédiée à l'ajout d'un article dans la base de données
 * réutilise la fonction findall
 * @param mysqli $conn objet de connexion mysqli
 * @param array $data tableau associatif représentant les infos de l'article à ajouter
 */
function add($conn, $data) {
	$statement = $conn->prepare("INSERT INTO vetements (id, gender, productDisplayName) VALUES (?, ?, ?);");
	$statement->bind_param("iss", $data['id'], $data['gender'], $data['productDisplayName']);
	$statement->execute();
	$result = $statement->get_result();
	findAll($conn);
}

/**
 * fonction de suppression d'un article dans la base de données.
 * réutilise la fonction findall
 * @param mysqli $conn objet de connexion mysqli
 * @param int $id identifiant de l'article
 */
function delete($conn, $id) {
	$statement = $conn->prepare("DELETE FROM vetements WHERE id = ? ;");
	$statement->bind_param("i", $id);
	$statement->execute();
	$result = findAll($conn);
}


$conn = connect();

if (count($_GET)){
	if (isset($_GET["filterby"])){
		findBy($conn, $_GET["filterby"], $_GET["filterval"]);
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$_POST = json_decode(file_get_contents('php://input'), true); 
	
	if (count($_POST)) {
		if (isset($_POST["action"])){
			if ($_POST["action"] == "add") {
				add($conn, $_POST);
			}else if ($_POST["action"] == "delete") {
				delete($conn, $_POST["id"]);
			}
		}
	}
}

$conn->close();

?>