<?php

// Improved Rasp.php
// - robust CSV reading (skips empty lines)
// - provides JSON endpoint when ?ajax=1 is requested
// - renders a simple live dashboard that polls the JSON endpoint every 5s

$csvPath = '/tmp/station/result.csv';
$rows = [];
if (is_readable($csvPath)) {
    // Read lines and skip empty ones
    $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            // Basic CSV parse (handles simple cases)
            $data = str_getcsv($line, ',', '"', '\\');
            // normalize to at least 6 columns
            while (count($data) < 6) $data[] = null;
            $rows[] = $data;
        }
    }
}

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

// helper to get last non-null value of an array
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

// Prepare history (last N points) for charting
$historyPoints = 60; // last 60 samples
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



// If AJAX requested, return JSON
if (isset($_GET['ajax']) && ($_GET['ajax'] === '1' || $_GET['ajax'] === 'true')) {
    header('Content-Type: application/json');
    echo json_encode(['latest' => $latest, 'history' => $history]);
    exit;
}



?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Station Live Dashboard</title>
    <style>
        :root {
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            color-scheme: light;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0.5rem;
            background: #f7f9fc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0.25rem;
            min-height: calc(100vh - 1rem);
            box-sizing: border-box;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .card {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(2, 6, 23, 0.06);
        }

        .card .label {
            font-size: 0.85rem;
            color: #475569;
        }

        .card .value {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .chartCard {
            margin-top: 1rem;
            background: white;
            padding: 1rem;
            border-radius: 8px;
        }

        /* Make canvas responsive by filling its container */
        #historyChart {
            width: 100% !important;
            height: 220px !important;
            /* default height for larger screens */
            display: block;
        }

        /* Small / 7-inch screens adjustments */
        @media (max-width: 800px),
        (max-height: 600px) {
            body {
                margin: 0.25rem;
            }

            .container {
                padding: 0.25rem;
                min-height: 100vh;
            }

            .header {
                gap: 0.5rem;
            }

            .header h1 {
                font-size: 1.05rem;
                margin: 0;
            }

            .meta {
                font-size: 0.82rem;
            }

            .cards {
                grid-template-columns: 1fr;
                gap: 0.4rem;
                margin-top: 0.6rem;
            }

            .card {
                padding: 0.5rem 0.6rem;
                border-radius: 6px;
            }

            .card .label {
                font-size: 0.78rem;
            }

            .card .value {
                font-size: 1.4rem;
            }

            .chartCard {
                padding: 0.5rem;
            }

            /* Taller chart on small screens to fill vertical space */
            #historyChart {
                height: calc(45vh) !important;
            }
        }

        .meta {
            color: #64748b;
            font-size: 0.9rem;
        }

        .empty {
            padding: 2rem;
            text-align: center;
            color: #94a3b8;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" integrity="" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Station Live Dashboard</h1>
            <div class="meta">Last read: <span id="lastTime"><?= htmlspecialchars($latest['time'] ?? '—') ?></span></div>
            <label style="font-size:0.85rem;">Refresh:
                <select id="pollIntervalSelect" style="margin-left:6px;padding:4px;border-radius:6px;border:1px solid #e2e8f0;">
                    <option value="1000">1s</option>
                    <option value="2000">2s</option>
                    <option value="5000">5s</option>
                </select>
            </label>
            <button id="pollToggleBtn" style="padding:4px 8px;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;cursor:pointer;margin-left:6px;">Pause</button>
            <button id="simulateBtn" style="padding:4px 8px;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;cursor:pointer;margin-left:6px;">Simulate</button>
        </div>
    </div>

    <?php if ($latest['time'] === null) : ?>
        <div class="empty">No data available yet. Make sure the CSV exists at <code><?= htmlspecialchars($csvPath) ?></code>.</div>
    <?php else: ?>

        <div class="cards">
            <div class="card">
                <div class="label">Temperature</div>
                <div class="value" id="valTemp"><?= $latest['temperature'] !== null ? number_format($latest['temperature'], 2) . ' °C' : '—' ?></div>
            </div>
            <div class="card">
                <div class="label">Light</div>
                <div class="value" id="valLight"><?= $latest['light'] !== null ? number_format($latest['light'], 2) . ' lx' : '—' ?></div>
            </div>
            <div class="card">
                <div class="label">Pressure</div>
                <div class="value" id="valPres"><?= $latest['pressure'] !== null ? number_format($latest['pressure'], 2) . ' hPa' : '—' ?></div>
            </div>
            <div class="card">
                <div class="label">Gas (air quality)</div>
                <div class="value" id="valGas"><?= $latest['gas'] !== null ? number_format($latest['gas'], 2) . ' ppm' : '—' ?></div>
            </div>
        </div>

        <div class="chartCard">
            <!-- Metric selection controls -->
            <div id="metricControls" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.5rem;">
                <label style="font-size:0.85rem;"><input type="checkbox" id="chk_temp" data-metric="temperature" checked> Temp</label>
                <label style="font-size:0.85rem;"><input type="checkbox" id="chk_light" data-metric="light" checked> Light</label>
                <label style="font-size:0.85rem;"><input type="checkbox" id="chk_hum" data-metric="humidity"> Humidity</label>
                <label style="font-size:0.85rem;"><input type="checkbox" id="chk_pres" data-metric="pressure"> Pressure</label>
                <label style="font-size:0.85rem;"><input type="checkbox" id="chk_gas" data-metric="gas"> Gas</label>
            </div>
            <canvas id="historyChart" height="120"></canvas>
        </div>

    <?php endif; ?>
    </div>

    <script>
        // Fetch JSON endpoint and update DOM + chart
        // Preserve existing query params (e.g. ?demo=1) when requesting the ajax JSON
        const endpoint = location.pathname + (location.search ? location.search + '&ajax=1' : '?ajax=1');
        let chart = null;

        function formatNumber(v, digits = 2) {
            return (v === null || v === undefined) ? '—' : Number(v).toFixed(digits);
        }

        function buildDatasets(history) {
            const labels = history.map(h => h.time ? h.time.substr(11, 8) : '');
            const metrics = {
                temperature: history.map(h => h.temperature),
                light: history.map(h => h.light),
                humidity: history.map(h => h.humidity),
                pressure: history.map(h => h.pressure),
                gas: history.map(h => h.gas),
            };
            return {
                labels,
                metrics
            };
        }

        // mapping of metric -> chart display properties
        const metricInfo = {
            temperature: {
                label: 'Temperature (°C)',
                color: '#ef4444',
                bg: 'rgba(239,68,68,0.08)'
            },
            light: {
                label: 'Light (lx)',
                color: '#f59e0b',
                bg: 'rgba(245,158,11,0.06)'
            },
            humidity: {
                label: 'Humidity (%)',
                color: '#3b82f6',
                bg: 'rgba(59,130,246,0.06)'
            },
            pressure: {
                label: 'Pressure (hPa)',
                color: '#10b981',
                bg: 'rgba(16,185,129,0.06)'
            },
            gas: {
                label: 'Gas (ppm)',
                color: '#8b5cf6',
                bg: 'rgba(139,92,246,0.06)'
            },
        };

        // Read which metrics are selected in the controls
        function getSelectedMetrics() {
            const inputs = document.querySelectorAll('#metricControls input[type=checkbox]');
            const sel = [];
            inputs.forEach(i => {
                if (i.checked) sel.push(i.dataset.metric);
            });
            // default to temperature + light if nothing selected
            return sel.length ? sel : ['temperature', 'light'];
        }

        let lastSelectedMetrics = null;

        function renderChart(history) {
            const ctx = document.getElementById('historyChart').getContext('2d');
            const dataObj = buildDatasets(history);
            const selected = getSelectedMetrics();

            // build dataset array based on selection
            const datasets = selected.map(m => ({
                label: metricInfo[m].label,
                data: dataObj.metrics[m],
                borderColor: metricInfo[m].color,
                backgroundColor: metricInfo[m].bg,
                tension: 0.25,
                spanGaps: true
            }));

            if (!chart) {
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dataObj.labels,
                        datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            } else {
                chart.data.labels = dataObj.labels;
                // If selection didn't change and dataset count matches, update in-place to avoid full redraw
                const sameSelection = Array.isArray(lastSelectedMetrics) && lastSelectedMetrics.length === selected.length && lastSelectedMetrics.every((v, i) => v === selected[i]);
                if (sameSelection && chart.data.datasets.length === datasets.length) {
                    for (let i = 0; i < datasets.length; i++) {
                        chart.data.datasets[i].data = datasets[i].data;
                    }
                    chart.update();
                } else {
                    chart.data.datasets = datasets; // replace datasets according to selection
                    chart.update();
                }
            }

            lastSelectedMetrics = selected.slice();
        }

        let lastHistory = [];

        // Polling configuration
        const POLL_INTERVAL_MS = 5000; // default 5000ms (5s)
        let _pollTimeout = null;
        let _pollingPaused = false;

        async function poll() {
            try {
                const res = await fetch(endpoint, {
                    cache: 'no-store'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                const latest = json.latest || {};
                document.getElementById('lastTime').textContent = latest.time || '—';
                document.getElementById('valTemp').textContent = latest.temperature !== null ? formatNumber(latest.temperature) + ' °C' : '—';
                document.getElementById('valLight').textContent = latest.light !== null ? formatNumber(latest.light) + ' lx' : '—';
                document.getElementById('valPres').textContent = latest.pressure !== null ? formatNumber(latest.pressure) + ' hPa' : '—';
                document.getElementById('valGas').textContent = latest.gas !== null ? formatNumber(latest.gas) + ' ppm' : '—';
                lastHistory = json.history || [];
                renderChart(lastHistory);
            } catch (e) {
                console.error('poll error', e);
            }
        }

        function startPolling(intervalMs) {
            stopPolling();
            _pollingPaused = false;
            (async function tick() {
                await poll();
                if (!_pollingPaused) _pollTimeout = setTimeout(tick, intervalMs);
            })();
        }

        function stopPolling() {
            _pollingPaused = true;
            if (_pollTimeout) {
                clearTimeout(_pollTimeout);
                _pollTimeout = null;
            }
        }

        // Simulation (client-side synthetic data) for testing realtime UI
        let _simInterval = null;
        let _simRunning = false;

        function startSimulation(intervalMs) {
            stopSimulation();
            _simRunning = true;
            // seed from lastHistory or create baseline
            let last = lastHistory.length ? lastHistory[lastHistory.length - 1] : {
                time: new Date().toISOString().slice(0, 19).replace('T', ' '),
                temperature: 22,
                light: 300,
                humidity: 50,
                pressure: 1013,
                gas: 400
            };
            _simInterval = setInterval(() => {
                // random walk small changes
                const next = Object.assign({}, last);
                next.time = new Date().toISOString().slice(0, 19).replace('T', ' ');
                next.temperature = Math.max(-50, +(next.temperature + (Math.random() - 0.5) * 0.4).toFixed(2));
                next.light = Math.max(0, +(next.light + (Math.random() - 0.5) * 8).toFixed(1));
                next.humidity = Math.max(0, Math.min(100, +(next.humidity + (Math.random() - 0.5) * 0.6).toFixed(2)));
                next.pressure = +(next.pressure + (Math.random() - 0.5) * 0.3).toFixed(2);
                next.gas = Math.max(0, +(next.gas + (Math.random() - 0.5) * 1.5).toFixed(1));

                // push to lastHistory and cap length
                lastHistory.push(next);
                if (lastHistory.length > 120) lastHistory.shift();
                last = next;

                // update cards
                document.getElementById('lastTime').textContent = next.time;
                document.getElementById('valTemp').textContent = next.temperature !== null ? formatNumber(next.temperature) + ' °C' : '—';
                document.getElementById('valLight').textContent = next.light !== null ? formatNumber(next.light) + ' lx' : '—';
                document.getElementById('valPres').textContent = next.pressure !== null ? formatNumber(next.pressure) + ' hPa' : '—';
                document.getElementById('valGas').textContent = next.gas !== null ? formatNumber(next.gas) + ' ppm' : '—';

                // re-render chart using lastHistory
                renderChart(lastHistory);
            }, intervalMs);
        }

        function stopSimulation() {
            _simRunning = false;
            if (_simInterval) {
                clearInterval(_simInterval);
                _simInterval = null;
            }
        }

        // initial render from server-side data
        const initialHistory = <?php echo json_encode($history); ?>;
        if (initialHistory.length) {
            lastHistory = initialHistory;
            renderChart(initialHistory);
        }

        // re-render when user changes metric selection
        document.querySelectorAll('#metricControls input[type=checkbox]').forEach(cb => {
            cb.addEventListener('change', () => {
                renderChart(lastHistory);
            });
        });

        // Poll toggle button
        const pollBtn = document.getElementById('pollToggleBtn');
        pollBtn.addEventListener('click', () => {
            if (_pollingPaused) {
                startPolling(POLL_INTERVAL_MS);
                pollBtn.textContent = 'Pause';
            } else {
                stopPolling();
                pollBtn.textContent = 'Resume';
            }
        });

        // Simulation toggle
        const simBtn = document.getElementById('simulateBtn');
        simBtn.addEventListener('click', () => {
            if (_simRunning) {
                stopSimulation();
                simBtn.textContent = 'Simulate';
                // resume polling when simulation stops
                startPolling(POLL_INTERVAL_MS);
            } else {
                // pause polling while simulating
                stopPolling();
                startSimulation(POLL_INTERVAL_MS);
                simBtn.textContent = 'Stop Sim';
            }
        });

        // Start polling by default
        startPolling(POLL_INTERVAL_MS);
    </script>
</body>

</html>