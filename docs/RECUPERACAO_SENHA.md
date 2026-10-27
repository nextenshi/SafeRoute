# SafeRoute — Recuperação de senha por código

## Atualizar a hospedagem (InfinityFree)

1. Faça backup do MySQL e selecione o banco **já existente** do SafeRoute no phpMyAdmin.
2. Importe **somente** sql/migracao_recuperacao_senha.sql. O script cria duas tabelas adicionais sem apagar contas, ocorrências e rotas.
3. Envie os arquivos atualizados para o htdocs preservando a organização de pastas: pages/login.php, pages/esqueci_senha.php, pages/redefinir_senha.php, includes/functions/recuperacao_senha.php, vendor/PHPMailer e o .htaccess da raiz. Não sobrescreva o .env privado nem reimporte o instalador completo.
4. Acrescente no .env privado do servidor:

    SMTP_HOST=smtp.gmail.com
    SMTP_USER=remetente@gmail.com
    SMTP_PASS=SENHA_DE_APP_DO_REMETENTE
    SMTP_FROM_EMAIL=remetente@gmail.com
    SMTP_FROM_NAME=SafeRoute
    RESET_CODE_SECRET=SEGREDO_ALEATORIO_DE_PELO_MENOS_32_CARACTERES

5. O remetente de SMTP_USER não precisa ser igual ao e-mail cadastrado do usuário. Para envio pelo Gmail, a conta remetente deve ter verificação em duas etapas e, se elegível, uma senha de app criada em https://myaccount.google.com/apppasswords . Nunca utilize a senha normal do Gmail no .env.
6. Para gerar RESET_CODE_SECRET no próprio computador: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;". Nunca envie tokens ou senhas pelo chat, por e-mail público ou ao GitHub.
7. Confirme que https://saferoute.gt.tc/.env e https://saferoute.gt.tc/vendor/PHPMailer/src/SMTP.php não exibem conteúdo (403 ou 404).
8. Abra https://saferoute.gt.tc/pages/esqueci_senha.php e solicite o código para o e-mail já cadastrado; procure na caixa de entrada e no spam. Use o código em https://saferoute.gt.tc/pages/redefinir_senha.php e teste login com a nova senha.

## Proteções

- PIN de 6 dígitos aleatórios, válido 10 minutos, uso único, 5 tentativas.
- Salvo como HMAC-SHA256 com segredo exclusivo do servidor, sem expor código em banco/logs.
- Limites de 1 envio a cada 2 minutos, máximo 3 por conta por hora e 20 pedidos por IP por hora; IP salvo como HMAC.
- Formulários com CSRF, mensagens genéricas para não revelar contas cadastradas e senha final gravada com password_hash.
- PHPMailer usa SMTP 587/STARTTLS e é licenciado LGPL-2.1, com licença incluída em vendor/PHPMailer/LICENSE.

## Limitações

O InfinityFree restringe o envio direto por PHP mail(); é necessário SMTP externo. Sem configuração válida o formulário não enviará código. A entrega de e-mails ainda precisa ser testada na hospedagem e pode ser afetada por spam. A redefinição não encerra automaticamente sessões antigas em outros dispositivos.

Para recuperar uma conta própria antes de ativar SMTP, o dono legítimo do banco pode gerar um novo hash com password_hash e atualizá-lo **somente na sua conta** pelo phpMyAdmin, sem divulgar a nova senha.

Fontes: https://forum.infinityfree.com/t/emails-from-my-website-arent-working/49242 ; https://support.google.com/mail/answer/185833
