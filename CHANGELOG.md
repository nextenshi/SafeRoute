# Histórico de alterações — SafeRoute

O registro abaixo resume a evolução relatada do projeto, **não** representa hashes ou datas reais de commits do GitHub.

## Versão de entrega acadêmica — 20/10/2026

- SPEC final reescrita segundo os recursos presentes no código e suas limitações.
- Preparação de pacote de código-fonte para GitHub, ignorando configuração local e arquivos privados.
- Removido administrador com senha fixa dos scripts SQL para novas instalações.
- Dados de demonstração continuam opcionais e identificados como fictícios.

## Evolução funcional anterior

- Estrutura de usuários, login, sessão, ocorrências e MySQL.
- Painel administrativo com registros, usuários, categorias, mapa e estatísticas.
- Planejador com busca de endereços, mapa e seleção de origem/destino.
- Rotas para carro, caminhada e bicicleta via APIs externas; modo moto aproximado.
- Consulta de paradas/linhas OpenStreetMap; exibição condicionada à disponibilidade dos dados.
- Marcadores das ocorrências aprovadas consideradas no indicador escolar.

## Pendente

- Testes ponta a ponta no ambiente escolar, revisão de segurança e hospedagem PHP/MySQL pública, se exigida.
- Dados oficiais de ônibus e itinerários completos não integrados.
