<!-- Arquivo: db.php -->
<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'inwood_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Cria a conexão PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // echo "Conexão bem-sucedida!"; // Remova em produção
} catch (PDOException $e) {
    // Registra o erro em um arquivo de log em produção
    error_log("Erro de conexão: " . $e->getMessage());
    
    // Exibe mensagem amigável em ambiente de desenvolvimento
    die("Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
?>