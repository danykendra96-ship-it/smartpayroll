<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Vérification de l'Installation SmartPayroll</h1>";
echo "<style>body { font-family: Arial; } .ok { color: green; } .error { color: red; font-weight: bold; } .warning { color: orange; }</style>";

$checks = [];

// Vérifier PHP version
$phpVersion = phpversion();
$checks[] = [
    'name' => 'Version PHP',
    'status' => version_compare($phpVersion, '7.4', '>=') ? 'ok' : 'error',
    'message' => "PHP $phpVersion" . (version_compare($phpVersion, '7.4', '>=') ? ' ✅' : ' ❌ (minimum 7.4 requis)')
];

// Vérifier les dossiers
$requiredDirs = [
    'app' => 'Dossier application',
    'app/config' => 'Configuration',
    'app/core' => 'Noyau MVC',
    'app/controllers' => 'Contrôleurs',
    'app/models' => 'Modèles',
    'app/views' => 'Vues',
    'app/views/home' => 'Landing page',
    'public' => 'Ressources publiques',
    'logs' => 'Logs'
];

foreach ($requiredDirs as $dir => $desc) {
    $path = __DIR__ . DIRECTORY_SEPARATOR . $dir;
    $exists = is_dir($path);
    $checks[] = [
        'name' => $desc,
        'status' => $exists ? 'ok' : 'error',
        'message' => $exists ? "✅ $path" : "❌ $path n'existe pas"
    ];
}

// Vérifier les fichiers essentiels
$requiredFiles = [
    'app/core/Session.php' => 'Classe Session',
    'app/core/Database.php' => 'Classe Database',
    'app/models/Employe.php' => 'Modèle Employe',
    'app/controllers/AuthController.php' => 'Contrôleur Auth',
    'app/views/home/index.php' => 'Landing page',
    'public/login.php' => 'Page de login'
];

foreach ($requiredFiles as $file => $desc) {
    $path = __DIR__ . DIRECTORY_SEPARATOR . $file;
    $exists = file_exists($path);
    $checks[] = [
        'name' => $desc,
        'status' => $exists ? 'ok' : 'error',
        'message' => $exists ? "✅ $path" : "❌ $path introuvable"
    ];
}

// Afficher les résultats
foreach ($checks as $check) {
    $class = $check['status'];
    echo "<div class='$class'>{$check['name']}: {$check['message']}</div>";
}

// Résumé
$okCount = count(array_filter($checks, fn($c) => $c['status'] === 'ok'));
$totalCount = count($checks);
$percent = round(($okCount / $totalCount) * 100);

echo "<hr>";
echo "<h2>📊 Résumé : $okCount/$totalCount ($percent%)</h2>";

if ($okCount === $totalCount) {
    echo "<p class='ok'><strong>✅ Installation complète !</strong> Vous pouvez utiliser l'application.</p>";
    echo "<p><a href='index.php'>Accéder à l'application</a></p>";
} else {
    echo "<p class='error'><strong>❌ Installation incomplète</strong> - Corrigez les erreurs ci-dessus.</p>";
}
?>