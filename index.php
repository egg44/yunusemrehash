<?php
session_start();

// DB bağlantı bilgilerini güvenli şekilde yönetin
$dsn = "mysql:host=db4free.net;dbname=yunusenrefreehas;charset=utf8mb4";
$db_user = "yunusemre";
$db_pass = "yuynusem5556@RmysqlU5eoothashcrackers9090555@";

$message = "";
$user_found = null;
$log_file = __DIR__ . '/logs/app.log';

// Log fonksiyonu
function app_log($msg) {
    global $log_file;
    if (!is_dir(dirname($log_file))) mkdir(dirname($log_file), 0755, true);
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

try {
    // PDO ile veritabanı bağlantısı
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    app_log("DB bağlantı hatası: " . $e->getMessage());
    die("DB bağlantı hatası: Lütfen daha sonra tekrar deneyiniz.");
}

// Hash tipleri
$hash_types = [1 => "MD5", 2 => "SHA1", 3 => "SHA256", 4 => "NTLM", 5 => "bcrypt"];

// Rate limit fonksiyonu
function check_rate_limit($pdo, $ip, $action, $max_requests, $seconds) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM request_logs WHERE ip_address=? AND action=? AND created_at > (NOW() - INTERVAL ? SECOND)");
    $stmt->execute([$ip, $action, $seconds]);
    return $stmt->fetchColumn() < $max_requests;
}

// Request loglama
function log_request($pdo, $ip, $email, $action) {
    $stmt = $pdo->prepare("INSERT INTO request_logs (ip_address, email, action) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $email, $action]);
}

// Kullanıcı bilgileri
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

// Sayfa kontrolü (Hakkında ve İletişim sayfaları)
$page = isset($_GET['page']) ? $_GET['page'] : null;
if ($page === 'about') {
    $message = "🚧 Bu sayfa bakımda. Lütfen daha sonra tekrar deneyin.";
} elseif ($page === 'contact') {
    $message = "📝 İletişim sayfasına baktınız.";
}

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // HASH kontrol
        if (isset($_POST['hash_value'], $_POST['hash_type'])) {
            $type_id = $_POST['hash_type'];
            $value = trim($_POST['hash_value']);
            if (!isset($hash_types[$type_id]) || !$value) {
                $message = "❗ Hash tipi ve değer girilmeli.";
            } elseif (!check_rate_limit($pdo, $ip, 'hash_check', 2, 60)) {
                $message = "⏳ Çok sık hash sorgusu yaptınız. 1 dakikada en fazla 2 sorgu.";
                app_log("Rate limit aşıldı: hash_check IP=$ip");
            } else {
                $t = $hash_types[$type_id];
                $stmt = $pdo->prepare("SELECT type, hash_value, cracked_value FROM user_hashes WHERE type=? AND hash_value=? LIMIT 1");
                $stmt->execute([$t, $value]);
                $user_found = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user_found) {
                    $cr = $user_found['cracked_value'] ?: 'Kırılmış bilgi yok';
                    $message = "🔓 Bulundu! Tip: {$user_found['type']} – Değer: <code>{$user_found['hash_value']}</code> – Sonuç: <strong>$cr</strong>";
                } else {
                    $message = "❗ Hash bulunamadı.";
                }
                log_request($pdo, $ip, null, 'hash_check');
                app_log("Hash sorgulandı: IP=$ip, Tip=$t");
            }
        }
    } catch (PDOException $e) {
        $message = "⚠ DB hatası, tekrar deneyin.";
        app_log("PDO Hata: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hash Kontrol</title>
    <style>
        body {
            background-image: url('https://backiee.com/static/wallpapers/1000x563/416986.jpg');
            background-size: cover;
            color: white;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h1, h2 {
            font-size: 32px;
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }

        input, select, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background-color: #2a73cc;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #1a5fa5;
        }

        .message {
            margin-top: 20px;
            padding: 15px;
            background-color: #444;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
        }

        nav {
            background-color: rgba(0, 0, 0, 0.7);
            width: 100%;
            padding: 10px 0;
            margin: 0;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
            margin: 0;
        }

        nav ul li {
            display: inline-block;
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
            border-radius: 5px;
        }

        nav ul li a:hover {
            background-color: #2a73cc;
        }

        .social-media a {
            margin: 0 10px;
        }

        .social-media img {
            width: 40px;
            height: 40px;
        }

        @media (max-width: 768px) {
            h1, h2 {
                font-size: 28px;
            }

            form {
                width: 90%;
            }

            .social-media img {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body>

<!-- Menü -->
<nav>
    <ul>
        <li><a href="/">Ana Sayfa</a></li>
       
            <ul>
                <li><a href="/hash_kirici.php">Hash Kırıcı</a></li>
                <li><a href="/hash_olusuturucu.php">Hash Oluşturucu</a></li>
            </ul>
        </li>
        <li><a href="/cookie/cookie.php">Cookie İşlemleri</a></li>
        <li><a href="?page=about">Hakkında</a></li>
        <li><a href="?page=contact">İletişim</a></li>
    </ul>
</nav>

<h1>🔐 Hash Kontrol</h1>

<!-- Hash kontrol formu -->
<form method="post">
    <select name="hash_type">
        <?php foreach ($hash_types as $i => $n) echo "<option value='$i'>$n</option>"; ?>
    </select>
    <input type="text" name="hash_value" placeholder="Hash değeri" required>
    <button type="submit">Kontrol Et</button>
</form>

<?php if ($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<!-- Sosyal medya bağlantıları -->
<h2>💬 Sosyal Medya</h2>
<p>Aşağıdaki sosyal medya hesaplarımızdan bize ulaşabilirsiniz:</p>
<div class="social-media">
    <a href="https://www.youtube.com/@offical_Yunus_Emre" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/e/ef/Youtube_logo.png" alt="YouTube" title="YouTube">
    </a>
    <a href="https://t.me/freehashcracker" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Telegram_2019_Logo.svg/2048px-Telegram_2019_Logo.svg.png" alt="Telegram" title="Telegram">
    </a>
    <a href="https://www.instagram.com/yunusemre.main" target="_blank">
        <img src="https://www.logo.wine/a/logo/Instagram/Instagram-Logo.wine.svg" alt="Instagram" title="Instagram">
    </a>
    <a href="mailto:resmi.yunus.emre.tr@proton.me" target="_blank">
        <img src="https://png.pngtree.com/png-vector/20190129/ourmid/pngtree-email-vector-icon-png-image_355828.jpg" alt="E-mail" title="E-mail">
    </a>
</div>

<script>
    document.querySelector('a[href="mailto:resmi.yunus.emre.tr@proton.me"]').addEventListener('click', function(event) {
        event.preventDefault();
        alert("E-posta adresimiz: resmi.yunus.emre.tr@proton.me");
        window.location.href = this.href;
    });
</script>

</body>
</html>
