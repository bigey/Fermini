<?php

require_once("../constantes.php");
require_once("../fonctions.php");

// start session
session_start();
unset($_SESSION['fermentation']);

// Verifie si l'autorisation est ok OU deconnexion
if ( !$_SESSION['auth']==1 || $_POST['deconnexion'] ) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( $_POST['valider'] ) {

	unset($_SESSION['message']);

	// Teste la valeur de retour pour definir l'action a effectuer
	switch ($_POST['reponse']) {
		case 1:
			header("Location: ../Declaration/souche.php");
			break;
		case 2:
			header("Location: ../ListeFerm/listeferm.php");
			break;
		case 3:
			header("Location: ../ListeFerm/listeferm_closed.php");
			break;
		default:
			header("Location: {$_SERVER['PHP_SELF']}");
			break;
	}

} elseif ( $_POST['admin'] && $_SESSION['utilisateur']['type']=='admin' ) {
  header("Location: ../Administration/admin.php");
  exit;

} else {

	// Affiche le formulaire

	haut_de_page("Fermini: accueil");

$page=<<<EOF
<h1><a>Fermini: accueil</a></h1>
<form id="form_167767" class="appnitro"  method="post" action="">

	<div class="form_description">
		<h2>Menu principal</h2>
		<p>Choisisser parmis les options suivantes</p>
		<p class='error'>{$_SESSION['message']}</p>
	</div>

	<ul >
		<li id="li_1" >
			<label class="description" for="element_1">Que voulez-vous faire ? </label>

			<span>
				<input id="element_1_1" name="reponse" class="element radio" type="radio" value="1" checked="checked"/>
				<label class="choice" for="element_1_1">Démarer une nouvelle fermentation</label>
				<input id="element_1_2" name="reponse" class="element radio" type="radio" value="2" />
				<label class="choice" for="element_1_2">Afficher les fermentations en cours</label>
				<input id="element_1_3" name="reponse" class="element radio" type="radio" value="3" />
				<label class="choice" for="element_1_3">Afficher les fermentations terminées</label>
			</span>

			<p class="guidelines" id="guide_1"><small>Choisir une des trois actions suivantes</small></p>
		</li>

		<li class="buttons">
			<button id="valider" type="submit" name="valider" value="1" title="Valider"></button>Valider
			<button id="deconnexion" type="submit" name="deconnexion" value="1" title="Déconnexion"></button>Déconnexion
			<button id="administration" type="submit" name="admin" value="1" title="Administration"></button>Administration
		</li>
	</ul>

</form>
EOF;
	echo $page;
	bas_de_page();
}

exit;

?>
