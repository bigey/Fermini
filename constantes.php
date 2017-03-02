<?php

/* Définition des constantes utilisables dans l'ensemble des scripts */

// definition du PATH pour l'ensemble du site
define("SITE_PATH", '/var/www/Fermini');

// definitions des constantes de connection à la base
define("USER","www-data");
define("PASS", "password");
define("SERVEUR", "localhost");
define("BASE", "Fermini");

// limite session
define("VALIDITE", 2700); // durée de validité de la session avec la fonction date("U"). date("U")+VALIDITE
define("PERIMEE", 604800); // durée pendant laquelle la session sera conservée dans le table "Session"

?>
