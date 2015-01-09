<?php

require_once("../fonctions.php");

// start session
session_start();

// verifie si l'autorisation est ok sinon die avec erreur
if (!$_SESSION['auth'] == 1) {
	
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( !isset($_SESSION['fermentation']['souche']) or !isset($_SESSION['fermentation']['culture']) ) {
	$_SESSION['message'] = "ERREUR: Déclaration impossible !";
	header("Location: ../Accueil/accueil.php");
	exit;

} elseif ( $_POST['retour'] ) {
	unset($_SESSION['message']);
	unset($_SESSION['fermentation']);
	header("Location: ../Accueil/accueil.php");
	exit;

} elseif ( $_POST['suivant'] ) {

	// Sauvez les infos dans la variable $_SESSION['fermentation']

	if ( empty($_POST['agitateur']) ) {
		$_SESSION['message'] = 'ERREUR: le champs "Nom de l\'agitateur" doivent être remplis !';
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;

	} elseif ( !is_numeric($_POST['vitesse']) or $_POST['vitesse']<0 or $_POST['vitesse']>1000 ) {
		$_SESSION['message'] = 'ERREUR: la vitesse doit être comprise entre 0 et 1000 tours/min !';
		header("Location: ".$_SERVER['PHP_SELF']);
		exit;

	}

	$poste = array(
				"agitateur" => $_POST['agitateur'],
				"ligne" => $_POST['ligne'],
				"colonne" => $_POST['colonne'],
				"balance" => $_POST['balance'],
				"vitesse" => $_POST['vitesse']
			);

	$_SESSION['fermentation']["poste"] = $poste;

	// Saut sur la page Déclaration
	unset($_SESSION['message']);
	header("Location: ../Declaration/declaration.php");
	exit;

} else {

	// Affiche le formulaire de saisie des conditions de culture
	haut_de_page("Poste de fermentation");

$page=<<<EOF
<h1><a>Poste de fermentation</a></h1>

<form id="form_167227" class="appnitro"  method="post" action="">

	<div class="form_description">
		<h2>Poste de fermentation</h2>
		<p>Données permettant de localiser le fermenteur sur l'agitateur</p>
		<p class=error>{$_SESSION['message']}</p>
	</div>

	<ul >

		<li id="li_1" >
			<label class="description" for="element_1">Agitateur </label>
			<div>
			<input id="element_1" name="agitateur" class="element text medium" type="text" maxlength="255" value="USAP-1"/>
			</div><p class="guidelines" id="guide_1"><small>Nom de l'agitateur utilisé</small></p>
		</li>

		<li id="li_4" >
			<label class="description" for="element_4">Position - ligne </label>
			<div>
			<select class="element select small" id="element_4" name="ligne">
				<option value="A" >A</option>
				<option value="B" >B</option>
				<option value="C" >C</option>
				<option value="D" >D</option>
				<option value="E" >E</option>
			</select>
			</div><p class="guidelines" id="guide_4"><small>Ligne sur laquelle est positionnée le fermenteur</small></p>
		</li>

		<li id="li_3" >
			<label class="description" for="element_3">Position - colonne </label>
			<div>
			<select class="element select small" id="element_3" name="colonne">
				<option value="1" >1</option>
				<option value="2" >2</option>
				<option value="3" >3</option>
			</select>
			</div><p class="guidelines" id="guide_3"><small>Ligne sur laquelle est positionnée le fermenteur</small></p>
		</li>

		<li id="li_5" >
			<label class="description" for="element_5">Vitesse d'agitation </label>
			<div>
			<input id="element_5" name="vitesse" class="element text small" type="text" maxlength="5" value="220"/>
			</div><p class="guidelines" id="guide_5"><small>Vitesse en tours/min [0 à 1000]</small></p>
		</li>

		<li id="li_2" >
			<label class="description" for="element_2">Balance </label>
			<div>
			<input id="element_2" name="balance" class="element text medium" type="text" maxlength="255" value="Ohaus"/>
			</div><p class="guidelines" id="guide_2"><small>Nom de la balance utlisée pour la mesure du poids du fermenteur</small></p>
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="167227" />
			<button id="valider" type="submit" name="suivant" value="Suivant" title="Suivant"></button>Suivant
			<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
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