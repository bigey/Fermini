<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// Verifie si l'autorisation est ok sinon on redirige sur la page d'authentification
if ( !$_SESSION['auth']==1 ) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;


// Un bouton est appuyé et on traite l'évènememnt
} elseif ( $_POST['annuler'] or $_POST['retour'] ) {
	unset($_SESSION['message']);
 	unset($_SESSION['fermentation']);
	unset($_SESSION['acquisition']);
	header("Location: ../ListeFerm/listeferm.php");
	exit;


} elseif ( isset($_POST['mesurer']) and isset($_SESSION['fermentation']['idFermentation']) ) {

	$id = $_SESSION['fermentation']['idFermentation'];
	$poids = balance();

	if (is_numeric($poids)) {

	haut_de_page("Prélèvement");

// Debut du block de formatage
$page=<<<EOF
		<h1><a>Résultat de la mesure</a></h1>
		<form id="form_167188" class="appnitro" method="post" action="">
			<div class="form_description">
				<h2>Résultat de la pesée après prélèvement</h2>
				<p>Merci de bien vouloir valider la mesure réalisée pour la fermention n° $id</p>
			</div>
				<ul >
					<li>Poids: <strong>$poids g</strong></li>

					<li>Valider la mesure en appuyant sur <strong>"Sauvegarder"</strong></li>

					<li class="buttons">
						<input type="hidden" name="form_id" value="167188" />
						<button id="sauvegarder" type="submit" name="sauvegarder" value="1" title="Sauvegarder"></button>Sauvegarder
						<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
					</li>
				</ul>
		</form>
EOF;
// Fin du block de formatage

		echo $page;
		bas_de_page();

		$_SESSION['acquisition']['poids'] = $poids;

	} else {

		// Valeur de retour n'est pas numérique donc il y a une erreur
		$_SESSION['message'] = "ERREUR: la balance n'a pas répondu !";
		header("Location: ".$_SERVER['PHP_SELF']);
	}


} elseif ( isset($_POST['sauvegarder']) and isset($_SESSION['acquisition']['poids']) and isset($_SESSION['fermentation']['idFermentation']) ) {

	$id = $_SESSION['fermentation']['idFermentation'];
	$poids = $_SESSION['acquisition']['poids'];

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit la requete pour rechercher les info sur l'acquisition précédante
	$query = 	"
				SELECT 
					idAcquisition,
					dateAcquisition,
					Prelevement_idPrelevement,
					(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(dateAcquisition)) AS delta,
					temps, 
					poids, 
					mCO2, 
					vCO2, 
					volumeRestant
				FROM Acquisition
				WHERE Fermentation_idFermentation='$id'
				ORDER BY dateAcquisition DESC
				";


	// Execute la requete
	$results = mysql_query($query) or die ("Error in query:  $query\n" . mysql_error());
	$table = mysql_fetch_assoc($results); // seul le dernier enregistrement est nécessaire
	mysql_free_result($results);

	if ( !empty($table) ) { // Il existe une acquisition précédante: calcul du volume restant

		$query = "INSERT INTO Prelevement SET poids='$poids'";

		mysql_query($query) or die ("Error in query: $query\n" . mysql_error());

		$idPrelevement = mysql_insert_id();

		$delta_temps = $table['delta']/3600;
		$delta_poids = $table['poids'] - $poids; // variation de la masse du fermenteur en g
		$delta_volume = $delta_poids/densite($table['mCO2'],NULL)/1000; // variation de volume en Litre
		$volume_n = $table['volumeRestant'] - $delta_volume; // calcul du volume restant dans le fermenteur (en L)
		$temps = $table['temps']+$delta_temps;
		$mCO2_n = $table['mCO2']; // le cumul de CO2 ne change pas (g/L)
		$vCO2_n = $table['vCO2']; // la vitesse ne change pas (g/L/h)

		// Construit la requete
		$query =	"
					INSERT INTO Acquisition (
						Fermentation_idFermentation,
						Prelevement_idPrelevement,
						temps, 
						poids, 
						volumeRestant, 
						mCO2, 
						vCO2, 
						temperature
					)
					VALUES (
						'$id',
						'$idPrelevement', 
						'$temps', 
						'$poids', 
						'$volume_n', 
						'$mCO2_n', 
						'$vCO2_n', 
						NULL
					)
					";

		// Execute la requete et ferme la connection
		mysql_query($query) or die ("Error in query: $query\n" . mysql_error());
		mysql_close($connection);

		// On redirige sur affiche_data pour afficher les résultats
		unset($_SESSION['message']);
		unset($_SESSION['acquisition']);
		unset($_SESSION['fermentation']);
		header("Location: ../ListeFerm/listeferm.php");
		exit;


	} else {

		// C'est la première acquisition pour cette fermentation: Ce n'est pas possible de faire un prélèvement
		// Proposer de faire une acquisition simple avant
		//echo "OK création de la première acquisition pour cette fermentation !\n";

		unset($_SESSION['acquisition']);
		$_SESSION['message'] = "ERREUR: vous devez faire au moins une acquisition avant le prélèvement !";
		header("Location: acquisition.php");
		exit;
	}


} elseif ( isset($_SESSION['fermentation']['idFermentation']) ) {  // On arrive sur la page

	$id			= $_SESSION['fermentation']['idFermentation'];
	$agitateur	= $_SESSION['fermentation']['poste']['agitateur'];
	$ligne		= $_SESSION['fermentation']['poste']['ligne'];
	$colonne	= $_SESSION['fermentation']['poste']['colonne'];
	$mCO2		= $_SESSION['acquisition']['mCO2'];

	haut_de_page("Prélèvement");

// Debut du block de formatage
$page=<<<EOF
<h1><a>Prélèvement</a></h1>
<form id="form_167188" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Faire un prélèvement</h2>
		<p>Voulez-vous faire un prélèvement pour la fermentation n° $id ?</p>
		<p class=error>{$_SESSION['message']}</p>
		
	</div>
		<ul>
			<li>Fermentation: <strong>$id</strong></li>
			<li>Agitateur: <strong>$agitateur</strong></li>
			<li>Ligne: <strong>$ligne</strong></li>
			<li>Colonne: <strong>$colonne</strong></li>
			<br />
			<li>Cumul CO2:&nbsp;&nbsp;<span class="important">$mCO2 g</span></li>
			<br />
			<li><label class="description">Voulez-vous faire un prélèvement ?</label></li>
			<br />

			<li>
				Si oui,
				<ul>
					<li>faite le prélèvement,</li>
					<li>reposer le fermenteur sur la balance,</li>
					<li>appuyer alors sur <strong>"Mesurer"</strong></li>
				</ul>
			</li>
			<br />
			<li>Si non, faite <strong>"Retour"</strong></li>
			<br />

			<li class="buttons">
				<input type="hidden" name="form_id" value="167188" />
				<button id="mesurer" type="submit" name="mesurer" value="1" title="Mesurer"></button>Mesurer
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
	unset($_SESSION['acquisitioin']);
	unset($_SESSION['fermentation']);
	header("Location: ../ListeFerm/listeferm.php");
	exit;
}

exit;

?>
