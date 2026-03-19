<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acortador de URLs
  </title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
  <div class="header"><div><h1>Acortador de URLs</h1><small></small></div><div class="label">Ruta base: <strong><?php echo htmlspecialchars(BASE_URL); ?></strong></div></div>
  <div class="card">
    <h2>Crear URL corta</h2>
    <form id="createForm">
      <input id="inputUrl" type="text" placeholder="https://example.com/page" required>
      <button type="submit">Generar URL corta</button>
    </form>
    <div id="createMessage" class="message" style="display:none;"></div>
  </div>

  <div class="card">
    <h2>URLs Guardadas</h2>
    <div id="urlListWrap">Cargando...</div>
  </div>

  <div class="card" id="statsCard" style="display:none;">
    <h2>Estadísticas</h2>
    <p id="statsMeta"></p>
    <div id="statsContent"></div>
    <div class="chart-wrap"><canvas id="statsChart"></canvas></div>
    <button id="closeStats">Cerrar</button>
  </div>
</div>
<script>
const api = 'api.php';
const baseUrl = '<?php echo htmlspecialchars(BASE_URL); ?>';
let statsChartInstance = null;

function showMessage(msg, type='success') {
  const el = document.getElementById('createMessage');
  el.style.display = 'block';
  el.className = type === 'error' ? 'message error' : 'message success';
  el.textContent = msg;
}

async function callApi(action, method='GET', payload=null) {
  let url = api + '?action=' + encodeURIComponent(action);
  const options = { method, headers: {} };
  if (payload) {
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(payload);
  }
  const res = await fetch(url, options);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Error de API');
  return data;
}

async function loadUrls() {
  try {
    const data = await callApi('urls');
    const list = data.urls;
    if (!list.length) {
      document.getElementById('urlListWrap').innerHTML = '<p>No hay URLs aún.</p>';
      return;
    }
    const rows = list.map(u => `
      <tr>
        <td><a class="link" href="${baseUrl}redirect.php?c=${encodeURIComponent(u.code)}" target="_blank">${baseUrl}redirect.php?c=${u.code}</a></td>
        <td>${u.total_accesses}</td>
        <td>${u.created_at}</td>
        <td><button class="btn" data-code="${u.code}">Ver stats</button></td>
      </tr>
    `).join('');
    document.getElementById('urlListWrap').innerHTML = `
      <div style="overflow-x:auto;"><table><thead><tr><th>Corto</th><th>Accesos</th><th>Creado</th><th>Acción</th></tr></thead><tbody>${rows}</tbody></table></div>`;
    document.querySelectorAll('#urlListWrap button').forEach(btn => {
      btn.addEventListener('click', () => showStats(btn.dataset.code));
    });
  } catch (err) {
    document.getElementById('urlListWrap').innerHTML = '<p class="error">No se pudo cargar la lista.</p>';
    console.error(err);
  }
}

async function showStats(code) {
  try {
    const url = api + '?action=stats&code=' + encodeURIComponent(code);
    const res = await fetch(url);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'No se pudo obtener stats');
    renderStats(data);
  } catch (err) {
    showMessage(err.message || 'No se pudo obtener stats.', 'error');
  }
}

function renderStats(data) {
  const content = document.getElementById('statsContent');
  const meta = document.getElementById('statsMeta');
  meta.innerHTML = `<strong>URL corta:</strong> <a class="link" href="${baseUrl}redirect.php?c=${encodeURIComponent(data.code)}" target="_blank">${baseUrl}redirect.php?c=${data.code}</a> <br><strong>Original:</strong> <span class="long-url"><a class="link" href="${data.target_url}" target="_blank" rel="noopener">${data.target_url}</a></span> <br><strong>Total accesos:</strong> ${data.total_accesses}`;
  let countryHtml = '<p>No hay accesos aún.</p>';
  if (data.countries.length) {
    countryHtml = '<table><thead><tr><th>País</th><th>Accesos</th></tr></thead><tbody>' + data.countries.map(c => `<tr><td>${c.country}</td><td>${c.hits}</td></tr>`).join('') + '</tbody></table>';
  }
  content.innerHTML = '<h3>Accesos por país</h3>' + countryHtml;
  const ctx = document.getElementById('statsChart');
  if (statsChartInstance) {
    statsChartInstance.destroy();
  }
  statsChartInstance = new Chart(ctx, {
    type: 'bar',
    data: { labels: data.days, datasets: [{ label: 'Accesos por día', data: data.hits, backgroundColor: 'rgba(59, 130, 246, 0.6)', borderColor: 'rgba(37, 99, 235, 1)', borderWidth: 1 }] },
    options: { scales: { y: { beginAtZero: true, precision: 0, ticks: { stepSize: 1 } } } }
  });
  document.getElementById('statsCard').style.display = 'block';
}

document.getElementById('createForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const url = document.getElementById('inputUrl').value.trim();
  if (!url) { showMessage('Ingresa una URL.', 'error'); return; }
  try {
    const data = await callApi('create', 'POST', { url });
    showMessage('URL corta generada: ' + data.short_url);
    document.getElementById('inputUrl').value = '';
    loadUrls();
  } catch (err) {
    showMessage(err.message || 'Error al crear URL', 'error');
  }
});

document.getElementById('closeStats').addEventListener('click', () => {
  document.getElementById('statsCard').style.display = 'none';
});

loadUrls();
</script>
</body>
</html>
