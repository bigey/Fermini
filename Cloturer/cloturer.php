<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// Verifie si l'autorisation est ok sinon on redirige sur la page d'authentification
if (!$_SESSION['auth']==1) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

// Un bouton est appuyé et on traite l'évènememnt
} elseif ( isset($_POST['retour']) ) {
	unset($_SESSION['message']);
	unset($_SESSION['fermentation']);
	header("Location: ../ListeFerm/listeferm.php");
	exit;

} elseif ( isset($_POST['cloturer']) and isset($_SESSION['fermentation']['idFermentation']) ) {

	$id = $_SESSION['fermentation']['idFermentation'];

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");

	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");


	if ( $_SESSION['fermentation']['statut']==1 ) {

		$query = "UPDATE Fermentation SET statut='0' WHERE idFermentation='$id'";

		mysql_query($query) ? $_SESSION['fermentation']['statut']=0 : die ("Error in query: $query\n" . mysql_error());
	}

	// Ferme la connexion
	mysql_close($connection);

	$_SESSION['message'] = "INFO: la fermentation n° $id est cloturée !";

	unset($_SESSION['fermentation']);
	header("Location: ../ListeFerm/listeferm.php");
	exit;


} elseif ( isset($_SESSION['fermentation']['idFermentation']) ) {  // On arrive sur la page

	$id = $_SESSION['fermentation']['idFermentation'];

	haut_de_page("Cloturer une fermentation");

// Debut du block de formatage
$page=<<<EOF
<h1><a></a></h1>
<form id="form_167188" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Cloturer une fermentation</h2>
		<p>Voulez-vous cloturer la fermentation n° $id?</p>

	</div>
		<ul >
			<li>Etes-vous certain de vouloir cloturer la fermentation n° <strong>$id</strong> ?</li>

			<li></li>

			<ul>
				<li>Si oui, appuyer alors sur <strong>"Cloturer"</strong></li>
				<li>Si non, faite <strong>"Annuler"</strong></li>
			</ul>

			<li class="buttons">
				<input type="hidden" name="form_id" value="167188" />
				<button id="cloturer" type="submit" name="cloturer" value="cloturer" title="Cloturer la fermentation"></button>Cloturer
				<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
			</li>
		</ul>
</form>
EOF;
// Fin du block de formatage

	echo $page;
	bas_de_page();

} else {
	unset($_SESSION['message']);
	unset($_SESSION['fermentation']);
	header("Location: ../ListeFerm/listeferm.php");
	exit;
}

exit;

?>
