<?php
session_start();
require_once 'config.php';

// Récupérer les ebooks
$stmt = $pdo->query("SELECT id, title, description, price, file_path FROM ebooks ORDER BY created_at DESC");
$ebooks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BenStore - Boutique d'Ebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">BenStore</a>
            <div class="ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-outline-primary">Se déconnecter</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-2">Connexion</a>
                    <a href="register.php" class="btn btn-primary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1 class="text-center my-4">Bienvenue sur BenStore</h1>
        <div class="row g-4">
            <?php foreach ($ebooks as $ebook): ?>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="card ebook-card">
                        <?php if (file_exists('uploads/' . basename($ebook['file_path']))): ?>
                            <canvas class="pdf-thumbnail" id="pdfThumbnail<?php echo $ebook['id']; ?>"></canvas>
                        <?php else: ?>
                            <div class="text-center p-3">Aperçu indisponible</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($ebook['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($ebook['description'], 0, 80)) . '...'; ?></p>
                            <p class="card-text"><strong><?php echo number_format($ebook['price'], 0); ?> FCFA</strong></p>
                            <a href="ebook_details.php?id=<?php echo $ebook['id']; ?>" class="btn btn-primary">Voir les détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.worker.min.js';

        async function renderPDFThumbnail(pdfUrl, canvasId) {
            try {
                const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
                const page = await pdf.getPage(1);
                const viewport = page.getViewport({ scale: 0.15 }); // Très petite

                const canvas = document.getElementById(canvasId);
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const context = canvas.getContext('2d');
                page.render({ canvasContext: context, viewport: viewport });
            } catch (error) {
                console.error('Erreur lors du rendu de la miniature PDF :', error);
            }
        }

        <?php foreach ($ebooks as $ebook): ?>
            <?php if (file_exists('Uploads/' . basename($ebook['file_path']))): ?>
                renderPDFThumbnail('http://localhost/benstore/Uploads/<?php echo htmlspecialchars(basename($ebook['file_path'])); ?>', 'pdfThumbnail<?php echo $ebook['id']; ?>');
            <?php endif; ?>
        <?php endforeach; ?>
    </script>
</body>
</html>