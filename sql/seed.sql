-- =============================================
-- SafeRoute — Dados Iniciais / Fundação
-- =============================================
-- Baseado na SPEC.md (Seção 8 e Seção 12)
-- =============================================

USE saferoute;

-- Inserção dos Tipos de Ocorrência previstos na SPEC
INSERT INTO tipo_ocorrencia (id_tipo, nome_tipo) VALUES
(1, 'Furto'),
(2, 'Roubo'),
(3, 'Vandalismo'),
(4, 'Agressão'),
(5, 'Outro')
ON DUPLICATE KEY UPDATE nome_tipo = VALUES(nome_tipo);
