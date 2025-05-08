<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;

// Carregar variáveis do ficheiro .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Obter a URI do MongoDB do .env
$mongoUri = $_ENV['MONGODB_URI'] ?? null;

if (!$mongoUri) {
    throw new Exception("❌ MONGODB_URI não definida no ficheiro .env");
}

// Criar cliente MongoDB
$client = new Client($mongoUri);

// (Opcional) acesso direto à coleção de filmes da base sample_mflix
$collection = $client->sample_mflix->movies;
