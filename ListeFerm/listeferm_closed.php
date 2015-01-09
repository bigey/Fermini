<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// verifie si l'autorisation est ok sinon die avec erreur

if ( !$_SESSION['auth']==1 ) {
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

} elseif ( $_POST['exporter'] ) {

	unset($_SESSION['message']);
	$filtered_keys = preg_grep("/^id_/", array_keys($_POST));

	if ( !empty($filtered_keys) ) {

		foreach ($filtered_keys as $key) {
			$_SESSION['liste'][]=$_POST["$key"];
		}

		header("Location: ../Cloturer/exporter.php");
		exit;

	} else {

	  $_SESSION['message']='ERREUR: vous n\'avez pas sélectionné de fermentation !';
	  header("Location: ".$_SERVER['PHP_SELF']);
	  exit;
	}

} elseif ( $_POST['affiche-data'] ) {

	$_SESSION['fermentation']['idFermentation'] = $_POST['affiche-data'];
	$_SESSION['fermentation']['statut'] = 0;
	header("Location: ../ListeFerm/affiche_data.php");
	exit;

} else {	// Liste les fermentation terminées

	unset($_SESSION['liste']);

	// Ouvre la connexion et la base
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit les requetes
	$query = 	"
				SELECT 
					idFermentation, 
					dateDeclaration,
					numMTF, 
					nomLabo,
					milieu, 
					agitateur,
					ligne, 
					colonne
				FROM 
					Fermentation AS F,
					Utilisateur AS U, 
					Souche AS S, 
					ConditionCulture AS C, 
					Poste AS P
				WHERE
					F.statut='0' AND
					U.idUtilisateur=F.Utilisateur_idUtilisateur AND
					U.idUtilisateur='{$_SESSION['utilisateur']['id']}' AND
					S.idSouche=F.Souche_idSouche AND
					C.idConditionCulture=F.ConditionCulture_idConditionCulture AND
					P.idPoste=F.Poste_idPoste
				ORDER BY dateDeclaration
				";

// 	$query = 	"
// 				SELECT 
// 				  idFermentation, dateDeclaration,
// 				  (UNIX_TIMESTAMP()-UNIX_TIMESTAMP(F.dateDeclaration)) AS temps,
// 				  numMTF, nomLabo,
// 				  milieu, agitateur,
// 				  ligne, colonne
// 				FROM 
// 				  Utilisateur AS U, 
// 				  Souche AS S, 
// 				  ConditionCulture AS C, 
// 				  Poste AS P, 
// 				  Fermentation AS F
// 				WHERE
// 				  F.Utilisateur_idUtilisateur='{$_SESSION['utilisateur']['id']}' AND
// 				  F.statut='0' AND 
// 				  F.Souche_idSouche=S.idSouche AND 
// 				  F.ConditionCulture_idConditionCulture=C.idConditionCulture AND 
// 				  F.Poste_idPoste=P.idPoste
// 				ORDER BY dateDeclaration
// 				";

	// Execute la requete
	$results = mysql_query($query) or die ("Error in query: " . mysql_error());
	$table = mysql_fetch_all($results);

	// Ferme la connexion
	mysql_free_result($results);
	mysql_close($connection);

	haut_de_page("Liste des fermentations terminées");

// Debut du block de formatage
$page=<<<EOF
<h1><a>Liste des fermentations terminées</a></h1>
<form id="form_167188" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Liste des fermentations terminées</h2>
		<p>Merci de sélectionner une fermentation dans la liste ci-dessous</p>
		<p class='error'>{$_SESSION['message']}</p>
	</div>

	<ul >
		<li>
			<table>
				<thead> <!--entête du tableau-->
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>MTF</th>
						<th>Nom</th>
						<th>Milieu</th>
						<th>Agitateur</th>
						<th>Ligne</th>
						<th>Colonne</th>
						<th>Info</th>
						<th>Data</th>
						<th>Export</th>
					</tr>
				</thead>

				<tfoot> <!--pied du tableau-->
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>MTF</th>
						<th>Nom</th>
						<th>Milieu</th>
						<th>Agitateur</th>
						<th>Ligne</th>
						<th>Colonne</th>
						<th>Info</th>
						<th>Data</th>
						<th>Export</th>
					</tr>
				</tfoot>

				<tbody>
EOF;
// Fin du block de formatage

	echo $page;

	if ( isset($table) ) {

		foreach ($table as $row) {

			echo "<tr>\n";

			echo "<td>{$row['idFermentation']}</td>\n";
			echo "<td>{$row['dateDeclaration']}</td>\n";
			echo "<td>{$row['numMTF']}</td>\n";
			echo "<td>{$row['nomLabo']}</td>\n";
			echo "<td>{$row['milieu']}</td>\n";
			echo "<td>{$row['agitateur']}</td>\n";
			echo "<td>{$row['ligne']}</td>\n";
			echo "<td>{$row['colonne']}</td>\n";

			echo "<td><button id='details' name='details' type='submit' value='{$row['idFermentation']}' title='Voir les détails'></button></td>\n";
			echo "<td><button id='affiche-data' name='affiche-data' type='submit' value='{$row['idFermentation']}' title='Voir les données acquises'></button></td>\n";
			echo "<td><input id='checkbox' type='checkbox' name='id_{$row['idFermentation']}' value='{$row['idFermentation']}' title='Exporter la fermentation' /></td>\n";
			echo "</tr>\n";
		}

	} else {

		echo "<tr><td>Aucune fermentation n'est terminée...</td></td>\n";
	}

// Debut du block de formatage
$page=<<<EOF
				</tbody>
			</table>
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
}

exit;

?>
