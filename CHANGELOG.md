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

## 20/10/2026 — Localização acessível no registro de ocorrências

- Campos visíveis de latitude e longitude substituídos por busca de rua/endereço e seleção de resultado geográfico.
- Novo fluxo para marcar o local clicando no mapa, arrastar o marcador ou autorizar a localização do dispositivo.
- Coordenadas permanecem ocultas no formulário e são validadas no servidor antes do armazenamento.
- Mapa diferencia local selecionado e ocorrências aprovadas, com avisos de privacidade e mensagens de erro acessíveis.
- A busca de endereços depende do serviço colaborativo Nominatim/OpenStreetMap; se estiver indisponível, o usuário pode escolher o local diretamente no mapa.
- Incluído workflow GitHub Actions para verificar sintaxe PHP a cada atualização.
- Revisão humana: mudança solicitada após teste real no navegador da hospedagem; teste funcional do formulário atualizado na hospedagem ainda é necessário.
