<?php

require_once("../constantes.php");
require_once("../fonctions.php");

// start session
session_start();
unset($_SESSION['fermentation']);

// Verifie si l'autorisation est ok OU deconnexion
if ( $_SESSION['auth']!=1 || $_SESSION['utilisateur']['id']!='admin' || $_POST['bouton']=='deconnexion' ) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( $_POST['bouton']=='annuler' ) {
	header("Location: ../Accueil/accueil.php");
	exit;

} elseif ( $_POST['bouton']=='creer' ) {

	if (empty($_POST['newIdUtilisateur'])) {
		$_SESSION['message'] = "ERREUR: Veuillez entrer un login !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

	if (empty($_POST['motPasse'])) {
		$_SESSION['message'] = "ERREUR: Veuillez entrer un mot de passe !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

	if (empty($_POST['nom'])) {
		$_SESSION['message'] = "ERREUR: Veuillez entrer le nom de l'utilisateur !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

	if (empty($_POST['prenom'])) {
		$_SESSION['message'] = "ERREUR: Veuillez entrer le prénom de l'utilisateur !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");
	
	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Les données doivent être "échappées" proprement !
	foreach ($_POST as $key => $value) {
		$_POST[$key] = mysql_real_escape_string($value);
	}

	// Construit la requete
	$query = "SELECT * FROM Utilisateur WHERE idUtilisateur = '{$_POST['newIdUtilisateur']}'";
	
	// Execute la requete
	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
	
	// Regarde les valeurs retournées par la base
	if ( mysql_num_rows($result)==0 ) {

		// Construit la requete d'insertion
		$query =	"
					INSERT INTO Utilisateur (
							idUtilisateur,
							nom,
							prenom,
							motPasse,
							typeUtilisateur
					)
					VALUES (
							'{$_POST['newIdUtilisateur']}',
							'{$_POST['nom']}',
							'{$_POST['prenom']}',
							SHA1('{$_POST['motPasse']}'),
							'user'
					)
					";

		// Execute la requete
		mysql_query($query) or die ("Error in query: $query. " . mysql_error());
		mysql_close($connection);
		$_SESSION['message'] = "La création de l'utilisateur {$_POST['newIdUtilisateur']} a réussi !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;

	} else { // Un uilisateur a déja cet id
		mysql_close($connection);
		$_SESSION['message'] = "ERREUR: Cet identifiant existe déja !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

} elseif ( $_POST['bouton']=='suprimer' ) {

	if (empty($_POST['idUtilisateur'])) {
		$_SESSION['message'] = "ERREUR: Veuillez selectioner un utilisateur !";
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");
	
	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit la requete de 
	$query =	"
				DELETE U.*, F.*, A.*, P.* 
				FROM 
					Utilisateur AS U LEFT JOIN Fermentation AS F ON F.Utilisateur_idUtilisateur=U.idUtilisateur
					LEFT JOIN Acquisition AS A ON A.Fermentation_idFermentation=F.idFermentation
					LEFT JOIN Prelevement AS P ON P.idPrelevement=A.Prelevement_idPrelevement
				WHERE 
					U.idUtilisateur='{$_POST['idUtilisateur']}'
				";

	// Execute la requete et ferme la connexion
	$results=mysql_query($query) or die ("Error in query: $query. " . mysql_error());
	mysql_free_result($results);
	mysql_close($connection);

	$_SESSION['message'] = "L'utilisateur {$_POST['idUtilisateur']} a été suprimer !";
	header("Location: ".$_SERVER['PHP_SELF']);
	exit;

} else {

	// Recherche la liste des utilisateurs

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");
	
	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit la requete
	$query = "SELECT * FROM Utilisateur WHERE idUtilisateur != 'admin'";
	
	// Execute la requete
	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());
	
	// Regarde les valeurs retournees par la base
	$utilisateurs = mysql_fetch_all($result);
	mysql_free_result($result);

	// Ferme la connexion
	mysql_close($connection);

	// Affiche le formulaire
	haut_de_page("Fermini: accueil");

$page=<<<EOF
<h1><a>Fermini: administration</a></h1>
<form class="appnitro" method="post" action="">

	<div class="form_description">
		<h2>Page d'administration</h2>
		<p>Choisisser parmis les options suivantes</p>
		<p class='error'>{$_SESSION['message']}</p>
	</div>

	<ul>
		<li>
			<h3>Création d'un utilisateur</h3>
		</li>

		<li>
			<label class="description">Nom </label>
			<div>
				<input name="nom" class="element text medium" type="text" maxlength="255" value="" />
			</div>
			<p class="guidelines"><small>Nom de l'utilisateur</small></p>
		</li>

		<li>
			<label class="description">Prénom </label>
			<div>
				<input name="prenom" class="element text medium" type="text" maxlength="255" value="" />
			</div>
			<p class="guidelines"><small>Prénom de l'utilisateur</small></p>
		</li>

		<li>
			<label class="description">Identifiant </label>
			<div>
				<input name="newIdUtilisateur" class="element text medium" type="text" maxlength="255" value="" />
			</div>
			<p class="guidelines"><small>Identifiant de l'utilisateur (login)</small></p>
		</li>

		<li>
			<label class="description">Mot de passe </label>
			<div>
				<input name="motPasse" class="element text medium" type="text" maxlength="255" value="" />
			</div>
			<p class="guidelines"><small>Mot de passe de l'utilisateur (password)</small></p>
		</li>

		<li class="buttons">
			<button id="valider" type="submit" name="bouton" value="creer" title="Créer"></button>Créer
			<button id="retour" type="submit" name="bouton" value="annuler" title="Annuler"></button>Annuler
			<button id="deconnexion" type="submit" name="bouton" value="deconnexion" title="Déconnexion"></button>Déconnexion
		</li>

		<li>
			<h3>Supression d'un utilisateur</h3>
		</li>

		<li>
		  <label class="description">Utilisateur à supprimer </label>
		  <div>
			<select size="5" style="width:300px" name="idUtilisateur">

EOF;
	echo $page;


	// Affiche tous les utilisateurs de la base
	foreach ($utilisateurs as $userRecord) {
		$idUser	= $userRecord['idUtilisateur'];
		$nom	= $userRecord['nom'];
		$prenom	= $userRecord['prenom'];
		$type	= $userRecord['typeUtilisateur'];
		echo "<option value=\"$idUser\">$prenom $nom (user:$idUser; type:$type)</option>";
	}


$page=<<<EOF
			</select>
		  </div>
		</li>

		<li class="buttons">
			<button id="annuler" type="submit" name="bouton" value="suprimer" title="Suprimer"></button>Suprimer
			<button id="retour" type="submit" name="bouton" value="annuler" title="Annuler"></button>Annuler
			<button id="deconnexion" type="submit" name="bouton" value="deconnexion" title="Déconnexion"></button>Déconnexion
		</li>
	</ul>
</form>
EOF;
	echo $page;

	bas_de_page();
}

exit;

?>
