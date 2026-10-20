USE saferoute;
-- DADOS DE DEMONSTRAÇÃO: podem ser removidos sem afetar o funcionamento do sistema.
INSERT INTO ocorrencia (id_usuario,id_tipo,descricao,latitude,longitude,data,horario,status,fonte)
SELECT u.id_usuario,t.id_tipo,'DADOS DE DEMONSTRAÇÃO — exemplo de ocorrência registrada.',-8.76120000,-63.90040000,CURDATE(),'08:30:00','aprovada','usuario'
FROM usuario u JOIN tipo_ocorrencia t ON t.nome_tipo='Furto'
WHERE u.id_usuario = (SELECT MIN(id_usuario) FROM usuario WHERE tipo_usuario='admin') AND NOT EXISTS (SELECT 1 FROM ocorrencia WHERE descricao LIKE 'DADOS DE DEMONSTRAÇÃO%');
INSERT INTO ocorrencia (id_usuario,id_tipo,descricao,latitude,longitude,data,horario,status,fonte)
SELECT u.id_usuario,t.id_tipo,'DADOS DE DEMONSTRAÇÃO — exemplo de ocorrência registrada.',-8.75000000,-63.89500000,CURDATE(),'18:10:00','aprovada','usuario'
FROM usuario u JOIN tipo_ocorrencia t ON t.nome_tipo='Roubo'
WHERE u.id_usuario = (SELECT MIN(id_usuario) FROM usuario WHERE tipo_usuario='admin') AND NOT EXISTS (SELECT 1 FROM ocorrencia WHERE descricao LIKE 'DADOS DE DEMONSTRAÇÃO%' AND latitude=-8.75000000);
