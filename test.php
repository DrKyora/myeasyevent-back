<?php
// Forcer le chargement pour test
function loadEnv($path) {
    // ... votre fonction loadEnv
}

// Test forcé
loadEnv(__DIR__ . '/.env');

echo "=== APRÈS CHARGEMENT FORCÉ ===\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'TOUJOURS NON DÉFINI') . "\n";
?>