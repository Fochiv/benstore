<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$ebook_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM ebooks WHERE id = ?");
$stmt->execute([$ebook_id]);
$ebook = $stmt->fetch();

if (!$ebook) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, ebook_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $ebook_id]);
    $success = "Achat enregistré ! En attente de confirmation.";
}

if (isset($_POST['buy']) && !isset($_SESSION['user_id'])) {
    $error = "Veuillez vous connecter pour acheter.";
}

// Vérifier si le fichier PDF existe
$pdf_exists = file_exists('uploads/' . basename($ebook['file_path']));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BenStore - <?php echo htmlspecialchars($ebook['title']); ?></title>
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
        <div class="ebook-details mt-5">
            <div class="row">
                <div class="col-md-4">
                    <?php if ($pdf_exists): ?>
                        <canvas id="pdfThumbnail"></canvas>
                    <?php else: ?>
                        <div class="text-center p-3">Aperçu indisponible</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <h2 class="card-title"><?php echo htmlspecialchars($ebook['title']); ?></h2>
                    <p><?php echo htmlspecialchars($ebook['description']); ?></p>
                    <p><strong>Prix : <?php echo number_format($ebook['price'], 0); ?> FCFA</strong></p>
                    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="buy" class="btn btn-primary">Acheter maintenant</button>
                    </form>
                    <?php if ($pdf_exists): ?>
                        <button class="btn btn-preview" onclick="showPDFPreview('http://localhost/benstore/Uploads/<?php echo htmlspecialchars(basename($ebook['file_path'])); ?>')">Aperçu du PDF</button>
                    <?php else: ?>
                        <div class="alert alert-danger">Le fichier PDF est introuvable.</div>
                    <?php endif; ?>
                    <div class="pdf-preview" id="pdfPreview" style="display: none;">
                        <canvas id="pdfCanvas"></canvas>
                    </div>
                </div>
            </div>
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

        async function showPDFPreview(pdfUrl) {
            const pdfPreview = document.getElementById('pdfPreview');
            const pdfCanvas = document.getElementById('pdfCanvas');
            pdfPreview.style.display = 'block';

            try {
                const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
                const page = await pdf.getPage(1);
                const viewport = page.getViewport({ scale: 1.0 }); // Pleine taille

                pdfCanvas.height = viewport.height;
                pdfCanvas.width = viewport.width;

                const context = pdfCanvas.getContext('2d');
                page.render({ canvasContext: context, viewport: viewport });
            } catch (error) {
                pdfPreview.style.display = 'none';
                alert('Erreur lors du chargement de l\'aperçu PDF : ' + error.message);
            }
        }

        <?php if ($pdf_exists): ?>
            renderPDFThumbnail('http://localhost/benstore/Uploads/<?php echo htmlspecialchars(basename($ebook['file_path'])); ?>', 'pdfThumbnail');
        <?php endif; ?>
    </script>
</body>
</html>