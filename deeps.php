<?php

// Improved Rasp.php
// - robust CSV reading (skips empty lines)
// - provides JSON endpoint when ?ajax=1 is requested
// - renders a simple live dashboard that polls the JSON endpoint every 2s
// - Enhanced error handling, caching, and performance optimizations

$csvPath = '/tmp/station/result.csv';
$rows = [];

// Cache configuration
$cacheFile = '/tmp/station/dashboard_cache.json';
$cacheTTL = 5; // seconds

// Function to read and parse CSV efficiently
function readCSVData($csvPath)
{
    if (!is_readable($csvPath)) {
        return [];
    }

    $rows = [];
    $handle = fopen($csvPath, 'r');
    if ($handle === false) {
        return [];
    }

    while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        // Skip empty lines
        if (count($data) === 1 && empty($data[0])) {
            continue;
        }
        // Normalize to at least 6 columns
        while (count($data) < 6) $data[] = null;
        $rows[] = $data;
    }
    fclose($handle);

    return $rows;
}

// Try to read from cache first (for non-AJAX requests)
if (!isset($_GET['ajax']) && is_readable($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $cachedData = json_decode(file_get_contents($cacheFile), true);
    if ($cachedData && isset($cachedData['latest'])) {
        $latest = $cachedData['latest'];
        $history = $cachedData['history'];
        $time = $cachedData['time'] ?? [];
        $temperature = $cachedData['temperature'] ?? [];
        $humidity = $cachedData['humidity'] ?? [];
        $pressure = $cachedData['pressure'] ?? [];
        $light = $cachedData['light'] ?? [];
        $gas = $cachedData['gas'] ?? [];
    } else {
        $rows = readCSVData($csvPath);
    }
} else {
    $rows = readCSVData($csvPath);
}

// Process data if not from cache
if (!isset($latest)) {
    $time = [];
    $temperature = [];
    $humidity = [];
    $pressure = [];
    $light = [];
    $gas = [];

    foreach ($rows as $data) {
        $ts = isset($data[0]) ? substr($data[0], 0, 19) : '';
        $time[] = $ts;
        $light[] = isset($data[1]) && $data[1] !== '' ? (float)$data[1] : null;
        $temperature[] = isset($data[2]) && $data[2] !== '' ? (float)$data[2] : null;
        $pressure[] = isset($data[3]) && $data[3] !== '' ? (float)$data[3] : null;
        $humidity[] = isset($data[4]) && $data[4] !== '' ? (float)$data[4] : null;
        $gas[] = isset($data[5]) && $data[5] !== '' ? (float)$data[5] : null;
    }

    // Helper to get last non-null value
    function last_value($arr)
    {
        for ($i = count($arr) - 1; $i >= 0; $i--) {
            if ($arr[$i] !== null && $arr[$i] !== '') return $arr[$i];
        }
        return null;
    }

    $latest = [
        'time' => last_value($time),
        'temperature' => last_value($temperature),
        'humidity' => last_value($humidity),
        'pressure' => last_value($pressure),
        'light' => last_value($light),
        'gas' => last_value($gas),
    ];

    // Prepare history (last N points)
    $historyPoints = 100; // Increased for better visualization
    $count = count($time);
    $start = max(0, $count - $historyPoints);
    $history = [];
    for ($i = $start; $i < $count; $i++) {
        $history[] = [
            'time' => $time[$i] ?? null,
            'temperature' => $temperature[$i] ?? null,
            'humidity' => $humidity[$i] ?? null,
            'pressure' => $pressure[$i] ?? null,
            'light' => $light[$i] ?? null,
            'gas' => $gas[$i] ?? null,
        ];
    }

    // Cache the processed data
    $cacheData = [
        'latest' => $latest,
        'history' => $history,
        'time' => $time,
        'temperature' => $temperature,
        'humidity' => $humidity,
        'pressure' => $pressure,
        'light' => $light,
        'gas' => $gas
    ];
    file_put_contents($cacheFile, json_encode($cacheData));
}

// Calculate statistics for enhanced display
function calculateStats($data)
{
    $validData = array_filter($data, function ($v) {
        return $v !== null && $v !== '';
    });
    if (empty($validData)) return null;
    return [
        'min' => min($validData),
        'max' => max($validData),
        'avg' => array_sum($validData) / count($validData),
        'current' => end($validData)
    ];
}

$stats = [
    'temperature' => calculateStats($temperature),
    'humidity' => calculateStats($humidity),
    'pressure' => calculateStats($pressure),
    'light' => calculateStats($light),
    'gas' => calculateStats($gas),
];

// If AJAX requested, return JSON with enhanced data
if (isset($_GET['ajax']) && ($_GET['ajax'] === '1' || $_GET['ajax'] === 'true')) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'latest' => $latest,
        'history' => $history,
        'stats' => $stats,
        'timestamp' => time()
    ]);
    exit;
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <title>Station Live Dashboard</title>
    <style>
        :root {
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            color-scheme: light dark;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: #f7f9fc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: #0f172a;
                color: #e2e8f0;
            }

            .card,
            .chartCard,
            .stats-panel {
                background: #1e293b;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }

            .meta,
            .stat-label {
                color: #94a3b8;
            }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
            min-height: 100vh;
        }

        .header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .status {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .meta {
            color: #64748b;
            font-size: 0.875rem;
        }

        .live-badge {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #22c55e;
            animation: pulse 2s infinite;
            margin-right: 0.5rem;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .card {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .card .value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .card .unit {
            font-size: 0.875rem;
            font-weight: normal;
            color: #64748b;
        }

        .card .trend {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            color: #64748b;
        }

        .trend-up {
            color: #ef4444;
        }

        .trend-down {
            color: #3b82f6;
        }

        .trend-stable {
            color: #10b981;
        }

        .stats-panel {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #64748b;
        }

        .stat-value {
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .chartCard {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .metric-controls {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .metric-controls label {
            font-size: 0.875rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .metric-controls input {
            cursor: pointer;
        }

        .refresh-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .refresh-control select {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: white;
            cursor: pointer;
        }

        #historyChart {
            width: 100% !important;
            height: 400px !important;
        }

        .empty {
            padding: 3rem;
            text-align: center;
            background: white;
            border-radius: 12px;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0.75rem;
            }

            .cards {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .card .value {
                font-size: 1.25rem;
            }

            #historyChart {
                height: 300px !important;
            }

            .stats-panel {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.25rem;
            }

            .metric-controls label {
                font-size: 0.75rem;
            }
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        button {
            background: none;
            border: 1px solid #cbd5e1;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        button:hover {
            background: #f1f5f9;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🌡️ Station Live Dashboard</h1>
            <div class="status">
                <div class="meta">
                    <span class="live-badge"></span>
                    <span id="connectionStatus">Live</span>
                </div>
                <div class="meta">Last update: <span id="lastTime"><?= htmlspecialchars($latest['time'] ?? '—') ?></span></div>
                <div class="meta">Auto-refresh: <span id="refreshInterval">2</span>s</div>
            </div>
        </div>

        <?php if ($latest['time'] === null) : ?>
            <div class="empty">
                📭 No data available yet.<br>
                Make sure the CSV exists at <code><?= htmlspecialchars($csvPath) ?></code> and contains valid data.
            </div>
        <?php else: ?>

            <div class="cards">
                <div class="card">
                    <div class="label">🌡️ Temperature</div>
                    <div class="value" id="valTemp"><?= $latest['temperature'] !== null ? number_format($latest['temperature'], 1) . '<span class="unit">°C</span>' : '—' ?></div>
                    <div class="trend" id="trendTemp"></div>
                </div>
                <div class="card">
                    <div class="label">💡 Light</div>
                    <div class="value" id="valLight"><?= $latest['light'] !== null ? number_format($latest['light'], 0) . '<span class="unit">lx</span>' : '—' ?></div>
                    <div class="trend" id="trendLight"></div>
                </div>
                <div class="card">
                    <div class="label">💧 Humidity</div>
                    <div class="value" id="valHum"><?= $latest['humidity'] !== null ? number_format($latest['humidity'], 1) . '<span class="unit">%</span>' : '—' ?></div>
                    <div class="trend" id="trendHum"></div>
                </div>
                <div class="card">
                    <div class="label">📊 Pressure</div>
                    <div class="value" id="valPres"><?= $latest['pressure'] !== null ? number_format($latest['pressure'], 1) . '<span class="unit">hPa</span>' : '—' ?></div>
                    <div class="trend" id="trendPres"></div>
                </div>
                <div class="card">
                    <div class="label">🌫️ Gas (Air Quality)</div>
                    <div class="value" id="valGas"><?= $latest['gas'] !== null ? number_format($latest['gas'], 0) . '<span class="unit">ppm</span>' : '—' ?></div>
                    <div class="trend" id="trendGas"></div>
                </div>
            </div>

            <!-- Statistics Panel -->
            <div class="stats-panel" id="statsPanel">
                <div class="stat-item">
                    <div class="stat-label">Temp Range</div>
                    <div class="stat-value" id="statTempRange">—</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Light Range</div>
                    <div class="stat-value" id="statLightRange">—</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Humidity Range</div>
                    <div class="stat-value" id="statHumRange">—</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Pressure Range</div>
                    <div class="stat-value" id="statPresRange">—</div>
                </div>
            </div>

            <div class="chartCard">
                <div class="chart-header">
                    <div class="metric-controls" id="metricControls">
                        <label><input type="checkbox" data-metric="temperature" checked> 🌡️ Temp</label>
                        <label><input type="checkbox" data-metric="light" checked> 💡 Light</label>
                        <label><input type="checkbox" data-metric="humidity"> 💧 Humidity</label>
                        <label><input type="checkbox" data-metric="pressure"> 📊 Pressure</label>
                        <label><input type="checkbox" data-metric="gas"> 🌫️ Gas</label>
                    </div>
                    <div class="refresh-control">
                        <button id="resetZoomBtn" title="Reset Chart View">↺ Reset Zoom</button>
                        <select id="refreshRate">
                            <option value="1">1s</option>
                            <option value="2" selected>2s</option>
                            <option value="5">5s</option>
                            <option value="10">10s</option>
                        </select>
                    </div>
                </div>
                <canvas id="historyChart"></canvas>
            </div>

        <?php endif; ?>
    </div>

    <script>
        // Configuration
        const endpoint = location.pathname + (location.search ? location.search + '&ajax=1' : '?ajax=1');
        let chart = null;
        let pollInterval = null;
        let currentRefreshRate = 2;
        let lastHistory = [];
        let previousValues = {};

        // Helper functions
        function formatNumber(v, digits = 1) {
            return (v === null || v === undefined) ? '—' : Number(v).toFixed(digits);
        }

        function updateTrend(elementId, currentValue, metric) {
            const prev = previousValues[metric];
            const element = document.getElementById(elementId);
            if (!element || prev === undefined || currentValue === null || currentValue === undefined) {
                if (element) element.innerHTML = '';
                return;
            }

            const diff = currentValue - prev;
            if (Math.abs(diff) < 0.01) {
                element.innerHTML = '→ Stable';
                element.className = 'trend trend-stable';
            } else if (diff > 0) {
                element.innerHTML = `↑ +${diff.toFixed(2)}`;
                element.className = 'trend trend-up';
            } else {
                element.innerHTML = `↓ ${diff.toFixed(2)}`;
                element.className = 'trend trend-down';
            }
        }

        function updateStats(stats) {
            if (!stats) return;

            if (stats.temperature) {
                document.getElementById('statTempRange').innerHTML = `${formatNumber(stats.temperature.min, 1)} - ${formatNumber(stats.temperature.max, 1)} °C`;
            }
            if (stats.light) {
                document.getElementById('statLightRange').innerHTML = `${formatNumber(stats.light.min, 0)} - ${formatNumber(stats.light.max, 0)} lx`;
            }
            if (stats.humidity) {
                document.getElementById('statHumRange').innerHTML = `${formatNumber(stats.humidity.min, 1)} - ${formatNumber(stats.humidity.max, 1)} %`;
            }
            if (stats.pressure) {
                document.getElementById('statPresRange').innerHTML = `${formatNumber(stats.pressure.min, 1)} - ${formatNumber(stats.pressure.max, 1)} hPa`;
            }
        }

        const metricInfo = {
            temperature: {
                label: 'Temperature (°C)',
                color: '#ef4444',
                yAxisID: 'y'
            },
            light: {
                label: 'Light (lx)',
                color: '#f59e0b',
                yAxisID: 'y1'
            },
            humidity: {
                label: 'Humidity (%)',
                color: '#3b82f6',
                yAxisID: 'y'
            },
            pressure: {
                label: 'Pressure (hPa)',
                color: '#10b981',
                yAxisID: 'y'
            },
            gas: {
                label: 'Gas (ppm)',
                color: '#8b5cf6',
                yAxisID: 'y1'
            },
        };

        function getSelectedMetrics() {
            const inputs = document.querySelectorAll('#metricControls input[type=checkbox]');
            const sel = [];
            inputs.forEach(i => {
                if (i.checked) sel.push(i.dataset.metric);
            });
            return sel.length ? sel : ['temperature', 'light'];
        }

        function renderChart(history) {
            const ctx = document.getElementById('historyChart').getContext('2d');
            const labels = history.map(h => h.time ? h.time.substr(11, 8) : '');
            const selected = getSelectedMetrics();

            const datasets = selected.map(m => ({
                label: metricInfo[m].label,
                data: history.map(h => h[m]),
                borderColor: metricInfo[m].color,
                backgroundColor: metricInfo[m].color + '20',
                borderWidth: 2,
                pointRadius: 1,
                pointHoverRadius: 5,
                tension: 0.3,
                fill: false,
                yAxisID: metricInfo[m].yAxisID || 'y'
            }));

            const hasLightOrGas = selected.some(m => m === 'light' || m === 'gas');

            if (!chart) {
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: 'Temperature / Humidity / Pressure'
                                }
                            },
                            y1: {
                                position: 'right',
                                beginAtZero: true,
                                title: {
                                    display: hasLightOrGas,
                                    text: 'Light (lx) / Gas (ppm)'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        }
                    }
                });
            } else {
                chart.data.labels = labels;
                chart.data.datasets = datasets;
                chart.update();
            }
        }

        // Reset zoom functionality
        document.getElementById('resetZoomBtn')?.addEventListener('click', () => {
            if (chart) {
                chart.resetZoom();
            }
        });

        async function poll() {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000);

                const res = await fetch(endpoint, {
                    cache: 'no-store',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                const latest = json.latest || {};

                // Update connection status
                document.getElementById('connectionStatus').innerHTML = '<span style="color:#22c55e;">●</span> Live';
                document.getElementById('lastTime').textContent = latest.time || '—';
                document.getElementById('lastTime').style.color = '';

                // Update values with trends
                const temp = latest.temperature;
                const light = latest.light;
                const hum = latest.humidity;
                const pres = latest.pressure;
                const gas = latest.gas;

                document.getElementById('valTemp').innerHTML = temp !== null ? formatNumber(temp, 1) + '<span class="unit">°C</span>' : '—';
                document.getElementById('valLight').innerHTML = light !== null ? formatNumber(light, 0) + '<span class="unit">lx</span>' : '—';
                document.getElementById('valHum').innerHTML = hum !== null ? formatNumber(hum, 1) + '<span class="unit">%</span>' : '—';
                document.getElementById('valPres').innerHTML = pres !== null ? formatNumber(pres, 1) + '<span class="unit">hPa</span>' : '—';
                document.getElementById('valGas').innerHTML = gas !== null ? formatNumber(gas, 0) + '<span class="unit">ppm</span>' : '—';

                updateTrend('trendTemp', temp, 'temperature');
                updateTrend('trendLight', light, 'light');
                updateTrend('trendHum', hum, 'humidity');
                updateTrend('trendPres', pres, 'pressure');
                updateTrend('trendGas', gas, 'gas');

                previousValues = {
                    temperature: temp,
                    light: light,
                    humidity: hum,
                    pressure: pres,
                    gas: gas
                };

                // Update stats
                if (json.stats) {
                    updateStats(json.stats);
                }

                // Update history and chart
                if (json.history && json.history.length) {
                    lastHistory = json.history;
                    renderChart(lastHistory);
                }
            } catch (e) {
                console.error('Poll error:', e);
                document.getElementById('connectionStatus').innerHTML = '<span style="color:#ef4444;">●</span> Offline';
                document.getElementById('lastTime').style.color = '#ef4444';
            }
        }

        // Refresh rate control
        function startPolling(intervalSeconds) {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(poll, intervalSeconds * 1000);
            document.getElementById('refreshInterval').textContent = intervalSeconds;
        }

        document.getElementById('refreshRate')?.addEventListener('change', (e) => {
            currentRefreshRate = parseInt(e.target.value);
            startPolling(currentRefreshRate);
        });

        // Initial render from server-side data
        const initialHistory = <?php echo json_encode($history); ?>;
        if (initialHistory && initialHistory.length) {
            lastHistory = initialHistory;
            renderChart(initialHistory);
        }

        // Initialize previous values
        previousValues = {
            temperature: <?php echo json_encode($latest['temperature'] ?? null); ?>,
            light: <?php echo json_encode($latest['light'] ?? null); ?>,
            humidity: <?php echo json_encode($latest['humidity'] ?? null); ?>,
            pressure: <?php echo json_encode($latest['pressure'] ?? null); ?>,
            gas: <?php echo json_encode($latest['gas'] ?? null); ?>
        };

        // Re-render when metric selection changes
        document.querySelectorAll('#metricControls input[type=checkbox]').forEach(cb => {
            cb.addEventListener('change', () => renderChart(lastHistory));
        });

        // Start polling
        startPolling(currentRefreshRate);
        poll(); // Immediate first poll
    </script>
</body>

</html>