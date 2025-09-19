<?php
// Configuração do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'user');
define('DB_PASS', 'senha');
define('DB_NAME', 'monitor_dashboard');

// Configurações gerais
define('SITE_NAME', 'Dashboard Monitor');
define('CHECK_TIMEOUT', 5); // Timeout em segundos para verificações
define('AUTO_REFRESH', 30); // Atualização automática em segundos

// Função de conexão com o banco
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Conexão falhou: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Função para verificar status de IP/Porta
function checkService($host, $port = 80, $timeout = CHECK_TIMEOUT) {
    $startTime = microtime(true);

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000); // em ms

    if ($fp) {
        fclose($fp);
        return ['status' => 'online', 'response_time' => $responseTime];
    } else {
        return ['status' => 'offline', 'response_time' => 0, 'error' => $errstr];
    }
}

// Função para verificar domínio
function checkDomain($domain, $port = 443) {
    // Remove protocolo se houver
    $domain = str_replace(['http://', 'https://'], '', $domain);

    // Verifica conectividade
    $result = checkService($domain, $port);

    // Verifica SSL se for HTTPS
    if ($port == 443 && $result['status'] == 'online') {
        $certInfo = getSslInfo($domain);
        if ($certInfo) {
            $result['ssl_expiry'] = $certInfo['validTo'];
        }
    }

    return $result;
}

// Função para obter informações SSL
function getSslInfo($domain) {
    $context = stream_context_create(["ssl" => ["capture_peer_cert" => TRUE]]);
    $stream = @stream_socket_client("ssl://".$domain.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if (!$stream) {
        return false;
    }

    $params = stream_context_get_params($stream);
    $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

    return [
        'validFrom' => date('Y-m-d', $cert['validFrom_time_t']),
        'validTo' => date('Y-m-d', $cert['validTo_time_t'])
    ];
}

// Função para formatar tempo de resposta
function formatResponseTime($ms) {
    if ($ms < 1000) {
        return $ms . ' ms';
    } else {
        return round($ms / 1000, 2) . ' s';
    }
}

// Função para calcular uptime
function calculateUptime($serviceId, $serviceType, $hours = 24) {
    $conn = getConnection();
    $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

    $sql = "SELECT 
            COUNT(*) as total_checks,
            SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_checks
            FROM check_history 
            WHERE service_id = ? 
            AND service_type = ? 
            AND checked_at >= ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $serviceId, $serviceType, $since);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data['total_checks'] > 0) {
        return round(($data['online_checks'] / $data['total_checks']) * 100, 2);
    }

    return 0;
}
?>
