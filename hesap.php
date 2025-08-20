<?php
// Veritabanı bağlantı bilgileri
$host = 'db4free.net'; // Veritabanı sunucusu
$dbname = 'yunusenrefreehas'; // Veritabanı adı
$username = 'yunusemre'; // Veritabanı kullanıcı adı
$password = 'yuynusem5556@RmysqlU5eoothashcrackers9090555@'; // Veritabanı şifresi

// PDO ile veritabanı bağlantısı oluşturuluyor
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    exit;
}

// Hesapları listelemek için fonksiyon
function getRandomAccount() {
    global $pdo;

    // Veritabanından hesapları çekiyoruz
    $sql = "SELECT * FROM accounts WHERE last_used IS NULL OR TIMESTAMPDIFF(MINUTE, last_used, NOW()) >= 1 ORDER BY RAND() LIMIT 1";
    $stmt = $pdo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Hesap kullanım kaydını güncellemek için fonksiyon
function logAccountUsage($account_id) {
    global $pdo;

    $sql = "UPDATE accounts SET last_used = NOW() WHERE id = :account_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':account_id' => $account_id]);
}

// Hesap alındığında yapılacak işlemler
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['get_account'])) {
        $account = getRandomAccount(); // Rastgele bir hesap al

        if (!$account) {
            // Eğer hesap yoksa
            $error_message = "Şu anda hesap yok, lütfen daha sonra tekrar deneyin.";
        } else {
            logAccountUsage($account['id']); // Hesap kullanımını güncelle
            $selected_account = $account; // Seçilen hesabı göster
        }

        // Eğer tüm hesaplar kullanılıyorsa
        if (!$selected_account) {
            $sql = "SELECT COUNT(*) FROM accounts WHERE (last_used IS NOT NULL AND TIMESTAMPDIFF(MINUTE, last_used, NOW()) < 1)";
            $stmt = $pdo->query($sql);
            $used_count = $stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM accounts";
            $stmt = $pdo->query($sql);
            $total_count = $stmt->fetchColumn();

            if ($used_count == $total_count && $total_count > 0) {
                $error_message = "Tüm hesaplar kullanımda, daha sonra tekrar deneyin veya admin ile iletişime geçin. <a href='https://t.me/freehashcracker' target='_blank'>Admin ile iletişime geçin</a>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Yönetimi</title>
    <style>
        body {
            background-image: url('https://backiee.com/static/wallpapers/1000x563/416986.jpg');
            background-size: cover;
            color: white;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding-top: 50px;
            text-align: center;
        }

        h1, h2 {
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .account-info {
            margin-top: 30px;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
        }

        .error-message {
            color: red;
            margin-top: 20px;
        }

        a {
            color: #00bfff;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Hesap Yönetimi Sistemi</h1>

    <!-- Hesap Al Butonu -->
    <form method="POST">
        <button type="submit" name="get_account">Hesap Al</button>
    </form>

    <!-- Eğer bir hesap seçildiyse bilgileri göster -->
    <?php if (isset($selected_account)): ?>
        <div class="account-info">
            <h2>Seçilen Hesap</h2>
            <p><strong>Platform:</strong> <?= $selected_account['platform'] ?></p>
            <p><strong>Username:</strong> <?= $selected_account['username'] ?></p>
            <p><strong>Password:</strong> <?= $selected_account['password'] ?></p>
            <p><strong>Tarih:</strong> <?= $selected_account['date_assigned'] ?></p>
        </div>
    <?php elseif (isset($error_message)): ?>
        <div class="error-message"><?= $error_message ?></div>
    <?php endif; ?>
</div>

</body>
</html>
