<?php

require_once("../constantes.php");
require_once("../fonctions.php");

session_start();

if ( isset($_POST['idUtilisateur']) || isset($_POST['motPass']) ) {

	// Formulaire recu
	// Verifie les valeurs
	if (empty($_POST['idUtilisateur'])) {
		$_SESSION['message'] = "ERREUR: Veuillez entrer un login !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

	if (empty($_POST['motPasse'])) {
		$_SESSION['message'] = "ERREUR: Veuillez entrer un mot de passe !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}
	
	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");
	
	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit la requete
	// Les données doivent être "échappées" proprement !
	foreach ($_POST as $key => $value) {
		$_POST[$key] = mysql_real_escape_string($value);
	}

	$query = "SELECT * FROM Utilisateur WHERE idUtilisateur = '" . $_POST['idUtilisateur'] . "' AND motPasse = SHA1('" . $_POST['motPasse'] . "')";
	
	// Execute la requete
	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
	
	// Regarde les valeurs retournees par la base
	if ( mysql_num_rows($result)==1 ) {

		$row=mysql_fetch_assoc($result);
		$nom = $row['nom'];
		$prenom = $row['prenom'];
		$typeUtilisateur = $row['typeUtilisateur'];

		// Si une ligne est retournée,
		// l'authentification est bonne

		$_SESSION['auth']			= 1;
		$_SESSION['utilisateur']['id']		= $_POST['idUtilisateur'];
		$_SESSION['utilisateur']['nom']		= $nom;
		$_SESSION['utilisateur']['prenom']	= $prenom;
		$_SESSION['utilisateur']['type']	= $typeUtilisateur;
		$_SESSION['message']			= "Bienvenue $prenom $nom ({$_POST['idUtilisateur']}) !";

		// Cree le cookie avec le nom d'utilisateur et la session
		// Validite de 1 jour(s)
		setcookie("userid", $_POST['idUtilisateur'], time()+(84600*1));

		// redirige vers la page d'accueil
		header("Location: ../Accueil/accueil.php");

	} else {

		// authentification impossible
		//echo "ERREUR: Login ou Mot de passe incorrect!";
		$_SESSION['message'] = "ERREUR: Login ou Mot de passe incorrect !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}
	
	// Libere le resultat
	mysql_free_result($result);
	
	// Ferme la connexion
	mysql_close($connection);

} else {
	// pas d'envoi
	// affiche le formulaire

	haut_de_page();

$page=<<<EOF
<h1><a>Page d'identification</a></h1>
<form id="form_167767" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Base de fermentation Fermini</h2>
		<p>Merci de vous identifier. </p>
		<p class="error">{$_SESSION['message']}</p>
	</div>
	<ul >
		<li id="li_1" >
			<label class="description" for="element_1">Identifiant utilisateur </label>
			<div>
				<input id="element_1" name="idUtilisateur" class="element text medium" type="text" maxlength="255" value="{$_COOKIE['userid']}"/>
			</div>

			<p class="guidelines" id="guide_1"><small>Entrer votre nom</small></p>
		</li>

		<li id="li_2" >
			<label class="description" for="element_2">Môt de passe </label>
			<div>
				<input id="element_2" name="motPasse" class="element text medium" type="password" maxlength="255" value=""/>
			</div>

			<p class="guidelines" id="guide_2"><small>Entrer votre môt de passe</small></p>
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="167767" />
			<button id="connexion" type="submit" name="connexion" value="1" title="Connexion"></button>Connexion
			<button id="effacer" type="reset" name="effacer" value="1" title="Effacer"></button>Effacer
		</li>
	</ul>
</form>
EOF;

	echo $page;
	bas_de_page();
}

exit;

?>