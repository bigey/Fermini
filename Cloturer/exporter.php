<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// Verifie si l'autorisation est ok sinon on redirige sur la page d'authentification
if ( !$_SESSION['auth']==1 ) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( isset($_POST['retour']) ) {
	unset($_SESSION['message']);
	unset($_SESSION['liste']);
	header("Location: ../ListeFerm/listeferm_closed.php");
	exit;

// Le bouton Exporter est appuyé & une liste d'ids existe dans SESSION
// } elseif ( isset($_SESSION['liste']) ) {

} elseif ( isset($_SESSION['liste']) && isset($_POST['exporter']) ) {

	if ( !empty($_POST['nomFichier']) ) {	// un nom de fichier est dispo

		foreach ($_SESSION['liste'] as $idFermentation) {
			$idList .= "$idFermentation ";
		}

		$path_to_file='/tmp';
		$return=export_data($_POST['nomFichier'],$idList);

		if ( $return==0 ) {
			header("Content-Disposition: attachment; filename={$_POST['nomFichier']}.ods");
			header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
			readfile("$path_to_file/{$_POST['nomFichier']}.ods");
			exit;

		} else {
			$_SESSION['message']="ERREUR: $return - l'exportation a échouée !";
			header("Location: ".$_SERVER['PHP_SELF']);
			exit;
		}

	} else {
		$_SESSION['message'] = 'ERREUR: merci de mettre un mon de fichier !';
		header("Location: {$_SERVER['PHP_SELF']}");
		exit;
	}
	

} elseif ( isset($_SESSION['liste']) ) {


  // Creation d'un nom de fichier aleatoire et afichage de la page
  $fileName = 'fermini_' . $_SESSION['utilisateur']['id'] . '_' . rand_str();

  haut_de_page("Liste des fermentations terminées");

// Debut du block de formatage
$page=<<<EOF
<h1>Exportation des données</h1>
<form id="form_167188" class="appnitro" method="post" action="">

	<div class="form_description">
		<h2>Exportation au format OpenOffice Calc (*.ods)</h2>
		<p>Souhaitez-vous modifier le nom du fichier ?</p>
		<p class='error'>{$_SESSION['message']}</p>
	</div>

	<ul >
		<li>
		  <label class="description" for="element_3">Nom du fichier d'exportation</label>
		  <div>
			<input id="element" name="nomFichier" class="element text medium" type="text" maxlength="100" value="$fileName"/>
		  </div>
		  <p class="guidelines" id="guide"><small>Il est possible de modifier le nom du fichier </small></p>
		</li>

		<li class="buttons">
		  <input type="hidden" name="form_id" value="167188" />
		  <button id='exporter' name='exporter' type='submit' title='Exporter les données' value='1'></button>Exporter
		  <button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
		</li>
	</ul>

</form>
EOF;
// Fin du block de formatage

		echo $page;
		bas_de_page();
		exit;

} else {
	$_SESSION['message'] = 'ERREUR: vous n\'avez pas sélectionné de fermentation !';
	unset($_SESSION['liste']);
	header("Location: ../ListeFerm/listeferm_closed.php");
	exit;
}

?>
