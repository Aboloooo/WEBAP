<?php
// Read CSV (if present) and prepare latest + history for client use
$csvPath = '/tmp/station/result.csv';
$history = [];
$latest = [
    'time' => null,
    'temperature' => null,
    'light' => null,
    'pressure' => null,
    'gas' => null,
];

if (file_exists($csvPath)) {
    $lines = array_filter(array_map('trim', file($csvPath))); // drop empty lines
    foreach ($lines as $ln) {
        $parts = str_getcsv($ln, ',', '"', '\\');
        // Expecting at least 6 columns: time, light, temperature, pressure, humidity, gas
        $parts = array_pad($parts, 6, null);
        $time = substr($parts[0] ?? '', 0, 19);
        $light = is_numeric($parts[1]) ? (float)$parts[1] : null;
        $temperature = is_numeric($parts[2]) ? (float)$parts[2] : null;
        $pressure = is_numeric($parts[3]) ? (float)$parts[3] : null;
        $gas = is_numeric($parts[5]) ? (float)$parts[5] : null;
        $history[] = [
            'time' => $time,
            'temperature' => $temperature,
            'light' => $light,
            'pressure' => $pressure,
            'gas' => $gas
        ];
    }
    if (count($history)) {
        $latest = end($history);
    }

    // If requested as ajax, return JSON (latest + history)
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'latest' => $latest,
            'history' => array_slice($history, -120)
        ]);
        exit;
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Station Dashboard</title>
    <style>
        body {
            font-family: system-ui, -apple-system, Roboto, "Segoe UI", Arial;
            background: #f3f4f6;
            color: #0f172a;
            margin: 0;
            padding: 0.5rem
        }

        .container {
            max-width: 1000px;
            margin: 0 auto
        }

        .header {
            display: flex;
            align-items: center;
            gap: 1rem
        }

        .meta {
            color: #64748b
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem
        }

        .card {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(2, 6, 23, 0.06);
            cursor: pointer
        }

        .card .label {
            font-size: 0.85rem;
            color: #475569
        }

        .card .value {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 0.25rem
        }

        .card.selected {
            border: 2px solid #3b82f6;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.06);
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.03), rgba(59, 130, 246, 0.01))
        }

        .chartCard {
            margin-top: 0.75rem;
            background: white;
            padding: 1rem;
            border-radius: 8px
        }

        #historyChart {
            width: 100% !important;
            height: 320px !important;
            display: block
        }

        @media (max-width:800px),
        (max-height:600px) {
            .cards {
                grid-template-columns: 1fr
            }

            #historyChart {
                height: 45vh !important
            }
        }

        .controls {
            margin-left: auto;
            display: flex;
            gap: 0.5rem;
            align-items: center
        }

        .controls select {
            padding: 4px;
            border-radius: 6px;
            border: 1px solid #e2e8f0
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:2.05rem">Station Live Dashboard</h1>
            <div class="meta" style="float:right">Last read: <span id="lastTime"><?= htmlspecialchars($latest['time'] ?? '—') ?></span></div>
            <div class="controls">
                <label style="font-size:0.9rem">Refresh:
                    <select id="pollIntervalSelect">
                        <option value="1000">1s</option>
                        <option value="2000">2s</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="cards">
            <div class="card" data-metric="temperature" id="card_temp">
                <div class="label">Temperature</div>
                <div class="value" id="valTemp"><?= $latest['temperature'] !== null ? number_format($latest['temperature'], 2) . ' °C' : '—' ?></div>
            </div>
            <div class="card" data-metric="light" id="card_light">
                <div class="label">Light</div>
                <div class="value" id="valLight"><?= $latest['light'] !== null ? number_format($latest['light'], 2) . ' lx' : '—' ?></div>
            </div>
            <div class="card" data-metric="pressure" id="card_pres">
                <div class="label">Pressure</div>
                <div class="value" id="valPres"><?= $latest['pressure'] !== null ? number_format($latest['pressure'], 2) . ' hPa' : '—' ?></div>
            </div>
            <div class="card" data-metric="gas" id="card_gas">
                <div class="label">Gas (air quality)</div>
                <div class="value" id="valGas"><?= $latest['gas'] !== null ? number_format($latest['gas'], 2) . ' ppm' : '—' ?></div>
            </div>
        </div>

        <div class="chartCard">
            <canvas id="historyChart"></canvas>
        </div>
    </div>

    <script>
        // Initialize data from server (history may be empty)
        const serverHistory = <?= json_encode($history) ?> || [];
        let lastHistory = Array.isArray(serverHistory) ? serverHistory.slice() : [];

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
            pressure: {
                label: 'Pressure (hPa)',
                color: '#10b981',
                bg: 'rgba(16,185,129,0.06)'
            },
            gas: {
                label: 'Gas (ppm)',
                color: '#8b5cf6',
                bg: 'rgba(139,92,246,0.06)'
            }
        };

        function formatNumber(v, digits = 2) {
            return (v === null || v === undefined) ? '—' : Number(v).toFixed(digits);
        }

        function buildDatasets(history) {
            const labels = history.map(h => h.time ? h.time.substr(11, 8) : '');
            const metrics = {
                temperature: history.map(h => h.temperature),
                light: history.map(h => h.light),
                pressure: history.map(h => h.pressure),
                gas: history.map(h => h.gas)
            };
            return {
                labels,
                metrics
            };
        }

        function getSelectedMetrics() {
            const sel = [];
            document.querySelectorAll('.cards .card.selected').forEach(c => {
                const m = c.dataset.metric;
                if (m) sel.push(m);
            });
            return sel.length ? sel : ['temperature'];
        }

        let chart = null;
        let lastSelected = null;

        function renderChart(history) {
            const ctx = document.getElementById('historyChart').getContext('2d');
            const dataObj = buildDatasets(history);
            const selected = getSelectedMetrics();
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
                const same = Array.isArray(lastSelected) && lastSelected.length === selected.length && lastSelected.every((v, i) => v === selected[i]);
                if (same && chart.data.datasets.length === datasets.length) {
                    for (let i = 0; i < datasets.length; i++) chart.data.datasets[i].data = datasets[i].data;
                    chart.update();
                } else {
                    chart.data.datasets = datasets;
                    chart.update();
                }
            }
            lastSelected = selected.slice();
        }

        // UI: card selection
        document.querySelectorAll('.cards .card').forEach(card => {
            card.addEventListener('click', () => {
                card.classList.toggle('selected');
                renderChart(lastHistory);
            });
        });

        // ensure only temperature selected by default
        (function() {
            const t = document.getElementById('card_temp');
            if (t) t.classList.add('selected');
            renderChart(lastHistory);
        })();

        // Polling — fetch real values from server JSON endpoint (?ajax=1)
        let POLL_INTERVAL_MS = 1000; // default 1s
        let _pollTimeout = null;
        let _pollingPaused = false;

        const endpoint = location.pathname + (location.search ? location.search + '&ajax=1' : '?ajax=1');

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
                lastHistory = json.history || lastHistory;
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

        // Poll interval select handling
        const pollSelect = document.getElementById('pollIntervalSelect');
        pollSelect.value = String(POLL_INTERVAL_MS);
        pollSelect.addEventListener('change', () => {
            const v = parseInt(pollSelect.value, 10) || 1000;
            POLL_INTERVAL_MS = v;
            // restart polling with new interval
            if (!_pollingPaused) startPolling(POLL_INTERVAL_MS);
        });

        // Start polling by default (use real server values)
        startPolling(POLL_INTERVAL_MS);
    </script>
</body>

</html>