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


## 20/10/2026 — Revisão final de instalação e segurança

- Configuração de banco separada em `.env.example` e `config/database.php` que lê apenas variáveis, sem credenciais versionadas.
- Correção do processamento POST antes de imprimir HTML em páginas de administração, preservando redirecionamentos.
- Inclusão de token CSRF nos formulários POST de cadastro, login, edição, moderação, registro, rotas e saída da conta.
- Logout alterado para método POST; senhas novas passaram a exigir pelo menos oito caracteres.
- Revisão de serialização JSON do mapa para reduzir risco de injeção em scripts.
- Documentação com instalação local e hospedagem PHP/MySQL, checklist de testes e limitações externas.
- Revisão humana necessária: validar banco, fluxos reais, APIs externas e segurança no domínio final. O agente não substitui o teste da aplicação no navegador.
- Histórico: o repositório remoto já possuía commits públicos antes desta revisão; o pacote histórico local contém commits de setembro, ainda não integrados à ancestralidade remota.
