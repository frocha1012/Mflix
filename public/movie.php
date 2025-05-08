<?php
require_once __DIR__ . '/../src/db.php';
use MongoDB\BSON\ObjectId;

if (!isset($_GET['id']) || !preg_match('/^[a-f\d]{24}$/i', $_GET['id'])) {
    die("ID inválido.");
}

$movie = $collection->findOne(['_id' => new ObjectId($_GET['id'])]);
if (!$movie) {
    die("Filme não encontrado.");
}
$commentsCollection = $client->sample_mflix->comments;

// Apagar comentário
if (isset($_GET['delete']) && preg_match('/^[a-f\d]{24}$/i', $_GET['delete'])) {
    $commentsCollection->deleteOne(['_id' => new ObjectId($_GET['delete'])]);
    header("Location: movie.php?id=" . $_GET['id']);
    exit;
}

// Inserir novo comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['text']) && trim($_POST['text']) !== '') {
    $commentsCollection->insertOne([
        'movie_id' => $movie->_id,
        'name' => $_POST['name'] ?: 'Anónimo',
        'email' => $_POST['email'] ?? '',
        'text' => $_POST['text'],
        'date' => new MongoDB\BSON\UTCDateTime()
    ]);
    header("Location: movie.php?id=" . $_GET['id']);
    exit;
}
// Atualizar comentário (apenas texto)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'], $_POST['edit_text'])) {
    $commentsCollection->updateOne(
        ['_id' => new ObjectId($_POST['edit_id'])],
        ['$set' => ['text' => $_POST['edit_text']]]
    );
    header("Location: movie.php?id=" . $_GET['id'] . "&commentPage=" . ($_GET['commentPage'] ?? 1));
    exit;
}


// Obter todos os comentários do filme
$commentsPerPage = 3;
$commentPage = isset($_GET['commentPage']) ? max((int) $_GET['commentPage'], 1) : 1;
$commentSkip = ($commentPage - 1) * $commentsPerPage;

$totalComments = $commentsCollection->countDocuments(['movie_id' => $movie->_id]);
$totalCommentPages = ceil($totalComments / $commentsPerPage);

$comments = $commentsCollection->find(
    ['movie_id' => $movie->_id],
    [
        'sort' => ['date' => -1],
        'limit' => $commentsPerPage,
        'skip' => $commentSkip
    ]
);


?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($movie->title ?? 'Filme') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<body class="bg-light">
<div class="container py-5">
    <a href="index.php" class="btn btn-secondary mb-4">← Voltar</a>

    <div class="row">
        <div class="col-md-4">
            <img src="<?= $movie->poster ?? 'https://via.placeholder.com/400x600' ?>" class="img-fluid rounded shadow" alt="Poster">
        </div>
        <div class="col-md-8">
        <h1>
            <?= htmlspecialchars($movie->title ?? 'Sem título') ?> 
            (
            <?php
                echo preg_match('/\d{4}/', (string) $movie->year, $match) ? $match[0] : '?';
            ?>
            )
        </h1>


            <?php if (!empty($movie->genres)): ?>
                <p>
                    <?php foreach ($movie->genres as $genre): ?>
                        <span class="badge bg-primary"><?= htmlspecialchars($genre) ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($movie->plot)): ?>
                <p class="mt-3"><?= htmlspecialchars($movie->plot) ?></p>
            <?php endif; ?>

            <ul class="list-group mt-3">
                <?php if (!empty($movie->directors)): ?>
                    <li class="list-group-item"><strong>Realizador(es):</strong> <?= implode(', ', (array) $movie->directors) ?></li>
                <?php endif; ?>

                <?php if (!empty($movie->writers)): ?>
                    <li class="list-group-item"><strong>Escritor(es):</strong> <?= implode(', ', (array) $movie->writers) ?></li>
                <?php endif; ?>

                <?php if (!empty($movie->cast)): ?>
                    <li class="list-group-item"><strong>Elenco:</strong> <?= implode(', ', (array) $movie->cast) ?></li>
                <?php endif; ?>

                <?php if (!empty($movie->languages)): ?>
                    <li class="list-group-item"><strong>Idiomas:</strong> <?= implode(', ', (array) $movie->languages) ?></li>
                <?php endif; ?>

                <?php if (!empty($movie->countries)): ?>
                    <li class="list-group-item"><strong>País(es):</strong> <?= implode(', ', (array) $movie->countries) ?></li>
                <?php endif; ?>

                <?php if (!empty($movie->released)): ?>
                    <li class="list-group-item"><strong>Data de lançamento:</strong> <?= $movie->released->toDateTime()->format('d/m/Y') ?></li>
                <?php endif; ?>

                <?php if (!empty($movie->imdb)): ?>
                    <li class="list-group-item">
                        <strong>IMDb:</strong>
                        <?= $movie->imdb->rating ?? '?' ?>/10,
                        <?= $movie->imdb->votes ?? '0' ?> votos
                        (ID: <?= $movie->imdb->id ?? 'n/a' ?>)
                    </li>
                <?php endif; ?>

                <?php if (!empty($movie->tomatoes?->viewer)): ?>
                    <li class="list-group-item">
                        <strong>Rotten Tomatoes:</strong>
                        <?= $movie->tomatoes->viewer->rating ?? '?' ?>/5,
                        <?= $movie->tomatoes->viewer->numReviews ?? '?' ?> reviews
                        (<?= $movie->tomatoes->viewer->meter ?? '?' ?>%)
                    </li>
                <?php endif; ?>

                <?php if (!empty($movie->awards)): ?>
                    <li class="list-group-item">
                        <strong>Prémios:</strong>
                        <?= $movie->awards->wins ?? 0 ?> vitórias,
                        <?= $movie->awards->nominations ?? 0 ?> nomeações
                    </li>
                <?php endif; ?>
            </ul>

            <hr class="my-5">

            <h3>Comentários</h3>

            <!-- Lista de comentários -->
            <?php foreach ($comments as $comment): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?= htmlspecialchars($comment->name ?? 'Anónimo') ?>,
                            <?= $comment->date->toDateTime()->format('d/m/Y H:i') ?>
                        </h6>
                        <p class="card-text"><?= nl2br(htmlspecialchars($comment->text)) ?></p>
                        <a href="?id=<?= $movie->_id ?>&delete=<?= $comment->_id ?>&commentPage=<?= $commentPage ?>" class="btn btn-sm btn-outline-danger me-2">Apagar</a>
                            <!-- Botão para abrir modal -->
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $comment->_id ?>">
                                Editar
                            </button>

                            <!-- Modal Bootstrap -->
                            <div class="modal fade" id="editModal<?= $comment->_id ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $comment->_id ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="post" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel<?= $comment->_id ?>">Editar Comentário</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="edit_id" value="<?= $comment->_id ?>">
                                    <div class="mb-3">
                                    <textarea name="edit_text" class="form-control" rows="4" required><?= htmlspecialchars($comment->text) ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                </div>
                                </form>
                            </div>
                            </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- Paginação de Comentários -->
            <?php if ($totalCommentPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $commentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                            href="?id=<?= $movie->_id ?>&commentPage=<?= $commentPage - 1 ?>">
                                ⬅️ Anterior
                            </a>
                        </li>
                        <li class="page-item <?= $commentPage >= $totalCommentPages ? 'disabled' : '' ?>">
                            <a class="page-link"
                            href="?id=<?= $movie->_id ?>&commentPage=<?= $commentPage + 1 ?>">
                                Próxima ➡️
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

            
            <hr class="my-5">
            <h3>Adicionar Comentário</h3>

            <!-- Formulário de novo comentário -->
            <form method="post" class="mb-4">
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="Nome (opcional)">
                </div>
                <div class="mb-2">
                    <input type="email" name="email" class="form-control" placeholder="Email (opcional)">
                </div>
                <div class="mb-2">
                    <textarea name="text" class="form-control" rows="3" required placeholder="Escreve o teu comentário..."></textarea>
                </div>
                <button type="submit" class="btn btn-success">Enviar comentário</button>
            </form>

        </div>
    </div>
</div>
</body>
</html>