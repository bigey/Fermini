<?php

require_once("../fonctions.php");
require_once("../constantes.php");

session_start();

// verifie si l'autorisation est ok sinon on redirige sur la page d'authentification
if ( !$_SESSION['auth']==1 ) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

// Si un bouton est appuyé et on traite l'évènememnt

} elseif ( $_POST['retour'] ) {
	unset($_SESSION['message']);
	unset($_SESSION['acquisition']);

	if ( $_SESSION['fermentation']['statut']==1 ) {
		unset($_SESSION['fermentation']);
		header("Location: listeferm.php");
		exit;

	} else {
		unset($_SESSION['fermentation']);
		header("Location: listeferm_closed.php");
		exit;
	}

} elseif ( isset($_POST['cloturer']) and isset($_SESSION['fermentation']['idFermentation']) ) { // On souhaite metre fin à la fermentation en cours
		unset($_SESSION['message']);
		header("Location: ../Cloturer/cloturer.php");
		exit;

} elseif ( isset($_POST['exporter']) and isset($_SESSION['fermentation']['idFermentation']) ) { // Exporter
		unset($_SESSION['message']);
		$_SESSION['liste'][]=$_SESSION['fermentation']['idFermentation'];
		header("Location: ../Cloturer/exporter.php");
		exit;

// Point d'arrivée de la page
} elseif ( isset($_SESSION['fermentation']['idFermentation']) ) {

	$id = $_SESSION['fermentation']['idFermentation'];

	// Ouvre la connexion
	$connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");

	// Selectionne la base
	mysql_select_db(BASE) or die ("Unable to select database!");

	// Construit les requetes
	$query = 	"
				SELECT 
				  U.idUtilisateur,
				  U.nom,
				  U.prenom,
				  F.idFermentation, 
				  F.dateDeclaration, 
				  F.commentaire, 
				  F.statut,
				  S.idSouche, 
				  S.numMTF, 
				  S.nomScientifique, 
				  S.nomLabo, 
				  S.description,
				  C.idConditionCulture, 
				  C.milieu, 
				  C.temperature, 
				  C.volume, 
				  C.typeFermenteur, 
				  C.oxygene,
				  P.idPoste, 
				  P.agitateur, 
				  P.vitesse, 
				  P.ligne, 
				  P.colonne, 
				  P.balance
				FROM 
				  Utilisateur AS U,
				  Souche AS S, 
				  ConditionCulture AS C, 
				  Poste AS P, 
				  Fermentation AS F
				WHERE 
				  F.idFermentation=$id AND
				  U.idUtilisateur=F.Utilisateur_idUtilisateur AND 
				  S.idSouche=F.Souche_idSouche AND 
				  C.idConditionCulture=F.ConditionCulture_idConditionCulture AND 
				  P.idPoste=F.Poste_idPoste
				";

// 				WHERE 
// 				  F.idFermentation=$id AND 
// 				  F.Souche_idSouche=S.idSouche AND 
// 				  F.ConditionCulture_idConditionCulture=C.idConditionCulture AND 
// 				  F.Poste_idPoste=P.idPoste
// 				";




	// Execute la requete
	$results = mysql_query($query) or die ("Error in query: " . mysql_error());
	$table = mysql_fetch_assoc($results);

	$statut = ($table['statut']==1) ? 'en cours...' : 'terminé';

	// Ferme la connexion
	mysql_free_result($results);
	mysql_close($connection);


	$_SESSION['fermentation']['statut'] = $table['statut'];

	haut_de_page("Détail d\'une fermentation");

$page = <<<EOF
<h1><a>Détail de la fermentation numéro $id</a></h1>
<form id="form_167188" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Fermentation numéro $id</h2>
		<p>Description détaillée</p>
		<p class="error">{$_SESSION['message']}</p>
	</div>
	<ul>
		<li>
			<label class="description" for="element_1">Uitlisateur:</label>
				<p>Identifiant: {$table['idUtilisateur']}</p>
				<p>Prénom: {$table['prenom']}</p>
				<p>Nom: {$table['nom']}</p>
		</li>

		<li>
			<label class="description" for="element_1">Souche:</label>
				<p>Numéro d'identification: {$table['idSouche']}</p>
				<p>MTF: {$table['numMTF']}</p>
				<p>Espèce: {$table['nomScientifique']}</p>
				<p>Nom: {$table['nomLabo']}</p>
				<p>Description: {$table['description']}</p>
		</li>

		<li>
			<label class="description" for="element_1">Milieu de culture:</label>
				<p>Numéro d'identification: {$table['idConditionCulture']}</p>
				<p>Milieu: {$table['milieu']}</p>
				<p>Volume de milieu: {$table['volume']} L</p>
				<p>Type de fermenteur: {$table['typeFermenteur']}</p>
				<p>Température: {$table['temperature']}°C</p>
				<p>Oxygène: {$table['oxygene']}</p>
		</li>

		<li>
			<label class="description" for="element_1">Poste de fermentation:</label>
				<p>Numéro d'identification: {$table['idPoste']}</p>
				<p>Nom de l'agitateur: {$table['agitateur']}</p>
				<p>Vitesse d'agitation: {$table['vitesse']} tours/min</p>
				<p>Ligne: {$table['ligne']}</p>
				<p>Colonne: {$table['colonne']}</p>
				<p>Balance utilisée: {$table['balance']}</p>
		</li>

		<li>
			<label class="description" for="element_1">Détails de fermentation:</label>
				<p>Numéro d'identification: {$table['idFermentation']}</p>
				<p>Date de déclaration: {$table['dateDeclaration']}</p>
				<p>Commentaire: {$table['commentaire']}</p>
				<p>Statut: {$statut}</p>
		</li>

		<li>
			<input type="hidden" name="form_id" value="167188" />
EOF;

	echo $page;


	if ( $_SESSION['fermentation']['statut']==1 ) {

$input=<<<EOF
			<button id="cloturer" type="submit" name="cloturer" value="cloturer" title="Cloturer la fermentation"></button>Cloturer
			<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
		</li>
	</ul>
</form>
EOF;

		echo $input;

	} else {

$input=<<<EOF
			<button id="exporter" type="submit" name="exporter" value="exporter" title="Exporter les données"></button>Exporter
			<button id="retour" type="submit" name="retour" value="retour" title="Retour"></button>Retour
		</li>
	</ul>
</form>
EOF;

		echo $input;
	}


	bas_de_page();

} else {

	unset($_SESSION['message']);
	unset($_SESSION['fermentation']);
	unset($_SESSION['acquisition']);
	header("Location: ../Accueil/accueil.php");
	exit;
}

exit;

?>
