<?php
/**
 * SafeRoute — Funções de Usuário
 * 
 * Responsável pelas operações de busca, validação e persistência
 * de usuários na tabela `usuario` usando PDO e prepared statements.
 */

/**
 * Busca um usuário pelo endereço de email.
 * Utilizado primariamente durante o processo de autenticação.
 *
 * @param PDO    $pdo   Conexão PDO ativa
 * @param string $email Endereço de email do usuário
 * @return array|null Dados do usuário ou null se não encontrado
 */
function buscarUsuarioPorEmail(PDO $pdo, string $email): ?array
{
    $sql = 'SELECT id_usuario, nome, email, senha, tipo_usuario, data_cadastro 
            FROM usuario 
            WHERE email = :email 
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', trim($email), PDO::PARAM_STR);
    $stmt->execute();

    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

/**
 * Busca dados públicos de um usuário pelo ID.
 * Por segurança, a senha é omitida da consulta.
 *
 * @param PDO $pdo Conexão PDO ativa
 * @param int $id  ID do usuário
 * @return array|null Dados do usuário ou null se não encontrado
 */
function buscarUsuarioPorId(PDO $pdo, int $id): ?array
{
    $sql = 'SELECT id_usuario, nome, email, tipo_usuario, data_cadastro 
            FROM usuario 
            WHERE id_usuario = :id 
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

/**
 * Verifica se já existe um usuário cadastrado com determinado email.
 *
 * @param PDO    $pdo   Conexão PDO ativa
 * @param string $email Endereço de email a verificar
 * @return bool True se já existir, false caso contrário
 */
function emailExiste(PDO $pdo, string $email): bool
{
    $sql = 'SELECT 1 FROM usuario WHERE email = :email LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', trim($email), PDO::PARAM_STR);
    $stmt->execute();

    return (bool) $stmt->fetchColumn();
}

/**
 * Cadastra um novo usuário no sistema.
 * 
 * - Valida os campos obrigatórios
 * - Garante que o email é único
 * - Gera o hash seguro da senha com PASSWORD_DEFAULT (bcrypt)
 * - Define o tipo_usuario padrão como 'comum'
 *
 * @param PDO    $pdo   Conexão PDO ativa
 * @param string $nome  Nome completo do usuário
 * @param string $email Endereço de email
 * @param string $senha Senha em texto puro (será hasheada)
 * @return array Array com chave 'sucesso' (bool) e 'mensagem' (string)
 */
function cadastrarUsuario(PDO $pdo, string $nome, string $email, string $senha): array
{
    $nome = trim($nome);
    $email = trim($email);

    // 1. Validação de campos obrigatórios
    if (empty($nome) || empty($email) || empty($senha)) {
        return [
            'sucesso'  => false,
            'mensagem' => 'Todos os campos são obrigatórios.',
        ];
    }

    // 2. Validação do comprimento do nome
    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) {
        return [
            'sucesso'  => false,
            'mensagem' => 'O nome deve ter entre 2 e 100 caracteres.',
        ];
    }

    // 3. Validação do formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        return [
            'sucesso'  => false,
            'mensagem' => 'Por favor, informe um endereço de email válido.',
        ];
    }

    // 4. Validação do comprimento da senha (mínimo 6 caracteres)
    if (strlen($senha) < 6) {
        return [
            'sucesso'  => false,
            'mensagem' => 'A senha deve conter no mínimo 6 caracteres.',
        ];
    }

    // 5. Verificar duplicidade de email
    if (emailExiste($pdo, $email)) {
        return [
            'sucesso'  => false,
            'mensagem' => 'Já existe uma conta cadastrada com este email.',
        ];
    }

    // 6. Gerar hash seguro da senha (bcrypt via PASSWORD_DEFAULT)
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    if ($senhaHash === false) {
        return [
            'sucesso'  => false,
            'mensagem' => 'Erro interno ao processar credenciais de segurança.',
        ];
    }

    // 7. Inserir no banco de dados com prepared statement
    $sql = 'INSERT INTO usuario (nome, email, senha, tipo_usuario, data_cadastro) 
            VALUES (:nome, :email, :senha, :tipo_usuario, NOW())';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':senha', $senhaHash, PDO::PARAM_STR);
    $stmt->bindValue(':tipo_usuario', 'comum', PDO::PARAM_STR);

    try {
        $stmt->execute();
        return [
            'sucesso'  => true,
            'mensagem' => 'Cadastro realizado com sucesso! Você já pode entrar no sistema.',
        ];
    } catch (PDOException $e) {
        // Não expor detalhes do banco para o usuário
        return [
            'sucesso'  => false,
            'mensagem' => 'Ocorreu um erro ao realizar o cadastro. Tente novamente mais tarde.',
        ];
    }
}
