<?php

require_once("../fonctions.php");

// start session
session_start();

// verifie si l'autorisation est ok sinon die avec erreur
if (!$_SESSION['auth'] == 1) {
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( !isset($_SESSION['fermentation']['souche']) ) {
	$_SESSION['message'] = "ERREUR: Déclaration impossible !";
	header("Location: ../Accueil/accueil.php");
	exit;

} elseif ( $_POST['retour'] ) {
	unset($_SESSION['message']);
	unset($_SESSION['fermentation']);
	header("Location: ../Accueil/accueil.php");
	exit;

} elseif ( $_POST['suivant'] ) {

	// Submit formulaire
	// Sauvez les infos dans la variable $_SESSION['fermentation']

	if ( empty($_POST['milieu']) or empty($_POST['type']) ) {
		$_SESSION['message'] = 'ERREUR: les champs "Milieu de culture" et "Type de fermenteur" doivent être remplis !';
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;

	} elseif ( !is_numeric($_POST['temperature']) ) {
		$_SESSION['message'] = 'ERREUR: le champs "Température" doit être numérique !';
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;

	} elseif ( !is_numeric($_POST['volume']) or $_POST['volume']<=0 or $_POST['volume']>1 ) {
		$_SESSION['message'] = 'ERREUR: le Volume doit être entre 0 et 1 litre !';
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;

	}

	$culture = array(
				"milieu" => $_POST['milieu'],
				"oxygene" => $_POST['oxygene'],
				"type" => $_POST['type'],
				"temperature" => $_POST['temperature'],
				"volume" => $_POST['volume']
			);
			
	$_SESSION['fermentation']["culture"] = $culture;

	// Saut sur la page poste de fermentation
	unset($_SESSION['message']);
	header("Location: ../Declaration/poste.php");
	exit;

} elseif ( $_POST['submit_idConditionCulture'] ) {

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect to mySQL server !");

	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit la requete pour rechercher si la souche est déja dans la DB
	$query =	"
			SELECT * FROM ConditionCulture WHERE idConditionCulture = '{$_POST['idConditionCulture']}'
			";

	// Execute la requete
	$result = mysql_query($query) or die ("Error in query: $query. " . mysql_error());

	// La souche existe déja dans la base
	if (mysql_num_rows($result) >= 1) {

		// si au moins une ligne est retournée,
		// le milieu est déja dans la base

		$record = mysql_fetch_assoc($result);

		$culture = array(
					"insert" => 1,
					"idConditionCulture" => $record['idConditionCulture']
				);
				
		$_SESSION['fermentation']["culture"] = $culture;

		// Saut sur la page poste de fermentation
		unset($_SESSION['message']);
		header("Location: ../Declaration/poste.php");

	} else {
		// Le milieu n'existe pas encore
		$_SESSION['message'] = 'Ce milieu n\'est pas référencé dans la base de donnée !';

		// le numéro n'est pas dans la base
		// donc il faut le formutlaire complet pour toute les informations

		haut_de_page("condition de culture");

$page = <<<EOF
<h1><a>Conditions de culture</a></h1>
<form id="form_167188" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Conditions de culture</h2>
		<p>Données relatives à la compostion du milieu et aux conditions de culture</p>
		<p class="error">{$_SESSION['message']}</p>
	</div>
	<ul>
		<li id="li_2" >
			<label class="description" for="element_2">Milieu de culture </label>
			<div>
			<textarea id="element_2" name="milieu" class="element textarea small"></textarea>
			</div><p class="guidelines" id="guide_2"><small>Décrire le milieu de culture utilisé</small></p>
		</li>

		<li id="li_11" >
			<label class="description" for="element_1">Volume de milieu</label>
			<div>
			<input id="element_4" name="volume" class="element text small" type="text" maxlength="7" value="0.3"/>
			</div><p class="guidelines" id="guide_1"><small>Entrer le volume de milieu. Doit être entre 0 et 1 litre</small></p>
		</li>

		<li id="li_3" >
			<label class="description" for="element_3">Type de fermenteur </label>
			<div>
			<input id="element_3" name="type" class="element text large" type="text" maxlength="255" value="mini-fermenteur"/>
			</div><p class="guidelines" id="guide_3"><small>Type de fermenteur utilisé. ex: Erlen, mini-fermenteur, avec ou sans cloche</small></p>
		</li>

		<li id="li_4" >
			<label class="description" for="element_4">Température </label>
			<div>
			<input id="element_4" name="temperature" class="element text small" type="text" maxlength="3" value="28"/>
			</div><p class="guidelines" id="guide_4"><small>Température de fermentation (°C)</small></p>
		</li>

		<li id="li_1" >
			<label class="description" for="element_1">Oxygène </label>
			<div>
			<textarea id="element_1" name="oxygene" class="element textarea small"></textarea>
			</div><p class="guidelines" id="guide_1"><small>Conditions d'aération du milieu</small></p>
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="167188" />
			<button id="valider" type="submit" name="suivant" value="1" title="Suivant"></button>Suivant
			<button id="retour" type="submit" name="retour" value="1" title="Retour"></button>Retour
			<button id="effacer" type="reset" name="effacer" value="1" title="Effacer"></button>Effacer
		</li>
	</ul>
</form>
EOF;

		echo $page;
		bas_de_page();
	}

	mysql_free_result($result);
	mysql_close($connection);

} else {

	// Affiche le formulaire de saisie de l'identifiant de culture

	haut_de_page("Condition de culture");

$page = <<<EOF
<h1><a>Conditions de culture</a></h1>

<form id="form_167188" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Conditions de culture</h2>
		<p>Données relatives à la compostion du milieu et aux conditions de culture</p>
		<p class=error>{$_SESSION['message']}</p>
	</div>

	<ul>
		<li id="li_1" >
			<label class="description" for="element_1">Le milieu de culture a t-il un identifiant dans la base ? </label><br />
			<label class="description" for="element_1">si oui entrez son numéro </label>
			<input id="element_1" name="idConditionCulture" class="element text small" type="text" maxlength="255" value=""/>
			<p class="guidelines" id="guide_1"><small>Numéro d'enregistrement du milieu dans la base de donnée Fermini</small></p>
		</li>

		<li class="buttons">
			<button id="valider" type="submit" name="submit_idConditionCulture" value="1" title="Suivant"></button>Suivant
			<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
		</li>

	</ul>

</form>
EOF;

	echo $page;
	bas_de_page();


}

exit;

?>