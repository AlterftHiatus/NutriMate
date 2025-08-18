<?php
require_once "../functions/auth.php";
if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Beraktivitas — Health Tracker</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
            margin: 0;
            background: #f7f7f9;
        }

        header {
            padding: 12px 16px;
            background: #111;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        header a {
            color: #fff;
            text-decoration: none;
            opacity: .9;
        }

        .wrap {
            max-width: 960px;
            margin: 16px auto;
            padding: 0 16px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .grid {
            display: grid;
            gap: 12px;
        }

        @media (min-width: 900px) {
            .grid-2 {
                grid-template-columns: 1.1fr .9fr;
            }
        }

        #map {
            width: 100%;
            height: 52vh;
            border-radius: 10px;
        }

        .controls {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        select,
        button {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            cursor: pointer;
        }

        button.primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        button.danger {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
        }

        button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .stat {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .stat .label {
            font-size: 12px;
            color: #6b7280;
        }

        .stat .value {
            font-size: 18px;
            font-weight: 700;
        }

        .note {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
        }

        .summary {
            margin-top: 12px;
            padding: 12px;
            background: #ecfeff;
            border: 1px solid #a5f3fc;
            border-radius: 8px;
            display: none;
        }

        .summary strong {
            display: inline-block;
            min-width: 90px;
        }
    </style>
</head>

<body>
    <!-- [Your existing HTML structure remains the same] -->
    <header>
        <div>Beraktivitas</div>
        <nav><a href="dashboard.php">← Kembali ke Dashboard</a></nav>
    </header>

    <div class="wrap grid grid-2">
        <section class="card">
            <div id="map"></div>
            <p class="note">Tips: Untuk akurasi lebih baik, aktifkan GPS dan tunggu hingga lokasi “mengunci” sebelum menekan <b>Mulai</b>.</p>
        </section>

        <section class="card">
            <div class="controls">
                <label for="activity">Aktivitas</label>
                <select id="activity">
                    <option value="walk">Jalan</option>
                    <option value="jog">Jogging</option>
                    <option value="bike">Bersepeda</option>
                </select>

                <button id="btnStart" class="primary">Mulai</button>
                <button id="btnStop" class="danger" disabled>Stop</button>
            </div>

            <div class="stats">
                <div class="stat">
                    <div class="label">Durasi</div>
                    <div class="value" id="durasi">00:00:00</div>
                </div>
                <div class="stat">
                    <div class="label">Jarak</div>
                    <div class="value"><span id="jarak">0.00</span> km</div>
                </div>
                <div class="stat">
                    <div class="label">Kecepatan</div>
                    <div class="value"><span id="pace">0.0</span> km/jam</div>
                </div>
            </div>

            <div class="summary" id="summary">
                <h3>Ringkasan Sesi</h3>
                <p><strong>Aktivitas:</strong> <span id="sumAct">-</span></p>
                <p><strong>Durasi:</strong> <span id="sumDur">-</span></p>
                <p><strong>Jarak:</strong> <span id="sumDist">-</span> km</p>
                <p><strong>Kalori:</strong> <span id="sumCal">-</span> kcal</p>
                <p><strong>EXP:</strong> <span id="sumExp">-</span></p>
                <button id="btnFinish" class="primary">Selesai</button>

            </div>

            <!-- <p class="note">Step 1 : belum kirim data ke server. Setelah ini (Step 2) kita hitung kalori & EXP lalu kirim ke backend untuk update EXP & streak.</p> -->
        </section>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Konfigurasi
        const MET_VALUES = {
            walk: 3.5,
            jog: 7.0,
            bike: 6.8
        };
        const USER_WEIGHT_KG = 65;
        const EXP_PER_KM = 10;

        // State
        let map, userMarker, pathLine, watchId = null;
        let points = [];
        let startedAt = null;
        let durationTimer = null;
        let totalDistanceM = 0;

        // Element References
        const btnStart = document.getElementById('btnStart');
        const btnStop = document.getElementById('btnStop');
        const btnFinish = document.getElementById('btnFinish');
        const elDurasi = document.getElementById('durasi');
        const elJarak = document.getElementById('jarak');
        const elPace = document.getElementById('pace');
        const summaryBox = document.getElementById('summary');
        const sumAct = document.getElementById('sumAct');
        const sumDur = document.getElementById('sumDur');
        const sumDist = document.getElementById('sumDist');
        const sumCal = document.getElementById('sumCal');
        const sumExp = document.getElementById('sumExp');

        // Fungsi Utama
        function initMap() {
            map = L.map('map').setView([-6.200000, 106.816666], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(initPosition, handleGeoError, {
                    enableHighAccuracy: true,
                    timeout: 10000
                });
            } else {
                alert('Browser tidak mendukung Geolocation.');
            }
        }

        function initPosition(pos) {
            const { latitude, longitude } = pos.coords;
            map.setView([latitude, longitude], 16);
            userMarker = L.marker([latitude, longitude]).addTo(map);
            pathLine = L.polyline([], { weight: 4 }).addTo(map);
        }

        function handleGeoError(err) {
            console.warn('Error lokasi:', err.message);
        }

        // Fungsi Utilitas
        function formatHHMMSS(sec) {
            const h = Math.floor(sec / 3600).toString().padStart(2, '0');
            const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(sec % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        function haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const toRad = deg => deg * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
            return 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)) * R;
        }

        // Fungsi Tracking
        function startTracking() {
            resetTrackingState();
            startTimer();
            
            watchId = navigator.geolocation.watchPosition(
                updatePosition,
                handleTrackingError,
                { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
            );
        }

        function updatePosition(pos) {
            const { latitude, longitude, accuracy } = pos.coords;
            if (accuracy > 50 && points.length < 3) return;

            const latlng = [latitude, longitude];
            points.push({ lat: latitude, lng: longitude, t: Date.now() });

            if (!userMarker) {
                userMarker = L.marker(latlng).addTo(map);
            } else {
                userMarker.setLatLng(latlng);
            }

            pathLine.addLatLng(latlng);
            map.panTo(latlng);

            updateDistance(latlng);
            updateStats();
        }

        function updateDistance(latlng) {
            if (points.length >= 2) {
                const prev = points[points.length - 2];
                const curr = points[points.length - 1];
                const d = haversine(prev.lat, prev.lng, curr.lat, curr.lng);
                if (d > 0 && d < 100) totalDistanceM += d;
            }
        }

        function stopTracking() {
            if (watchId) navigator.geolocation.clearWatch(watchId);
            stopTimer();
            showSummary();
        }

        // Fungsi UI
        function resetTrackingState() {
            points = [];
            totalDistanceM = 0;
            startedAt = new Date();
            
            if (pathLine) pathLine.remove();
            pathLine = L.polyline([], { weight: 4 }).addTo(map);
            
            elJarak.textContent = '0.00';
            elPace.textContent = '0.0';
            elDurasi.textContent = '00:00:00';
            summaryBox.style.display = 'none';
        }

        function startTimer() {
            if (durationTimer) clearInterval(durationTimer);
            durationTimer = setInterval(updateStats, 1000);
        }

        function stopTimer() {
            if (durationTimer) clearInterval(durationTimer);
            durationTimer = null;
        }

        function updateStats() {
            const seconds = Math.floor((Date.now() - startedAt.getTime()) / 1000);
            const km = (totalDistanceM / 1000);
            const speed = seconds > 0 ? (km) / (seconds / 3600) : 0;
            
            elDurasi.textContent = formatHHMMSS(seconds);
            elJarak.textContent = km.toFixed(2);
            elPace.textContent = speed.toFixed(1);
        }

        function showSummary() {
            const seconds = Math.floor((Date.now() - startedAt.getTime()) / 1000);
            const km = (totalDistanceM / 1000);
            const actKey = document.getElementById('activity').value;
            
            // Hitung kalori dan EXP
            const hours = seconds / 3600;
            const calories = MET_VALUES[actKey] * USER_WEIGHT_KG * hours;
            const exp = km * EXP_PER_KM;
            
            // Tampilkan ringkasan
            const activityNames = { walk: 'Jalan', jog: 'Jogging', bike: 'Bersepeda' };
            sumAct.textContent = activityNames[actKey] || '-';
            sumDur.textContent = formatHHMMSS(seconds);
            sumDist.textContent = km.toFixed(2);
            sumCal.textContent = Math.round(calories);
            sumExp.textContent = Math.round(exp);
            
            summaryBox.style.display = 'block';
        }

        // Event Listeners
        btnStart.addEventListener('click', () => {
            btnStart.disabled = true;
            btnStop.disabled = false;
            startTracking();
        });

        btnStop.addEventListener('click', () => {
            btnStop.disabled = true;
            btnStart.disabled = false;
            stopTracking();
        });

        btnFinish.addEventListener('click', async () => {
    btnFinish.disabled = true;
    btnFinish.textContent = "Menyimpan...";
    
    try {
        const actKey = document.getElementById('activity').value;
        const seconds = Math.floor((Date.now() - startedAt.getTime()) / 1000);
        const km = (totalDistanceM / 1000);
        const calories = Math.round(MET_VALUES[actKey] * USER_WEIGHT_KG * (seconds / 3600));
        const exp = Math.round(km * EXP_PER_KM);

        const response = await fetch('../functions/save_activity.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                activity: actKey,
                duration: seconds,
                calories: calories,
                exp: exp,
                distance: km
            })
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error(`Invalid JSON response: ${text}`);
        }

        if (!data.success) {
            throw new Error(data.message || 'Save failed');
        }

        alert('Data tersimpan!');
        window.location.href = 'dashboard.php';

    } catch (error) {
        console.error('Error:', error);
        alert('Gagal menyimpan: ' + error.message);
    } finally {
        btnFinish.disabled = false;
        btnFinish.textContent = "Selesai";
    }
});

        // Initialize
        initMap();
    </script>
</body>
</html>