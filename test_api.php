<?php

// Test script to create quiz
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzgwOTEzMjE5LCJleHAiOjE3ODA5MTY4MTksIm5iZiI6MTc4MDkxMzIxOSwianRpIjoiWm52ZkRPd0ZoZFFPTkVRSyIsInN1YiI6IjUiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.gOkoOVciEx2HTDuhFZL-AckP24FshpLsdp3EGsAen98';

// Create quiz
$quizData = [
    'judul' => 'Kuis Matematika Dasar',
    'deskripsi' => 'Kuis untuk kelas 1',
    'kategori' => 'Matematika',
    'soal_waktu' => 60,
    'perm_istirahat' => 300,
    'akses' => 'publik'
];

$ch = curl_init('http://127.0.0.1:8000/api/kuis');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($quizData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$quizResponse = json_decode($response, true);
if ($quizResponse['data']['kuis_id'] ?? null) {
    $kuisId = $quizResponse['data']['kuis_id'];
    echo "\n✓ Quiz berhasil dibuat dengan ID: " . $kuisId . "\n\n";
    
    // Create questions
    $questions = [
        [
            'kuis_id' => $kuisId,
            'soal_soal' => 'Berapa hasil dari 2+2?',
            'tipe_soal' => 'pilihan_ganda',
            'poin' => 10,
            'jawaban_a' => '2',
            'jawaban_b' => '4',
            'jawaban_c' => '6',
            'jawaban_d' => '8',
            'jawaban_benar' => 'b'
        ],
        [
            'kuis_id' => $kuisId,
            'soal_soal' => 'Berapa hasil dari 3+3?',
            'tipe_soal' => 'pilihan_ganda',
            'poin' => 10,
            'jawaban_a' => '5',
            'jawaban_b' => '6',
            'jawaban_c' => '7',
            'jawaban_d' => '8',
            'jawaban_benar' => 'b'
        ]
    ];
    
    foreach ($questions as $idx => $questionData) {
        $ch = curl_init('http://127.0.0.1:8000/api/soal');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($questionData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $qResponse = curl_exec($ch);
        $qHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Soal " . ($idx + 1) . " - HTTP Code: " . $qHttpCode . "\n";
        if ($qHttpCode == 201) {
            echo "✓ Soal " . ($idx + 1) . " berhasil ditambahkan\n";
        } else {
            echo "Response: " . $qResponse . "\n";
        }
    }
    
    // Get quiz detail
    echo "\n\nMengambil detail kuis...\n";
    $ch = curl_init('http://127.0.0.1:8000/api/kuis/' . $kuisId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    
    $detailResponse = curl_exec($ch);
    $detailHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $detailHttpCode . "\n";
    $detail = json_decode($detailResponse, true);
    echo "Judul: " . $detail['data']['judul'] . "\n";
    echo "Jumlah Soal: " . $detail['data']['jumlah_soal'] . "\n";
    echo "Total Poin: " . $detail['data']['total_poin'] . "\n";
    echo "Status: " . $detail['data']['status'] . "\n";
    echo "Kode Kuis: " . $detail['data']['kode_kuis'] . "\n";
}
