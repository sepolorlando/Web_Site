<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // troque se usar outra porta no XAMPP
define('DB_NAME', 'mercearia_online');
define('DB_USER', 'root');
define('DB_PASS', '');


try {
    // Cria a conexão PDO
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Registra o erro em log
    error_log("Erro de conexão: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
