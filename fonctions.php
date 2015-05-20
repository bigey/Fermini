<?php

/* Le fichier "fonctions.php" contient les fonctions que vous utiliserez fréquemment */

require_once("constantes.php");

// Code de haut de page XHTML
function haut_de_page($texte = "") {

$haut=<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>$texte</title>
<link rel="stylesheet" type="text/css" href="../view.css" media="all">
<script type="text/javascript" src="view.js"></script>
</head>
<body id="main_body" >
<img id="top" src="top.png" alt="">
<div id="form_container">
EOF;

echo $haut;
}


// Code de bas de page XHTML
function bas_de_page($text = "") {

$bas=<<<BAS
<div id="footer">UMR 1083 Sciences Pour l'Oenologie - INRA</div>
</div>
<img id="bottom" src="bottom.png" alt="">
</body>
</html>
BAS;

echo $bas;
}


// permet d'inserer un paragraphe d'erreur dans la sortie html
function message($texte = '') {
	if(isset($texte)) {
		echo "<p class=error>".$texte."</p>\n";
	}
}


// fonction qui lit un resultat de requete mysql et retourne un array à 2 dimentions
function mysql_fetch_all($result) {
   while($row=mysql_fetch_assoc($result)) {
       $return[] = $row;
   }

   return $return;
}


// fonction qui envoie des ordre à la balance Ohaus
function balance($arg = "SP") {

	$output = array();
 	$return_value = 0;
	$commande = "/var/www/Fermini/Scripts/balance_ohaus.pl";
	$string = exec("$commande $arg", $output, $return_value);

	return( ($return_value==0) ? $string : NULL );
}


// fonction qui exporte les données de fermentation dans un document oocalc
function export_data($filename,$arg) {

	$commande="/var/www/Fermini/Scripts/export_2_oocalc.pl $filename $arg";
	$commande=escapeshellcmd($commande);

	system("cd /tmp && $commande", $return_value);
	return($return_value);
}

// Generate a random character string
function rand_str($length = 5, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
	// Length of character list
	$chars_length = (strlen($chars) - 1);

	// Start our string
	$string = $chars{rand(0, $chars_length)};

	// Generate random string
	for ($i = 1; $i < $length; $i = strlen($string)) {

		// Grab a random character from our list
		$r = $chars{rand(0, $chars_length)};

		// Make sure the same two characters don't appear next to each other
		if ($r != $string{$i - 1}) $string .=  $r;
	}

	// Return the string
	return $string;
}


// fonction qui calcule la densite du milieu
function densite($cO2, $glucose) {

	return(1);
}


?>
