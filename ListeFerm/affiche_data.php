<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// Verifie si l'autorisation est ok sinon on redirige sur la page d'authentification
if (!$_SESSION['auth']==1) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( isset($_POST['retour']) ) {

	unset($_SESSION['message']);

	if ( $_SESSION['fermentation']['statut']==1 ) {
		unset($_SESSION['fermentation']);
		header("Location: listeferm.php");
		exit;

	} else {
		unset($_SESSION['fermentation']);
		header("Location: listeferm_closed.php");
		exit;
	}

} elseif ( $_POST['exporter'] and isset($_SESSION['fermentation']['idFermentation']) ) {
	$_SESSION['liste'][]=$_SESSION['fermentation']['idFermentation'];
	header("Location: ../Cloturer/exporter.php");
	exit;


// On arrive sur la page donc il doit exister un id de fermentation et un statut
} elseif ( isset($_SESSION['fermentation']['idFermentation']) and isset($_SESSION['fermentation']['statut']) ) {

	$id	= $_SESSION['fermentation']['idFermentation'];
	$statut	= $_SESSION['fermentation']['statut'];

	switch ( $statut ) {
	case 0:
		$statut='terminé';
		break;
	case 1:
		$statut='en cours';
		break;
	case 2:
		$statut='exporté';
		break;
	}


	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");

	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit les requetes
	$query = 	"
				SELECT 
				  idAcquisition, 
				  dateAcquisition, 
				  temps, 
				  poids, 
				  volumeRestant, 
				  mCO2, 
				  vCO2, 
				  Prelevement_idPrelevement
				FROM Acquisition
				WHERE Fermentation_idFermentation='$id' 
				ORDER BY dateAcquisition DESC
			";


	// Execute la requete
	$results = mysql_query($query) or die ("Error in query: " . mysql_error());
	$table = mysql_fetch_all($results);


	// Ferme la connexion
	mysql_free_result($results);
	mysql_close($connection);

	haut_de_page("Liste des acquisitions");

$page=<<<EOF
<h1><a>Fermentations</a></h1>

<form id="form_167188" class="appnitro" method="post" action="">

	<div class="form_description">
		<h2>Liste des acquisitions</h2>
		<p>Acquisitions disponibles pour la fermentation n° $id ($statut)</p>
		<p class="error">{$_SESSION['message']}</p>
	</div>

	<ul >
		<li>
		<table>

			<thead> <!--entête du tableau-->
				<tr>
					<th>ID</th>
					<th>Date</th>
					<th>Temps (h)</th>
					<th>Masse (g)</th>
					<th>Volume (L)</th>
					<th>CO2 cumulé (g/L)</th>
					<th>Vitesse CO2 (g/L/h)</th>
					<th>Prélèvement (ID)</th>
				</tr>
			</thead>

			<tfoot> <!--pied du tableau-->
				<tr>
					<th>ID</th>
					<th>Date</th>
					<th>Temps (h)</th>
					<th>Masse (g)</th>
					<th>Volume (L)</th>
					<th>CO2 cumulé (g/L)</th>
					<th>Vitesse CO2 (g/L/h)</th>
					<th>Prélèvement (ID)</th>
				</tr>
			</tfoot>

			<tbody>
EOF;

	echo $page;

	if ( !empty($table) ) {

		foreach ($table as $row) {

			echo "<tr>\n";

			echo "<td>{$row['idAcquisition']}</td>\n";
			echo "<td>{$row['dateAcquisition']}</td>\n";
			echo "<td>" . number_format($row['temps'], 2) . "</td>\n";
			echo "<td>" . number_format($row['poids'], 2) . "</td>\n";
			echo "<td>" . number_format($row['volumeRestant'], 3) . "</td>\n";
			echo "<td>" . number_format($row['mCO2'], 2) . "</td>\n";
			echo "<td>" . number_format($row['vCO2'], 3) . "</td>\n";
			echo "<td>{$row['Prelevement_idPrelevement']}</td>\n";

			echo "</tr>\n";
		}


	} else {
		echo "<tr><td>aucune donnée pour le moment...</td></tr>\n";
	}

$page=<<<EOF
			</tbody>
		</table>

		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="167188" />
			<button id="exporter" type="submit" name="exporter" value="exporter" title="Exporter les données"></button>Exporter
			<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
		</li>
	</ul>
</form>
EOF;

	echo $page;
	bas_de_page();

} else {
	unset($_SESSION['message']);
	unset($_SESSION['fermentation']);
	header("Location: ../Accueil/accueil.php");
	exit;
}

exit;

?>
