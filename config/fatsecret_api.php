<?php
// ====== FATSECRET CONFIG ======
$client_id = "c17ed02e24714e25b343583cc4061127";   // ganti dengan client ID kamu
$client_secret = "1eed5c4f84d94febb627d8d23d908b55"; // ganti dengan client secret kamu

// Ambil Access Token
function getAccessToken($client_id, $client_secret) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://oauth.fatsecret.com/connect/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$client_id:$client_secret");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&scope=basic");

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL Error (Token): " . curl_error($ch));
    }

    curl_close($ch);
    $data = json_decode($response, true);

    if (!$data || !isset($data['access_token'])) {
        if (isset($data['error'])) {
            die("Error {$data['error']['code']}: {$data['error']['message']}. 
            Solusi: pastikan IP server sudah di-whitelist di FatSecret Developer Console.");
        }
        die("Gagal mendapatkan access_token. Response mentah: " . $response);
    }

    return $data['access_token'];
}

// Search Food
function searchFood($access_token, $query) {
    $url = "https://platform.fatsecret.com/rest/server.api?method=foods.search&search_expression=" . urlencode($query) . "&format=json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL Error (Search): " . curl_error($ch));
    }

    curl_close($ch);
    $data = json_decode($response, true);

    if (!$data) {
        die("Gagal decode JSON. Response mentah: " . $response);
    }

    // Jika ada error dari API, tampilkan pesan jelas
    if (isset($data['error'])) {
        die("Error {$data['error']['code']}: {$data['error']['message']}. 
        Solusi: pastikan IP server sudah di-whitelist di FatSecret Developer Console.");
    }

    return $data;
}
