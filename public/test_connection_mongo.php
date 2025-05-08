<?php
require_once __DIR__ . '/../src/db.php'; // Inclui a conexão MongoDB com dotenv

try {
    // Testa listagem de bases de dados
    $databases = $client->listDatabases();

    echo "✅ Conexão estabelecida com sucesso!<br><br>";
    echo "Bases de dados disponíveis:<br>";

    foreach ($databases as $db) {
        echo "- " . $db->getName() . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
