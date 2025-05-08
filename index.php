<?php
require_once __DIR__ . '/src/db.php';

$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$limit = 8;
$skip = ($page - 1) * $limit;

$options = [
    'limit' => $limit,
    'skip' => $skip,
    'sort' => ['year' => -1],
    'projection' => ['title' => 1, 'year' => 1, 'poster' => 1, 'genres' => 1]
];

$movies = $collection->find([], $options);
$total = $collection->countDocuments();
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MFlix - Filmes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🎬 MFlix</a>
        </div>
    </nav>

    <div class="container py-4">
        <h1 class="mb-4">Filmes</h1>

    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php foreach ($movies as $movie): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?= $movie->poster ?? 'https://via.placeholder.com/220x330' ?>" class="card-img-top" style="height: 330px; object-fit: cover;" alt="Poster">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($movie->title) ?></h5>
                        <p class="card-text">
                            <?= preg_match('/\d{4}/', (string) $movie->year, $match) ? $match[0] : 'Ano desconhecido' ?>
                        </p>


                        <?php if (!empty($movie->genres)): ?>
                            <p>
                                <?php foreach ($movie->genres as $genre): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($genre) ?></span>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="movie.php?id=<?= $movie->_id ?>" class="btn btn-primary btn-sm">Ver detalhes</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginação Inteligente -->
    <nav class="mt-4">
        <ul class="pagination justify-content-center">

            <!-- Primeira página -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=1">⏮️</a>
            </li>

            <!-- Anterior -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>">«</a>
            </li>

            <?php
            $start = max(1, $page - 3);
            $end = min($totalPages, $page + 3);
            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Seguinte -->
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>">»</a>
            </li>

        </ul>
    </nav>
</div>
</body>
</html>
