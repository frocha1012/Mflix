<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$mongoUri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI') ?? null;

if (!$mongoUri) {
    throw new Exception("❌ MONGODB_URI não definida no ambiente");
}

$client = new Client($mongoUri);
$collection = $client->sample_mflix->movies;

