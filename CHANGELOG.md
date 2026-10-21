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
- Histórico: na data desta revisão os commits originais de setembro ainda não faziam parte da branch pública. Foram integrados posteriormente, em 21/10/2026, conforme registro abaixo.

## 20/10/2026 — Localização acessível no registro de ocorrências

- Campos visíveis de latitude e longitude substituídos por busca de rua/endereço e seleção de resultado geográfico.
- Novo fluxo para marcar o local clicando no mapa, arrastar o marcador ou autorizar a localização do dispositivo.
- Coordenadas permanecem ocultas no formulário e são validadas no servidor antes do armazenamento.
- Mapa diferencia local selecionado e ocorrências aprovadas, com avisos de privacidade e mensagens de erro acessíveis.
- A busca de endereços depende do serviço colaborativo Nominatim/OpenStreetMap; se estiver indisponível, o usuário pode escolher o local diretamente no mapa.
- Incluído workflow GitHub Actions para verificar sintaxe PHP a cada atualização.
- Revisão humana: mudança solicitada após teste real no navegador da hospedagem; o estudante confirmou posteriormente que a busca de endereços atualizada funcionou em produção (resultado informado, sem auditoria independente).

## 20/10/2026 — Testes do sistema publicado, informados pelo estudante

- Endereço publicado para avaliação: https://saferoute.gt.tc/.
- O responsável executou o roteiro principal e comunicou 8 resultados OK: layout, cadastro, login, registro pendente, moderação administrativa, mapa/estatísticas, rotas e proteção de arquivos.
- Os resultados foram relatados pelo usuário, sem logs de execução independentes anexados.
- Permanecem pendentes uma instalação limpa por outra pessoa e revisão adicional de segurança; o estudante informou que a nova busca de endereços já foi testada com sucesso em produção.
- A revisão da documentação distingue funcionalidade relatada, limitação conhecida e teste ainda não comprovado.

## 21/10/2026 — Integração do histórico original de setembro

- Verificados e integrados ao grafo Git da branch `main` os **três commits autênticos de 23/09/2026**: `86076043` (estrutura inicial), `111ff173` (remoção do gerador de hash) e `62d74fbb` (cadastro e login).
- Merge original: [`698263cb`](https://github.com/nextenshi/SafeRoute/commit/698263cb09c7c0961915b354393dccfa06c9343b), contendo os dois pais (branch atual e último commit de setembro).
- Integração feita com merge sem substituição de arquivos (`-s ours`), preservando os commits históricos e o código final, sem `push --force`.
- A origem desses commits foi conferida por objeto Git e SHA; os testes de instalação limpa e convite do professor são controles separados.
