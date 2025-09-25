<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Nombre total d'ebooks
$stmt = $pdo->query("SELECT COUNT(*) as total FROM ebooks");
$total_ebooks = $stmt->fetchColumn();

// Revenu total (commandes complétées)
$stmt = $pdo->query("SELECT SUM(e.price) as total_revenue 
                     FROM orders o 
                     JOIN ebooks e ON o.ebook_id = e.id 
                     WHERE o.status = 'completed'");
$total_revenue = $stmt->fetchColumn() ?: 0;

// Nombre de clients
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'client'");
$total_clients = $stmt->fetchColumn();

// Commandes en attente
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetchColumn();

// Date du dernier ebook ajouté
$stmt = $pdo->query("SELECT MAX(created_at) as last_added FROM ebooks");
$last_added = $stmt->fetchColumn() ?: 'Aucun ebook';

// Ebooks les plus populaires (top 3)
$stmt = $pdo->query("SELECT e.title, COUNT(o.id) as order_count 
                     FROM ebooks e 
                     LEFT JOIN orders o ON e.id = o.ebook_id 
                     GROUP BY e.id 
                     ORDER BY order_count DESC 
                     LIMIT 3");
$popular_ebooks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BenStore - Tableau de bord Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">BenStore Admin</a>
            <div class="ms-auto">
                <a href="logout.php" class="btn btn-outline-primary">Se déconnecter</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1 class="text-center my-4">Tableau de bord Admin - BenStore</h1>
        <div class="card mb-4">
            <div class="card-body">
                <p>Bienvenue ! Vous pouvez gérer les ebooks depuis ici.</p>
                <a href="manage_ebooks.php" class="btn btn-primary">Gérer les Ebooks</a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h4>Ebooks Disponibles</h4>
                    <p><?php echo $total_ebooks; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h4>Revenu Total</h4>
                    <p><?php echo number_format($total_revenue, 0); ?> FCFA</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h4>Clients Inscrits</h4>
                    <p><?php echo $total_clients; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h4>Commandes en Attente</h4>
                    <p><?php echo $pending_orders; ?></p>
                </div>
            </div>
        </div>

        <!-- Liste des ebooks -->
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="card-title">Ebooks Disponibles</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Prix (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT id, title, price FROM ebooks ORDER BY created_at DESC LIMIT 5");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . number_format($row['price'], 0) . " FCFA</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ebooks populaires et dernière mise à jour -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="popular-ebooks">
                    <h3 class="card-title p-3">Ebooks les Plus Populaires</h3>
                    <ul>
                        <?php
                        foreach ($popular_ebooks as $ebook) {
                            echo "<li>" . htmlspecialchars($ebook['title']) . " (" . $ebook['order_count'] . " commandes)</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <h4>Dernier Ebook Ajouté</h4>
                    <p><?php echo $last_added; ?></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>