<?php
// Veritabanı bağlantısı
$host = 'db4free.net';
$dbname = 'yunusenrefreehas';
$username = 'yunusemre';
$password = 'yuynusem5556@RmysqlU5eoothashcrackers9090555@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanına bağlanırken hata oluştu: " . $e->getMessage());
}

$hash_value = '';  // Başlangıçta boş değer
$hash_type_name = '';  // Hash türü adı

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input_value']) && isset($_POST['hash_type'])) {
    $input_value = trim($_POST['input_value']);
    $hash_type = $_POST['hash_type'];

    // Hash türüne göre işlemi yapalım
    switch ($hash_type) {
        case 1: 
            $hash_value = md5($input_value); 
            $hash_type_name = "MD5";
            break;
        case 2: 
            $hash_value = sha1($input_value); 
            $hash_type_name = "SHA1";
            break;
        case 3: 
            $hash_value = hash('sha256', $input_value); 
            $hash_type_name = "SHA256";
            break;
        case 4: 
            $hash_value = hash('md4', $input_value); 
            $hash_type_name = "NTLM";
            break;
        case 5: 
            $hash_value = password_hash($input_value, PASSWORD_BCRYPT); 
            $hash_type_name = "bcrypt";
            break;
        default: 
            $hash_value = 'Geçersiz Hash Türü'; 
            $hash_type_name = 'Geçersiz Hash Türü';
            break;
    }

    // Veritabanına kaydedelim
    try {
        // Tabloya veri ekleme işlemi
        $stmt = $pdo->prepare("INSERT INTO hashdata (hash_value, original_value, hash_type) VALUES (?, ?, ?)");
        $stmt->execute([$hash_value, $input_value, $hash_type_name]);
    } catch (PDOException $e) {
        // Hata mesajı
        echo "<div style='color: red;'>Veritabanı hatası: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hash Oluşturucu</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 15px;
        }

        form {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #2a73cc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1a5fa5;
        }

        .result {
            background-color: #444;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>🔐 Hash Oluşturucu</h1>
    <form method="post">
        <select name="hash_type">
            <option value="1">MD5</option>
            <option value="2">SHA1</option>
            <option value="3">SHA256</option>
            <option value="4">NTLM</option>
            <option value="5">bcrypt</option>
        </select>
        <input type="text" name="input_value" placeholder="Hash'lemek için bir değer girin" required>
        <button type="submit">Hash Oluştur</button>
    </form>

    <?php if ($hash_value): ?>
        <div class="result">
            <strong>Hash Sonucu (<?= htmlspecialchars($hash_type_name) ?>):</strong><br>
            <?= htmlspecialchars($hash_value) ?>
        </div>
    <?php endif; ?>
</body>
</html>
