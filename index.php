<?php
// permet de démarrer la session
// et donc les activer
session_start();
if ( !isset($_SESSION["email"]) ){
	// ajouter ce header déclenche une redirection
	header("Location: login.php");
	// on arrête le script au cas où la redirection n'a pas fonctionné
	die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST")  {
	// si la méthode est POST
	if (isset($_POST["logout"])) {
		// si la clé "logout" est présente
		// alors on détruit la session
		session_destroy(); 
		// puis on redirige
		header("Location: login.php"); 
		die();
	}
}
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>TempleDuSWAG</title>
		<link rel="icon" href="img/logo.png" />
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
	</head>
	<body>

		<nav class="navbar navbar-expand-sm bg-light navbar-light fixed-top">
			<div class="container-fluid">
				<ul class="navbar-nav">
					<li class="nav-link" href="http://localhost:8888/td11/index.php">
					<a class="navbar-brand" href="http://localhost:8888/td11/index.php"><img src="img/logo.png" height="24"></a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" href="http://localhost:8888/td11/index.php"><i class="bi bi-house-fill"> Home</i></a>
					</li>
				</ul>
				<!-- nouvelle liste non ordonnée placée à droite (navbar-right) -->
				<ul class="navbar-nav navbar-right">
					<li class="nav-item">
					 <?php 
					 	// on accède à la valeur email de la session pour l'afficher (indiquer qu'on est connecté)
						echo $_SESSION['email'];
					?>
					</li>
					<li>
						<!-- bouton par formulaire pour éviter l'AJAX (on souhaite gérer les connexion sans AJAX dans ce TD) -->
						<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
							<button class="btn btn-danger" name="logout" value="logout" type="submit">Logout</button>
						</form>
					</li>
				</ul>
			</div>
		</nav>

		<div class="container-fluid text-center" style="margin-top:80px">
			<a class="navbar-brand" href="http://localhost:8888/td11/index.php"><img src="img/logo.png" height="200"></a>
			<p>Bienvenue dans le super site des étudiants en L2 MIASHS 😊</p> 
		</div>

		<div class="container">
			<form class="row align-items-center ajaxform" method="post" action="/td11/api.php">
				<div class='col-auto'>
						<input type="number" name="id" class="form-control" id="id" placeholder="Identifiant">
				</div>
				<div class='col-auto'>
						<input type="text" name="productDisplayName" class="form-control" id="productDisplayName" placeholder="Description">
				</div>
				<div class="col-auto">
						<select class="form-select" name="gender" aria-label="gender" id="gender" placeholder="Genre">
							<option selected>Genre à choisir...</option>
							<option value="Men">Masculin</option>
							<option value="Women">Feminin</option>
						</select>
				</div>
				<div class="col-auto">
					<button type="submit" class="btn btn-primary">Envoyer</button>
				</div>
				</div>
			</form>
		</div>



		<?php 
			$db_host = 'localhost';
			$db_user = 'root';
			$db_password = 'root';
			$db_db = 'produits';
			$db_port = 8889;

			// Create connection
			$conn = new mysqli($db_host, $db_user, $db_password, $db_db);

			// Check connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			function findAll($conn) {
				$sql = "SELECT * FROM vetements";
				$result = $conn->query($sql);
				$data = [];
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						array_push($data, $row);
					}
				} else {
					echo "Aucun résultat 😐";
				}
				return $data;
			}

			$data = findAll($conn);

			$conn->close();
		?>

		<div class="container mt-5">
			<div class="row">
				<?php
					$nbres = "<div class='col-sm-4'>
						<h5>". count($data) ." produits trouvés</h5>
					</div></div>";
					echo $nbres;
					echo '<div id="produitsAffichage" class="row gx-5 gy-5">';

					for ($i = 0; $i < count($data); $i++) {
						$genderIcon = "";
						if ($data[$i]['gender'] == "Women"){ $genderIcon = "<i class='bi bi-gender-female'></i>"; }
						else{ $genderIcon = '<i class="bi bi-gender-male"></i>';  } 
						$card = "
						<div class='col-sm-4'>
							<div class='card text-bg-light'>
								<img class='card-img-top' src='img/vetements/{$data[$i]['id']}.jpg' alt='Card image cap'>
								<div class='card-body'>
									<h4 class='card-title'>{$data[$i]['productDisplayName']}</h4>
									<p class='card-text'>
										<span class='badge bg-secondary'>{$data[$i]['articleType']}</span>
										<span class='badge bg-secondary'>{$data[$i]['baseColour']}</span>
										<span class='badge bg-dark'>{$data[$i]['year']}</span>
										<button class='btn btn-secondary btn-sm' onclick='filterByGender(\"" . $data[$i]["gender"] . "\")' >$genderIcon</button>
										<button class='btn btn-danger btn-sm' onclick='remove(". $data[$i]["id"] .")'><i class='bi bi-trash3-fill'></i></button>
									</p>
								</div>
							</div>
						</div>";
						echo $card;
					}
				?>
			</div>
		</div>

		
	
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
		<script type="text/javascript" src="swag.js"></script>
  </body>
</html>
