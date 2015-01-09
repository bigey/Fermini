<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// verifie si l'autorisation est ok sinon die avec erreur

if (!$_SESSION['auth']==1) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

} elseif ( $_POST['retour'] ) { // $_POST est initialisé par un bouton
	unset($_SESSION['fermentation']);
	header("Location: ../Accueil/accueil.php");
	exit;

} elseif ( $_POST['details'] ) {
	unset($_SESSION['message']);
	$_SESSION['fermentation']['idFermentation'] = $_POST['details'];
	header("Location: ../ListeFerm/detail.php");
	exit;

} elseif ( $_POST['acquisition'] ) {
	$_SESSION['fermentation']['idFermentation'] = $_POST['acquisition'];
	header("Location: ../Acquisition/acquisition.php");
	exit;

} elseif ( $_POST['affiche-data'] ) {
	$_SESSION['fermentation']['idFermentation'] = $_POST['affiche-data'];
	$_SESSION['fermentation']['statut'] = 1;
	header("Location: ../ListeFerm/affiche_data.php");
	exit;

} else {  // pas de bouton appuyé, on liste les fermentations en cours

	unset($_SESSION['fermentation']);
	$_SESSION['message'] = "Utilisateur: " . $_SESSION['utilisateur']['prenom'] . " " . $_SESSION['utilisateur']['nom'];

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");

	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit les requetes
	$query =	"
				SELECT 
					idFermentation, 
					numMTF, 
					nomLabo, 
					agitateur, 
					ligne, 
					colonne
				FROM 
					Fermentation AS F, 
					Utilisateur AS U, 
					Souche AS S, 
					Poste AS P
				WHERE
					F.statut='1' AND
					U.idUtilisateur=F.Utilisateur_idUtilisateur AND
					U.idUtilisateur='{$_SESSION['utilisateur']['id']}' AND
					S.idSouche=F.Souche_idSouche AND
					P.idPoste=F.Poste_idPoste
				ORDER BY F.idFermentation
				";

// 	$query =	"
// 				SELECT idFermentation, numMTF, nomLabo, agitateur, ligne, colonne
// 				FROM Utilisateur AS U, Fermentation AS F, Souche AS S , Poste AS P
// 				WHERE
// 					U.idUtilisateur='{$_SESSION['utilisateur']['id']}' AND
// 					F.statut='1' AND
// 					S.idSouche=F.Souche_idSouche AND 
// 					P.idPoste=F.Poste_idPoste
// 				ORDER BY idFermentation
// 				";


	// Execute la requete
	$results = mysql_query($query) or die ("Error in query: " . mysql_error());
	$table = mysql_fetch_all($results);
	mysql_free_result($results);


	// Haut de la page XHTML
	haut_de_page("Liste des fermentations en cours");

$page = <<<EOF
<h1><a>Liste des fermentations en cours</a></h1>
<form id="form_167188" class="appnitro" method="post" action="">

	<div class="form_description">
		<h2>Liste des fermentations en cours</h2>
		<p>Merci de sélectionner une fermentation dans la liste ci-dessous</p>
		<p class="error">{$_SESSION['message']}</p>
	</div>

	<ul >
		<li>

		<table>
			<thead> <!--entête du tableau-->
				<tr>
					<th>ID</th>
					<th>Temps (h)</th>
					<th>CO2 (g/L)</th>
					<th>VCO2 (g/L/h)</th>
					<th>MTF</th>
					<th>Nom</th>
					<th>Agitateur</th>
					<th>Ligne</th>
					<th>Colonne</th>
					<th>Info</th>
					<th>Acquis.</th>
					<th>Data</th>
				</tr>
			</thead>

			<tfoot> <!--pied du tableau-->
				<tr>
					<th>ID</th>
					<th>Temps (h)</th>
					<th>CO2 (g/L)</th>
					<th>VCO2 (g/L/h)</th>
					<th>MTF</th>
					<th>Nom</th>
					<th>Agitateur</th>
					<th>Ligne</th>
					<th>Colonne</th>
					<th>Info</th>
					<th>Acquis.</th>
					<th>Data</th>
				</tr>
			</tfoot>

			<tbody>
EOF;

	echo $page;

	if ( isset($table) ) {

		foreach ($table as $row) {


			$query =	"
						SELECT 
						  temps, 
						  mCO2, 
						  vCO2
						FROM 
						  Acquisition
						WHERE 
						  Fermentation_idFermentation='{$row['idFermentation']}'
						ORDER BY temps DESC 
						LIMIT 1
						";

			// Execute la requete
			$results = mysql_query($query) or die ("Error in query: " . mysql_error());
			$acquis = mysql_fetch_assoc($results);
			mysql_free_result($results);

			$temps	= '';
			$mCO2	= '';
			$vCO2	= '';

			if ( !empty($acquis) ) {
				$temps=number_format($acquis['temps'], 2);
				$mCO2=number_format($acquis['mCO2'], 2);
				$vCO2=number_format($acquis['vCO2'], 3);
			}



			echo "<tr>\n";
			echo "<td>{$row['idFermentation']}</td>\n";
			echo "<td>$temps</td>\n";
			echo "<td>$mCO2</td>\n";
			echo "<td>$vCO2</td>\n";
			echo "<td>{$row['numMTF']}</td>\n";
			echo "<td>{$row['nomLabo']}</td>\n";
			echo "<td>{$row['agitateur']}</td>\n";
			echo "<td>{$row['ligne']}</td>\n";
			echo "<td>{$row['colonne']}</td>\n";
			echo "<td><button id='details' name='details' type='submit' title='Voir les détails' value='{$row['idFermentation']}'></button></td>\n";
			echo "<td><button id='acquisition' name='acquisition' type='submit' title='Faire une acquisition' value='{$row['idFermentation']}'></button></td>\n";
			echo "<td><button id='affiche-data' name='affiche-data' type='submit' title='Données acquises' value='{$row['idFermentation']}'></button></td>\n";
			echo "</tr>\n";
		}

	} else {

		echo "<tr><td>Aucune fermentation en cours...</td></td>\n";
	}


$page = <<<EOF
			</tbody>
		</table>
		</li>

		<li class="buttons">
			<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
		</li>
	</ul>
</form>
EOF;

	echo $page;

	// Bas de la page XHTML
	bas_de_page();

	// Ferme la connexion
	mysql_close($connection);
}

exit;

?>
