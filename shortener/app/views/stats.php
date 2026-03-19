<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas - <?php echo htmlspecialchars($code); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>.chart-wrap{background:#111827;border:1px solid #1f2937;border-radius:12px;padding:10px;margin-top:10px;}.back-link{display:inline-block;margin-top:16px;color:#f8fafc;text-decoration:none;border:1px solid #334155;padding:8px 12px;border-radius:8px;background:#2563eb;}.back-link:hover{background:#1d4ed8;}</style>
</head>
<body>
<div class="card">
    <h1>Estadísticas de URL</h1>
    <p class="meta"><span class="pill">Ruta base</span> <?php echo htmlspecialchars($baseUrl); ?></p>
    <p class="meta"><span class="pill">URL corta</span> <a class="link" style="color:#93c5fd;" href="<?php echo htmlspecialchars($baseUrl . 'redirect.php?c=' . $code); ?>" target="_blank"><?php echo htmlspecialchars($baseUrl . 'redirect.php?c=' . $code); ?></a></p>
    <p class="meta"><span class="pill">URL original</span> <a class="link" style="color:#93c5fd;" href="<?php echo htmlspecialchars($url['target_url']); ?>" target="_blank"><?php echo htmlspecialchars($url['target_url']); ?></a></p>
    <p class="meta"><span class="pill">Fecha de creación</span> <?php echo htmlspecialchars($url['created_at']); ?></p>
    <p class="meta"><span class="pill">Total accesos</span> <?php echo $totalAccesses; ?></p>

    <h2>Países</h2>
    <?php if (count($countryRows) === 0): ?><p>No hay accesos todavía.</p><?php else: ?>
    <table><thead><tr><th>País</th><th>Accesos</th></tr></thead><tbody><?php foreach ($countryRows as $row): ?><tr><td><?php echo htmlspecialchars($row['country']); ?></td><td><?php echo (int)$row['hits']; ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>

    <h2>Gráfica por día</h2>
    <div class="chart-wrap"><canvas id="accessChart" width="800" height="300"></canvas></div>
    <script>
        const ctx = document.getElementById('accessChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($days); ?>,
                datasets: [{ label: 'Accesos por día', data: <?php echo json_encode($hits); ?>, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 }]
            },
            options: { scales: { y: { beginAtZero: true, precision:0 } } }
        });
    </script>

    <a class="back-link" href="index.php">← Volver</a>
</div>
</body>
</html>
