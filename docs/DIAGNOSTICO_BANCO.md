# Diagnóstico do banco (Etapa 1A)

Objetivo: descobrir como o seu banco **realmente** está hoje, antes de qualquer
migração. O arquivo `sql/diagnostico_banco.sql` só contém comandos `SELECT`
(16 no total): não cria, altera nem apaga nada.

## Passo a passo no XAMPP

1. Abra o **XAMPP Control Panel** e confirme que **Apache** e **MySQL** estão ligados.
2. Abra `http://localhost/phpmyadmin/`.
3. Na coluna da esquerda, **clique no banco do SafeRoute** (normalmente `saferoute`).
4. Clique na aba **SQL**.
5. Abra o arquivo `sql/diagnostico_banco.sql` em um editor de texto, copie **a PARTE A inteira**
   (da linha `-- PARTE A` até antes de `-- PARTE B`), cole na caixa e clique em **Executar**.
6. Repita com a **PARTE B** (até a linha `-- FIM DO DIAGNÓSTICO`).
7. Envie os resultados na conversa. Opções, da mais fácil à mais completa:
   - tirar um print de cada tabela de resultado;
   - clicar em **Exportar** abaixo do resultado, escolher **CSV** e anexar;
   - copiar a tabela (selecionar + Ctrl+C) e colar.

Dicas:
- Se aparecer só **um** resultado, execute os comandos **um por vez** (cada um termina em `;`).
- Na **Parte B**, um erro como `Table 'saferoute.usuario' doesn't exist` ou `Unknown column`
  **não é falha sua**: é uma informação. Copie a mensagem e siga para o comando seguinte.
- Se `banco_atual` (resultado A1) vier vazio, você esqueceu de clicar no banco (passo 3).

## O que NÃO aparece nos resultados
Nomes, e-mails, senhas e hashes **não** são mostrados: só contagens e a estrutura das tabelas.
Os resultados podem ser compartilhados sem expor dados pessoais.

## O que cada comando responde

| Comando | Pergunta respondida | Esperado (se o banco seguiu o schema.sql) |
|---|---|---|
| A1 | Qual servidor (MySQL ou MariaDB), versão, charset e banco atual? | XAMPP costuma ser MariaDB |
| A2, A3 | Existem outros bancos ou tabelas fora do lugar? | Só o banco do SafeRoute |
| A4 | As 4 tabelas da SPEC existem? Existe uma tabela `usuarios`? | 4 e 0 |
| A5–A8 | Tabelas, colunas, índices e chaves estrangeiras reais | Iguais ao `schema.sql` |
| A9 | Há views, triggers ou rotinas? | 0, 0, 0 |
| B1 | Quantas linhas em cada tabela? | Qualquer valor |
| B2 | Quantos admins? Alguma senha fora do formato de hash? | `senhas_curtas_suspeitas` = 0 |
| B3 | E-mails duplicados ignorando maiúsculas/espaços? | 0 |
| B4 | Quais tipos de ocorrência existem? | Furto, Roubo, Vandalismo, Agressão, Outro |
| B5 | Coordenadas inválidas ou vínculos quebrados? | 0 em todos os problemas |
| B6 | Ocorrências por status e fonte | Qualquer valor |
| B7 | Rotas incompletas ou sem usuário | Qualquer valor |

## Próximo passo
Com os resultados, eu escrevo a migração (arquivo SQL separado, com rollback e
sem apagar dados) para você revisar **antes** de qualquer alteração no banco.
