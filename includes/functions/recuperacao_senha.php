<?php
declare(strict_types=1);
/** SafeRoute: recuperação de senha por código, sem segredos no repositório. */
require_once __DIR__ . '/../../config/database.php';

function srResetConfigured(): bool
{
    return strlen(safeRouteConfig('RESET_CODE_SECRET')) >= 32
        && filter_var(safeRouteConfig('SMTP_FROM_EMAIL'), FILTER_VALIDATE_EMAIL)
        && safeRouteConfig('SMTP_HOST') !== ''
        && safeRouteConfig('SMTP_USER') !== ''
        && safeRouteConfig('SMTP_PASS') !== '';
}

/** Usa o serviço SMTP externo; o mail() do PHP não funciona de forma confiável no InfinityFree. */
function srSendResetEmail(string $destination, string $code): void
{
    if (!srResetConfigured()) {
        throw new RuntimeException('Servidor de envio não configurado.');
    }
    require_once __DIR__ . '/../../vendor/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../../vendor/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/PHPMailer/src/SMTP.php';
    $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = safeRouteConfig('SMTP_HOST');
    $mailer->Port = 587; // InfinityFree: porta permitida com TLS/STARTTLS
    $mailer->SMTPAuth = true;
    $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Username = safeRouteConfig('SMTP_USER');
    $mailer->Password = safeRouteConfig('SMTP_PASS');
    $mailer->Timeout = 15;
    $mailer->CharSet = 'UTF-8';
    $mailer->setFrom(safeRouteConfig('SMTP_FROM_EMAIL'), safeRouteConfig('SMTP_FROM_NAME', 'SafeRoute'));
    $mailer->addAddress($destination);
    $mailer->isHTML(false);
    $mailer->Subject = 'SafeRoute - código para redefinir sua senha';
    $mailer->Body = "Seu código temporário do SafeRoute é: {$code}\n\n"
        . "Válido por 10 minutos. Você tem até 5 tentativas.\n\n"
        . "Use o código em https://saferoute.gt.tc/pages/redefinir_senha.php\n\n"
        . "Se não pediu esta recuperação, ignore o e-mail. Nunca compartilhe este código.";
    $mailer->send();
}

function srResetKey(string $email, string $code): string
{
    return hash_hmac('sha256', strtolower(trim($email)) . ':' . $code, safeRouteConfig('RESET_CODE_SECRET'));
}

/** Limite global por IP sem armazenar endereço IP em texto puro. */
function srResetRateLimit(PDO $pdo): bool
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $ipHash = hash_hmac('sha256', $ip, safeRouteConfig('RESET_CODE_SECRET'));
    $sql = 'INSERT INTO recuperacao_limite_ip (ip_hash, janela_inicio, quantidade)
            VALUES (?, NOW(), 1)
            ON DUPLICATE KEY UPDATE
              quantidade = IF(janela_inicio < NOW() - INTERVAL 1 HOUR, 1, quantidade + 1),
              janela_inicio = IF(janela_inicio < NOW() - INTERVAL 1 HOUR, NOW(), janela_inicio)';
    $pdo->prepare($sql)->execute([$ipHash]);
    $st = $pdo->prepare('SELECT quantidade FROM recuperacao_limite_ip WHERE ip_hash=?');
    $st->execute([$ipHash]);
    if (random_int(1, 100) === 1) {
        $pdo->exec('DELETE FROM recuperacao_limite_ip WHERE janela_inicio < NOW() - INTERVAL 2 DAY');
    }
    return (int)$st->fetchColumn() <= 20;
}

/** Resposta genérica evita revelar se o endereço está cadastrado. */
function srRequestResetCode(string $email): void
{
    if (!srResetConfigured()) {
        error_log('[SafeRoute] Recuperação não configurada: confira RESET_CODE_SECRET e SMTP_* no .env.');
        return;
    }
    $pdo = getConnection();
    if (!srResetRateLimit($pdo)) return;
    $st = $pdo->prepare('SELECT id_usuario, email FROM usuario WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user) return;
    $id = (int)$user['id_usuario'];
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $key = srResetKey((string)$user['email'], $code);
    $created = false;
    try {
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT envios_na_janela,
                               TIMESTAMPDIFF(SECOND, ultima_solicitacao, NOW()) AS desde_ultimo,
                               TIMESTAMPDIFF(SECOND, janela_inicio, NOW()) AS desde_janela
                               FROM recuperacao_senha WHERE id_usuario=? FOR UPDATE');
        $lock->execute([$id]);
        $prior = $lock->fetch(PDO::FETCH_ASSOC);
        if ($prior) {
            $sinceLast = (int)$prior['desde_ultimo'];
            $sinceWindow = (int)$prior['desde_janela'];
            if ($sinceLast < 120 || ($sinceWindow < 3600 && (int)$prior['envios_na_janela'] >= 3)) {
                $pdo->rollBack();
                return;
            }
            $count = $sinceWindow >= 3600 ? 1 : ((int)$prior['envios_na_janela'] + 1);
            $sql = 'UPDATE recuperacao_senha SET codigo_hash=?, expira_em=NOW() + INTERVAL 10 MINUTE,
                    tentativas=0, ultima_solicitacao=NOW(),
                    janela_inicio=IF(janela_inicio < NOW() - INTERVAL 1 HOUR, NOW(), janela_inicio),
                    envios_na_janela=? WHERE id_usuario=?';
            $pdo->prepare($sql)->execute([$key,$count,$id]);
        } else {
            $sql = 'INSERT INTO recuperacao_senha
                    (id_usuario, codigo_hash, expira_em, tentativas, ultima_solicitacao, janela_inicio, envios_na_janela)
                    VALUES (?, ?, NOW() + INTERVAL 10 MINUTE, 0, NOW(), NOW(), 1)';
            $pdo->prepare($sql)->execute([$id,$key]);
        }
        $pdo->commit();
        $created = true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[SafeRoute] Erro ao gerar código de recuperação: ' . $exception->getMessage());
        return;
    }
    if (!$created) return;
    try {
        srSendResetEmail((string)$user['email'], $code);
    } catch (Throwable $exception) {
        error_log('[SafeRoute] SMTP falhou ao entregar recuperação: ' . $exception->getMessage());
        // Permitir nova tentativa se o email não pôde ser enviado, sem expor a informação ao visitante.
        $pdo->prepare('DELETE FROM recuperacao_senha WHERE id_usuario=? AND codigo_hash=?')->execute([$id,$key]);
    }
}

/** Redefine apenas após validar e consumir o código. Nunca devolve o código ao navegador. */
function srChangePasswordWithCode(string $email, string $code, string $newPassword): bool
{
    if (!srResetConfigured() || !preg_match('/^[0-9]{6}$/D', $code)
        || strlen($newPassword) < 10 || strlen($newPassword) > 72) return false;
    $pdo = getConnection();
    try {
        $pdo->beginTransaction();
        $sql = 'SELECT u.id_usuario, u.email, r.codigo_hash,
                (r.expira_em <= NOW()) AS expirado, r.tentativas
                FROM usuario u JOIN recuperacao_senha r ON r.id_usuario=u.id_usuario
                WHERE u.email=? LIMIT 1 FOR UPDATE';
        $st = $pdo->prepare($sql);
        $st->execute([$email]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $pdo->rollBack();
            return false;
        }
        $id = (int)$row['id_usuario'];
        $expired = (int)$row['expirado'] === 1;
        $locked = (int)$row['tentativas'] >= 5;
        if ($expired || $locked) {
            $pdo->prepare('DELETE FROM recuperacao_senha WHERE id_usuario=?')->execute([$id]);
            $pdo->commit();
            return false;
        }
        $expected = srResetKey((string)$row['email'], $code);
        if (!hash_equals((string)$row['codigo_hash'], $expected)) {
            $pdo->prepare('UPDATE recuperacao_senha SET tentativas=tentativas+1 WHERE id_usuario=?')->execute([$id]);
            $pdo->commit();
            return false;
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE usuario SET senha=? WHERE id_usuario=?')->execute([$hash,$id]);
        $pdo->prepare('DELETE FROM recuperacao_senha WHERE id_usuario=?')->execute([$id]);
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[SafeRoute] Falha ao redefinir senha: ' . $exception->getMessage());
        return false;
    }
}
