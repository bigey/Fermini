<?php

require_once("../fonctions.php");
require_once("../constantes.php");

// start session
session_start();

// Verifie si l'autorisation est ok sinon die avec erreur
if ( !$_SESSION['auth']==1 ) {
	session_destroy();
	header("Location: ../Identification/identification.php");
	exit;

} elseif (	!isset($_SESSION['fermentation']['souche']) ||
			!isset($_SESSION['fermentation']['culture']) ||
			!isset($_SESSION['fermentation']['poste']) ) {

	$_SESSION['message'] = "ERREUR: Déclaration impossible !";
	header("Location: ../Accueil/accueil.php");
	exit;

} else {

  // Ouvre la connexion
  $connection = mysql_connect(SERVEUR, USER, PASS) or die ("Unable to connect database!");

  // Selectionne la base
  mysql_select_db(BASE) or die ("Unable to select database!");

  // Construit les requetes
  foreach ($_SESSION['fermentation']['souche'] as $key => $value) {
	  $souche[$key] = mysql_real_escape_string($value);
  }

  foreach ($_SESSION['fermentation']['culture'] as $key => $value) {
	  $culture[$key] = mysql_real_escape_string($value);
  // 	echo "Culture: $key => $value\n";
  }

  foreach ($_SESSION['fermentation']['poste'] as $key => $value) {
	  $poste[$key] = mysql_real_escape_string($value);
	  // echo "Poste[$key] = ".$poste[$key]."\n";
  }


  // Execute les requetes en écriture
  if ( !isset($souche['insert']) ) {

	  $query_souche = 	"
				  INSERT INTO `Souche` SET
				  numMTF='{$souche['mtf']}',
				  nomScientifique='{$souche['espece']}',
				  nomLabo='{$souche['nom']}',
				  description='{$souche['description']}'
				  ";

	  mysql_query($query_souche) or die ("Error in query: " . mysql_error());
	  $_SESSION['fermentation']['souche']['insert'] = 1;
	  $_SESSION['fermentation']['souche']['idSouche'] = mysql_insert_id();
  }

  if ( !isset($culture['insert']) ) {

	  $query_culture =	"
				  INSERT INTO `ConditionCulture` SET
				  milieu='{$culture['milieu']}',
				  volume='{$culture['volume']}',
				  temperature='{$culture['temperature']}',
				  typeFermenteur='{$culture['type']}',
				  oxygene='{$culture['oxygene']}'
				  ";

	  mysql_query($query_culture) or die ("Error in query: " . mysql_error());
	  $_SESSION['fermentation']['culture']['insert'] = 1;
	  $_SESSION['fermentation']['culture']['idConditionCulture'] = mysql_insert_id();
  }

  if ( !isset($poste['insert']) ) {

	  $query_poste =		"
				  INSERT INTO `Poste` SET
				  agitateur='{$poste['agitateur']}',
				  vitesse='{$poste['vitesse']}',
				  ligne='{$poste['ligne']}',
				  colonne='{$poste['colonne']}',
				  balance='{$poste['balance']}'
				  ";

	  mysql_query($query_poste) or die ("Error in query: " . mysql_error());
	  $_SESSION['fermentation']['poste']['insert'] = 1;
	  $_SESSION['fermentation']['poste']['idPoste'] = mysql_insert_id();
  }


  // Préparation de la requête en insertion dans la table Fermentation

  if ( empty($_SESSION['fermentation']['insert']) ) {

	  $query_fermentation = 	"
					  INSERT INTO `Fermentation` SET
					  Utilisateur_idUtilisateur='{$_SESSION['utilisateur']['id']}',
					  Souche_idSouche='{$_SESSION['fermentation']['souche']['idSouche']}',
					  ConditionCulture_idConditionCulture='{$_SESSION['fermentation']['culture']['idConditionCulture']}',
					  Poste_idPoste='{$_SESSION['fermentation']['poste']['idPoste']}',
					  commentaire='NULL',
					  statut='1'
				  ";

	  mysql_query($query_fermentation) or die ("Error in query: $query_fermentation\n" . mysql_error());

	  $_SESSION['fermentation']['insert'] = 1;
	  $_SESSION['fermentation']['idFermentation'] = mysql_insert_id();
  }


  // Ferme la connexion
  mysql_close($connection);

  $_SESSION['message'] = "INFO: Déclaration réalisée avec succès !";
  header("Location: ../ListeFerm/listeferm.php");

}

?>
