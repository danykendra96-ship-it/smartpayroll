<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tester l'inclusion de Session
require_once __DIR__ . '/app/core/Session.php';

// Tester le namespace
$sessionClass = '\App\Core\Session';
if (class_exists($sessionClass)) {
    echo "✅ Classe Session trouvée : " . $sessionClass;
    echo "<br>Session status : " . session_status();
} else {
    echo "❌ Classe Session NON trouvée";
}

// Tester la BDD
require_once __DIR__ . '/app/core/Database.php';
try {
    $db = \App\Core\Database::getInstance();
    echo "<br>✅ Connexion BDD réussie";
} catch (Exception $e) {
    echo "<br>❌ Erreur BDD : " . $e->getMessage();
}
?>