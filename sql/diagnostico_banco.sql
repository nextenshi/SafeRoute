-- =====================================================================
-- SafeRoute — Diagnóstico do banco de dados (Etapa 1A)
-- =====================================================================
-- SOMENTE LEITURA.
--   * Contém apenas comandos SELECT.
--   * Não cria, altera nem apaga nada (nenhuma tabela, coluna ou linha).
--   * Não mostra nomes, e-mails nem senhas/hashes: só contagens e a
--     estrutura das tabelas. Pode copiar o resultado sem expor dados.
--
-- COMO USAR (phpMyAdmin do XAMPP):
--   1. Abra http://localhost/phpmyadmin/
--   2. Clique no banco do SafeRoute na coluna da esquerda (normalmente
--      "saferoute"). Isso é importante: os comandos usam o banco selecionado.
--   3. Abra a aba "SQL".
--   4. Cole a PARTE A inteira e clique em "Executar".
--   5. Depois cole a PARTE B e execute (veja o aviso no início da parte B).
--   6. Para cada resultado: use "Exportar" > CSV, ou tire um print, ou
--      copie a tabela, e envie na conversa.
--
-- Se o phpMyAdmin mostrar apenas o ÚLTIMO resultado, execute os comandos
-- um de cada vez (cada comando termina com ponto e vírgula).
-- =====================================================================


-- =====================================================================
-- PARTE A — Estrutura (sempre funciona, mesmo se faltar alguma tabela)
-- =====================================================================

-- A1. Servidor e banco selecionado
--     (se "banco_atual" vier vazio/NULL, selecione o banco e rode de novo)
SELECT VERSION()                AS versao,
       @@version_comment        AS distribuicao,
       DATABASE()               AS banco_atual,
       @@character_set_database AS charset_do_banco,
       @@collation_database     AS collation_do_banco,
       @@lower_case_table_names AS lower_case_tables,
       @@sql_mode               AS sql_mode,
       CURRENT_USER()           AS usuario_da_conexao;

-- A2. Bancos de dados existentes no servidor (sem os bancos do sistema)
SELECT SCHEMA_NAME AS banco
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin', 'test')
ORDER BY SCHEMA_NAME;

-- A3. Tabelas de todos os bancos de usuário (para achar tabelas fora do lugar,
--     por exemplo uma tabela "usuarios" em vez de "usuario")
SELECT TABLE_SCHEMA AS banco,
       TABLE_NAME   AS tabela,
       ENGINE       AS motor,
       TABLE_ROWS   AS linhas_aprox
FROM information_schema.TABLES
WHERE TABLE_SCHEMA NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin', 'test')
ORDER BY TABLE_SCHEMA, TABLE_NAME;

-- A4. Quantas das 4 tabelas previstas na SPEC existem no banco atual
--     (esperado: 4) e se existe a tabela "usuarios" (esperado: 0)
SELECT
  (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN ('usuario', 'tipo_ocorrencia', 'ocorrencia', 'rota')) AS tabelas_da_spec_encontradas,
  (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios')                                              AS existe_tabela_usuarios;

-- A5. Tabelas do banco atual (motor e collation)
SELECT TABLE_NAME      AS tabela,
       ENGINE          AS motor,
       TABLE_COLLATION AS collation,
       TABLE_ROWS      AS linhas_aprox,
       CREATE_TIME     AS criada_em
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;

-- A6. Colunas de todas as tabelas do banco atual
SELECT TABLE_NAME       AS tabela,
       ORDINAL_POSITION AS pos,
       COLUMN_NAME      AS coluna,
       COLUMN_TYPE      AS tipo,
       IS_NULLABLE      AS aceita_nulo,
       COLUMN_DEFAULT   AS valor_padrao,
       COLUMN_KEY       AS chave,
       EXTRA            AS extra,
       COLUMN_COMMENT   AS comentario
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME, ORDINAL_POSITION;

-- A7. Índices
SELECT TABLE_NAME   AS tabela,
       INDEX_NAME   AS indice,
       SEQ_IN_INDEX AS posicao,
       COLUMN_NAME  AS coluna,
       NON_UNIQUE   AS nao_unico
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- A8. Chaves estrangeiras (relacionamentos) e regra de exclusão
SELECT k.TABLE_NAME             AS tabela,
       k.COLUMN_NAME            AS coluna,
       k.CONSTRAINT_NAME        AS restricao,
       k.REFERENCED_TABLE_NAME  AS tabela_referenciada,
       k.REFERENCED_COLUMN_NAME AS coluna_referenciada,
       rc.DELETE_RULE           AS ao_excluir,
       rc.UPDATE_RULE           AS ao_atualizar
FROM information_schema.KEY_COLUMN_USAGE k
JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
  ON  rc.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
  AND rc.CONSTRAINT_NAME   = k.CONSTRAINT_NAME
  AND rc.TABLE_NAME        = k.TABLE_NAME
WHERE k.TABLE_SCHEMA = DATABASE()
  AND k.REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME;

-- A9. Views, gatilhos (triggers) e rotinas no banco atual (esperado: tudo 0)
SELECT 'views' AS objeto, COUNT(*) AS quantidade
  FROM information_schema.VIEWS    WHERE TABLE_SCHEMA = DATABASE()
UNION ALL
SELECT 'triggers', COUNT(*)
  FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()
UNION ALL
SELECT 'rotinas', COUNT(*)
  FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE();


-- =====================================================================
-- PARTE B — Qualidade dos dados (somente contagens)
-- =====================================================================
-- ATENÇÃO: estes comandos assumem as tabelas e colunas do schema.sql.
-- Se uma tabela ou coluna não existir, o phpMyAdmin mostra um erro
-- ("Table ... doesn't exist" / "Unknown column"). Isso NÃO é problema:
-- é uma informação útil. Envie o texto do erro e continue com o comando
-- seguinte (execute um de cada vez, se preciso).
-- =====================================================================

-- B1. Quantidade de linhas por tabela
SELECT
  (SELECT COUNT(*) FROM usuario)         AS usuarios,
  (SELECT COUNT(*) FROM tipo_ocorrencia) AS tipos_de_ocorrencia,
  (SELECT COUNT(*) FROM ocorrencia)      AS ocorrencias,
  (SELECT COUNT(*) FROM rota)            AS rotas;

-- B2. Usuários (sem mostrar nomes, e-mails ou hashes)
--     hashes_bcrypt deve ser igual a total; senhas_curtas_suspeitas deve ser 0
--     (hash bcrypt tem 60 caracteres; menos que isso pode ser senha em texto puro).
SELECT COUNT(*)                                                      AS total,
       COALESCE(SUM(tipo_usuario = 'admin'), 0)                      AS admins,
       COALESCE(SUM(tipo_usuario = 'comum'), 0)                      AS comuns,
       COALESCE(SUM(senha LIKE '$2_$%'), 0)                          AS hashes_bcrypt,
       COALESCE(SUM(CHAR_LENGTH(senha) < 60), 0)                     AS senhas_curtas_suspeitas,
       COALESCE(SUM(email <> LOWER(TRIM(email))), 0)                 AS emails_nao_normalizados,
       MIN(data_cadastro)                                            AS primeiro_cadastro,
       MAX(data_cadastro)                                            AS ultimo_cadastro
FROM usuario;

-- B3. E-mails repetidos ignorando maiúsculas/minúsculas e espaços
--     (esperado: 0 grupos repetidos)
SELECT COUNT(*) AS grupos_de_email_repetido
FROM (SELECT 1
      FROM usuario
      GROUP BY LOWER(TRIM(email))
      HAVING COUNT(*) > 1) AS repetidos;

-- B4. Tipos de ocorrência cadastrados
SELECT id_tipo, nome_tipo
FROM tipo_ocorrencia
ORDER BY id_tipo;

-- B5. Ocorrências: coordenadas, datas e vínculos
--     (esperado: tudo 0 nas colunas de problema)
SELECT COUNT(*)                                                           AS total,
       COALESCE(SUM(latitude  NOT BETWEEN -90  AND 90), 0)                AS latitude_invalida,
       COALESCE(SUM(longitude NOT BETWEEN -180 AND 180), 0)               AS longitude_invalida,
       COALESCE(SUM(latitude = 0 AND longitude = 0), 0)                   AS coordenada_zero_zero,
       MIN(data)                                                          AS data_mais_antiga,
       MAX(data)                                                          AS data_mais_recente,
       (SELECT COUNT(*) FROM ocorrencia o
          LEFT JOIN usuario u ON u.id_usuario = o.id_usuario
         WHERE u.id_usuario IS NULL)                                      AS sem_usuario,
       (SELECT COUNT(*) FROM ocorrencia o
          LEFT JOIN tipo_ocorrencia t ON t.id_tipo = o.id_tipo
         WHERE t.id_tipo IS NULL)                                         AS sem_tipo
FROM ocorrencia;

-- B6. Ocorrências por status e fonte
SELECT status, fonte, COUNT(*) AS quantidade
FROM ocorrencia
GROUP BY status, fonte
ORDER BY status, fonte;

-- B7. Rotas: preenchimento e vínculos
SELECT COUNT(*)                                       AS total,
       COALESCE(SUM(distancia IS NULL), 0)            AS sem_distancia,
       COALESCE(SUM(risco_estimado IS NULL), 0)       AS sem_risco,
       (SELECT COUNT(*) FROM rota r
          LEFT JOIN usuario u ON u.id_usuario = r.id_usuario
         WHERE u.id_usuario IS NULL)                  AS sem_usuario
FROM rota;

-- FIM DO DIAGNÓSTICO (nenhum dado foi alterado).
