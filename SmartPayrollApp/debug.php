<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Afficher les erreurs même si le script échoue
ini_set('error_reporting', E_ALL);

echo "<h1>✅ Débogage activé</h1>";
echo "<p>Les erreurs PHP seront maintenant affichées.</p>";
echo "<p><a href='public/login.php'>Retour au login</a></p>";
?>