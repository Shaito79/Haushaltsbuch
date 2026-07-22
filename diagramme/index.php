<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Statistik/Statistik.php';

$statistik = new Statistik();
$einnahmen = $statistik->getGesamteinnahmen();
$ausgaben  = abs($statistik->getGesamtausgaben());
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagramme & Auswertung</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f9; }
        .chart-container { width: 100%; max-width: 450px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #007bff; font-weight: bold; }
    </style>
</head>
<body>

    <h1>Übersicht: Einnahmen vs. Ausgaben</h1>

    <div class="chart-container">
        <canvas id="myChart"></canvas>
    </div>

    <a href="../index.php" class="back-link">← Zurück zur Hauptseite</a>

    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Einnahmen (€)', 'Ausgaben (€)'],
                datasets: [{
                    data: [<?= $einnahmen ?>, <?= $ausgaben ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>

</body>
</html>