<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ebook'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (empty($title) || empty($price) || empty($_FILES['ebook_file']['name'])) {
        $error = "Le titre, le prix et le fichier PDF sont obligatoires.";
    } elseif ($_FILES['ebook_file']['type'] !== 'application/pdf') {
        $error = "Seuls les fichiers PDF sont autorisés pour l'ebook.";
    } else {
        $upload_dir = '../uploads/'; // Chemin vers benstore/uploads/
        // Créer le dossier s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['ebook_file']['name']);
        $file_path = 'uploads/' . $file_name; // Stocké comme uploads/... dans la base

        if (move_uploaded_file($_FILES['ebook_file']['tmp_name'], $upload_dir . $file_name)) {
            $stmt = $pdo->prepare("INSERT INTO ebooks (title, description, price, file_path) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$title, $description, $price, $file_path])) {
                $success = "Ebook ajouté avec succès.";
            } else {
                $error = "Erreur lors de l'ajout de l'ebook.";
            }
        } else {
            $error = "Erreur lors de l'upload du fichier. Vérifiez que le dossier uploads existe et est accessible.";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("SELECT file_path FROM ebooks WHERE id = ?");
    $stmt->execute([$delete_id]);
    $ebook = $stmt->fetch();

    if ($ebook) {
        if (file_exists($ebook['file_path'])) {
            unlink($ebook['file_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM ebooks WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $success = "Ebook supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression de l'ebook.";
        }
    } else {
        $error = "Ebook non trouvé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BenStore - Gérer les Ebooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">BenStore Admin</a>
            <div class="ms-auto">
                <a href="logout.php" class="btn btn-outline-primary">Se déconnecter</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1 class="text-center my-4">Gérer les Ebooks</h1>
        
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <!-- Formulaire d'ajout d'ebook -->
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="card-title">Ajouter un Ebook</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Prix (FCFA)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="ebook_file" class="form-label">Fichier PDF</label>
                        <input type="file" class="form-control" id="ebook_file" name="ebook_file" accept=".pdf" required>
                    </div>
                    <button type="submit" name="add_ebook" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>

        <!-- Liste des ebooks -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Liste des Ebooks</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Prix (FCFA)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT id, title, price FROM ebooks ORDER BY created_at DESC");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . number_format($row['price'], 0) . " FCFA</td>";
                            echo "<td><a href='manage_ebooks.php?delete_id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Confirmer la suppression ?\")'>Supprimer</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>