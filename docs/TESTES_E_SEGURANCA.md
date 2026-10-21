# Testes e checklist de segurança — SafeRoute

## Confirmado na revisão de código

- Senhas cadastradas com `password_hash` e login com `password_verify`.
- Consultas com entrada de usuários usam instruções preparadas/PDO nos fluxos principais.
- Páginas administrativas têm validação de sessão/perfil.
- Tokens CSRF inseridos nas páginas que recebem formulários POST.
- O arquivo versionado `config/database.php` lê `.env` e não contém credenciais reais.
- `.env` consta no `.gitignore`; `.htaccess` bloqueia arquivos e pastas sensíveis em Apache compatível.

## Resultados funcionais relatados pelo responsável — 20/10/2026

O estudante executou testes no domínio `https://saferoute.gt.tc/` e informou **8 de 8 resultados OK**. Estes resultados foram comunicados após o uso do site publicado; **não** equivalem a uma auditoria independente. O procedimento de teste é descrito abaixo.

| # | Teste realizado | Resultado informado |
| --- | --- | --- |
| 1 | Página inicial, aparência e navegação sem 404/500 | OK |
| 2 | Cadastro de usuário com gravação no MySQL | OK |
| 3 | Login, perfil, senha errada recusada e encerramento de sessão | OK |
| 4 | Registro de ocorrência com status pendente | OK |
| 5 | Aprovação de ocorrência pelo administrador e restrição do acesso comum | OK |
| 6 | Ocorrência aprovada no mapa e na contagem de estatísticas | OK |
| 7 | Planejamento de rotas e tratamento de falhas de serviços externos/ônibus | OK, conforme relato |
| 8 | Proteção contra acesso público a arquivos privados | OK, conforme relato |

**Atualização — confirmação do estudante (21/10/2026):** a busca de endereços na nova tela de ocorrências foi testada na hospedagem e funcionou, segundo o responsável. Resultado informado, não verificado independentemente nesta documentação.

## Verificações adicionais antes da entrega definitiva

- [ ] Clonar o repositório em **outro computador**, importar `sql/install.sql` e configurar `.env` seguindo apenas a SPEC.
- [ ] Confirmar no banco que senhas foram armazenadas como hashes, não texto.
- [ ] Conferir se POST sem token CSRF é rejeitado com HTTP 403 em diferentes formulários.
- [ ] Confirmar em aba anônima o funcionamento de todas as rotas principais e ausência de login salvo.
- [x] Testar a busca de endereço na tela de ocorrências atualizada em produção (OK informado pelo estudante).
- [ ] Verificar separadamente, caso ainda não tenha sido feito, clique no mapa, arraste do marcador e localização atual.
- [ ] Verificar individualmente que `/.env`, `/config/database.php`, `/includes/auth.php` e `/sql/hosting.sql` não exibem conteúdo privado.
- [ ] Registrar e revisar limitações da hospedagem e das APIs externas; não tratar estimativas escolares como avaliações reais de segurança.
- [ ] Conferir convite de colaboração do professor `rafaelpitwak` e preservar histórico de commits genuíno.

**Limitações conhecidas:** dados de ônibus do OpenStreetMap podem estar incompletos e serviços externos podem ficar indisponíveis. O indicador de risco é experimental/didático e não garante segurança real.
