<?php
// permet de démarrer la session
// et donc les activer
session_start();
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>TempleDuSWAG</title>
		<link rel="icon" href="img/logo.png" />
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
	</head>
	<body>
		<div class="container">
			<div class="row">
				<!-- formulaire qui renvoie vers cette même page. Par méthode POST -->
				<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
					<div class="form-group">
						<input type="email" name="email" class="form-control"  aria-describedby="emailHelp" placeholder="Adresse email">
					</div>
					<div class="form-group">
						<input type="password" name="mdp" class="form-control"  placeholder="Mot de passe">
					</div>
					<div class="form-group form-check">
						<input type="checkbox" name="inscription" class="form-check-input" id="modeInscription">
						<label class="form-check-label" for="modeInscription">Inscription</label>
					</div>
					<button type="submit" class="btn btn-primary">Envoyer</button>
				</form>
			</div>
		</div>

		<?php
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
		 * fonction d'ajout d'un utilisateur
		 * @param mysqli $conn objet mysqli instancié
		 * @param string $email email donné pour l'ajout
		 * @param string $mdp mot de passe donné pour l'ajout
		 */
		function addUser($conn, $email, $mdp) {
			// on crypte le mot de passe par une méthode de hashage
			$cryptedMdp = hash('sha384', $mdp);

			// on peut désormais exécuter la requête SQL
			$statement = $conn->prepare("INSERT INTO users(`email`, `mdp`) VALUES (? , ?);");
			$statement->bind_param("ss", $email, $cryptedMdp);
			$statement->execute();
			$result = $statement->get_result();
		}

		/**
		 * focntion pour vérifier, à partir de l'email (qui est la clé primaire et donc unique), si un
		 * utilisateur existe.
		 * @param mysqli $conn objet mysqli instancié
		 * @param string $email email de l'utilisateur (c'est la clé primaire)
		 */
		function checkIfExists($conn, $email) {
			// on exécute la requête SQL
			$statement = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? ;");
			$statement->bind_param("s", $email);
			$statement->execute();
			$result = $statement->get_result();
			// on prépare une variable count
			$count = 0;
			// on accède au contenu
			while($row = $result->fetch_assoc()) {
				// ici nous accédons à la seule clé disponbile dans le tableau associatif
				// cette clé est différente car c'est le résultat de la focntion COUNT(*) de SQL
				$count = $row["COUNT(*)"]; // c'est un numérique
			}
			if ($count > 0){ return true; }
			else { return false; }
		}

		/**
		 * focntion qui sert à se connecter. Elle vérifie (check) donc l'exactitude des identifiant/motdepasse donnés
		 * avec ce qu'il y a dans la base de données
		 * @param mysqli $conn objet mysqli instancié
		 * @param string $email email de l'utilisateur
		 * @param string $mdp mot de passe de l'utilisateur
		 */
		function checkUser($conn, $email, $mdp){
			// on crypte le mot de passe avec une fonction de hashage
			$cryptedMdp = hash('sha384', $mdp);
			// pas besoin de récupérer le mot de passe car on le vérifie déjà par la requête SQL
			$statement = $conn->prepare("SELECT (email) FROM users WHERE email = ? AND mdp = ? ;");
			$statement->bind_param("ss", $email, $cryptedMdp);
			$statement->execute();
			$result = $statement->get_result();

			$user = null; // on met l'utilisateur en null, une variable "vide"
			if ($result->num_rows > 0) {
				// s'il y a quelque chose qui est trouvé alors on remplace
				// donc si rien n'est trouvé, la variable $user restera null
				while($row = $result->fetch_assoc()) {
					$user = $row;
				}
			}
			// n'oublions pas de retourner l'utilisateur (ici au format tableau associatif)
			return $user;
		}

		$conn = connect();

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			
			if (count($_POST)) {
				// on récupère les infos de la requête POST
				$email = $_POST['email'];
				$mdp = $_POST["mdp"];
				// isset retourne un booléen, c'est donc très pratique ici 😀
				$inscription = isset($_POST["inscription"]);
				
				if ($inscription) {
					// si le mode inscription alors
					// on vérifie si l'utilisateur n'existe pas déjà
					$exists = checkIfExists($conn, $email);
					if ($exists){
						// s'il existe on affiche
						echo "<p> L'utilisateur existe déjà ! 😒</p>";
					}else{
						// sinon on ajoute l'utilisateur
						addUser($conn, $email, $mdp);
						// puis on affiche
						echo "<p> L'utilisateur a bien été créé. Veuillez vous connecter.</p>";
					}
				}else {
					// si ce n'est pas le mode d'inscription alors on fait la connexion
					// on vérifie si les identifiants sont bons et présent dans la BDD
					$user = checkUser($conn, $email, $mdp);
					if (is_null($user)) {
						// si l'utilisateur est null (donc les identifiants sont erronés) on affiche puis quitte le script
						echo "<p> FAUX 😡 identifiant ou mot de passe incorrect. Essaie encore ! </p>";
						exit();
					}

					// on ajoute à la session (sera effectué uniquement si $user n'est pas null)
					$_SESSION["email"] = $user["email"];
					// on redirige vers index.php. il y a rrivera avec le bon cookie de session :D
					header("Location: index.php");
					die();
				}
			}
		}

		$conn->close();
		
		?>

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
	</body>
</html>
