# Testes e checklist de segurança — SafeRoute

## Confirmado na revisão de código

- Senhas cadastradas com `password_hash` e login com `password_verify`.
- Consultas com entrada de usuários usam instruções preparadas/PDO nos fluxos principais.
- Páginas administrativas têm validação de sessão/perfil.
- Tokens CSRF inseridos nas páginas que recebem formulários POST.
- O arquivo versionado `config/database.php` lê `.env` e não contém credenciais reais.
- `.env` consta no `.gitignore`; `.htaccess` bloqueia arquivos e pastas sensíveis em Apache compatível.

## Testes que ainda devem ser executados e registrados

- [ ] Clonar o repositório em outro computador, importar `sql/install.sql` e conectar usando `.env`.
- [ ] Cadastro → login → criação de ocorrência → aprovação admin → mapa público.
- [ ] Perfil normal não consegue entrar nas telas `pages/admin/*`.
- [ ] Senha armazenada no banco é hash, não texto.
- [ ] Tentativas POST sem CSRF devolvem erro 403.
- [ ] Busca por local e rotas externas carregam em navegador com internet.
- [ ] Paradas de ônibus ausentes ou erro externo são tratados sem afirmar dados falsos.
- [ ] Link da hospedagem funciona em HTTPS e aba anônima.
- [ ] Tentativas de visualizar `.env`, SQL, `config/` e `includes/` retornam acesso negado.
- [ ] Rever limitação de tentativas de login, tamanho de payloads, validações e risco de spam antes de disponibilizar ao público.

**Limitações:** ainda não há testes funcionais de banco ou hospedagem comprovados nesta atualização; integrações de ônibus podem ficar vazias. O indicador de risco é demonstrativo, não recomendação de segurança real.
