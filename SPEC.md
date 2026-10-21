# SPEC FINAL — SafeRoute

**Sistema:** SafeRoute — Sistema Inteligente de Segurança Urbana e Planejamento de Rotas  
**Versão da especificação:** 2.3 (inclui histórico autêntico integrado em 21/10/2026)  
**Instituição:** Instituto Federal de Rondônia — Campus Porto Velho Calama  
**Modalidade:** projeto acadêmico de Desenvolvimento de Sistemas  
**Repositório indicado:** https://github.com/nextenshi/SafeRoute

> Esta especificação descreve o que foi encontrado no código-fonte entregue, separa as funcionalidades implementadas das limitações e registra verificações ainda necessárias. Não é uma certificação de funcionamento em produção.

## 1. Problema e objetivo

Organizar informações de ocorrências urbanas e apoiar a consulta de deslocamentos por um mapa interativo, de maneira acessível. O SafeRoute recebe registros enviados por usuários, permite revisão administrativa e apresenta dados aprovados em mapas e estatísticas. Um indicador didático apresenta a quantidade de registros em uma região relacionada a dois pontos escolhidos; **não** prevê crimes, certifica a segurança de ruas nem calcula a rota mais segura.

## 2. Público e papéis

- **Visitante:** consulta páginas públicas, mapa de ocorrências aprovadas e busca locais no planejador. Para registrar ocorrências ou salvar análises, deve entrar na conta.
- **Usuário comum:** cadastra conta, faz login/logout, registra ocorrências, consulta seu perfil e histórico e salva consultas de rota.
- **Administrador:** acessa seis áreas administrativas, revisa registros, altera seu status, edita/exclui ocorrências, consulta usuários, acrescenta categorias, observa mapas e estatísticas.

## 3. Funcionalidades (RF)

| ID | Requisito | Situação no código |
| --- | --- | --- |
| RF01 | Cadastrar usuários com e-mail único e senha protegida por hash | Implementado |
| RF02 | Autenticar, encerrar sessão e controlar acesso comum/admin | Implementado |
| RF03 | Consultar e editar nome/senha do próprio perfil | Implementado |
| RF04 | Registrar ocorrência com categoria, data, hora, descrição e localização | Implementado; endereço pesquisável, clique no mapa e localização com permissão. Coordenadas são preenchidas internamente e validadas no servidor |
| RF05 | Encaminhar nova ocorrência como pendente para moderação | Implementado |
| RF06 | Aprovar, rejeitar, editar e excluir ocorrências (admin) | Implementado |
| RF07 | Consultar usuários cadastrados (admin) | Implementado; listagem, sem edição/exclusão de usuários na interface |
| RF08 | Adicionar categorias de ocorrências (admin) | Implementado; criação/listagem, sem edição/exclusão na interface |
| RF09 | Visualizar mapa público com marcadores apenas de ocorrências aprovadas e filtro por tipo | Implementado |
| RF10 | Visualizar mapa administrativo e indicadores/estatísticas de registros | Implementado |
| RF11 | Selecionar origem/destino por pesquisa de endereço ou clique no mapa | Implementado; depende de geocodificação online |
| RF12 | Exibir trajeto viário, distância/tempo e direções quando um serviço responder | Implementado na interface; depende de serviço de rotas externo |
| RF13 | Oferecer modos carro, moto, caminhada, bicicleta e ônibus | Implementado como opções; capacidades distintas por modo |
| RF14 | Consultar paradas/linhas de ônibus do OSM e tentar desenhar trajetos mapeados | Implementado como consulta externa; cobertura e carregamento não garantidos |
| RF15 | Exibir ocorrências aprovadas consideradas na análise da região como marcadores vermelhos | Implementado; o responsável informou teste funcional do mapa na hospedagem |
| RF16 | Calcular indicador escolar e registrar consultas na tabela rota | Implementado; não avalia o caminho pelas ruas |
| RF17 | Interface responsiva com navegação pública e área administrativa | Implementado; o responsável informou teste visual do site publicado |

**Áreas administrativas:** `dashboard.php`, `registros.php`, `usuarios.php`, `tipos.php`, `mapa.php` e `estatisticas.php`, na pasta `pages/admin/`. Há ainda `editar_registro.php`.

## 4. Regras de negócio (RN)

- **RN01:** uma ocorrência submetida por usuário entra como `pendente`.
- **RN02:** mapas e estatísticas públicos utilizam ocorrências `aprovada`.
- **RN03:** apenas administradores acessam `/pages/admin/*` por validação de sessão/perfil.
- **RN04:** a estimativa de risco é **experimental**: seleciona ocorrências aprovadas num retângulo expandido entre origem e destino; a pontuação cresce com a quantidade de registros e, em trajetos longos, recebe acréscimo. A área não é o trajeto efetivamente percorrido.
- **RN05:** a distância gravada em `rota` para o indicador é calculada em linha reta (Haversine), não a distância do serviço de navegação.
- **RN06:** moto utiliza o roteamento e a previsão do modo carro, sem regras específicas para motos.
- **RN07:** ônibus apresenta informações colaborativas disponíveis e links externos; não calcula uma viagem completa com horários, tarifas ou baldeações.
- **RN08:** dados de demonstração, quando usados, devem ficar identificados como fictícios, nunca como boletins oficiais.

## 5. Requisitos não funcionais (RNF)

- **RNF01 — Plataforma:** PHP 8+, MySQL/MariaDB compatível, servidor Apache local (por exemplo, XAMPP).
- **RNF02 — Interface:** HTML, CSS e JavaScript responsivos; Leaflet com mapas OpenStreetMap.
- **RNF03 — Banco:** PDO, consultas parametrizadas nas operações com entrada do usuário e relacionamentos por chave estrangeira.
- **RNF04 — Senhas/sessões:** `password_hash`/`password_verify`, sessão HTTP e restrição administrativa.
- **RNF05 — Conectividade:** serviços públicos de mapas, geocodificação, rotas e transporte exigem internet e podem limitar requisições.
- **RNF06 — Privacidade:** evitar publicar banco com dados reais, senhas, arquivos locais de conexão e informações pessoais no GitHub.
- **RNF07 — Segurança:** proteção CSRF nos formulários POST, PDO parametrizado, senhas com hash e mensagens de erro genéricas. O responsável informou teste de autorização e bloqueio de arquivos privados na hospedagem; revisão complementar (limitação de tentativas de login, testes de CSRF e auditoria de segurança) continua pendente.
- **RNF08 — Acessibilidade:** campos e botões identificados; tanto no planejamento de rotas quanto no registro de ocorrências é possível escolher um ponto por endereço ou mapa, sem digitar coordenadas. Revisão completa com teclado/leitor de tela ainda é necessária.

## 6. Modelo de dados — MySQL

As quatro tabelas são criadas por `sql/install.sql`:

- **usuario** (`id_usuario` PK, `nome`, `email` UNIQUE, `senha` hash, `tipo_usuario` `comum|admin`, `data_cadastro`).
- **tipo_ocorrencia** (`id_tipo` PK, `nome_tipo` UNIQUE).
- **ocorrencia** (`id_ocorrencia` PK, `id_usuario` FK, `id_tipo` FK, `descricao`, `latitude`, `longitude`, `data`, `horario`, `status` `pendente|aprovada|rejeitada`, `fonte`).
- **rota** (`id_rota` PK, `id_usuario` FK, `origem`, `destino`, `distancia`, `risco_estimado`, `data_consulta`).

Relacionamentos: `usuario 1:N ocorrencia`, `usuario 1:N rota`, `tipo_ocorrencia 1:N ocorrencia`. **Não existe tabela de paradas/ônibus**; são consultados serviços online.

## 7. Arquitetura, ferramentas e integrações

- **Backend:** PHP; configuração PDO em `config/database.php` (sem segredos; lê `.env` local ou variáveis definidas pela hospedagem); autenticação em `includes/auth.php`.
- **Frontend:** HTML/CSS/JS (`assets/css/style.css` e `assets/js/rotas.js`).
- **Mapas:** Leaflet + OpenStreetMap.
- **Pesquisa de endereços:** Nominatim/OSM via JavaScript.
- **Rotas rodoviárias:** OSRM público para carro; roteadores comunitários para pé/bicicleta; moto deriva de carro.
- **Transporte público:** Overpass API/OSM para paradas e relações de linhas; links externos para Google Maps e serviço local.
- **Ambiente:** XAMPP/Apache + servidor MySQL, inclusive instância usada pelo MySQL Workbench na escola.
- **Versionamento:** código e documentação no repositório GitHub `nextenshi/SafeRoute`, com histórico original de 23/09/2026 (três commits autênticos) integrado na ancestralidade da branch `main` por um merge em 21/10/2026, sem reescrever os commits de outubro ou alterar os arquivos do site.

## 8. Critérios de aceite e testes finais

| ID | Cenário a demonstrar | Resultado esperado |
| --- | --- | --- |
| CA01 | Cadastrar conta e entrar | Sessão autenticada; área de usuário visível |
| CA02 | Usuário comum tentar URL administrativa | Acesso negado |
| CA03 | Registrar ocorrência | Aparece como `pendente` para administrador |
| CA04 | Administrador aprovar registro | Registro aparece no mapa público |
| CA05 | Administrador rejeitar registro | Registro não aparece no mapa público |
| CA06 | Filtrar mapa por categoria | Apenas a categoria selecionada aparece |
| CA07 | Pesquisar origem/destino e escolher transporte | Marcadores aparecem e são manipuláveis |
| CA08 | Calcular trajeto com internet | Quando API responde, percurso segue ruas e mostra direções/distância/tempo |
| CA09 | Analisar/salvar consulta estando logado | Registra linha em `rota` e mostra indicador com marcadores correspondentes |
| CA10 | Consultar paradas numa área mapeada no OSM | Exibe paradas cadastradas ou erro/ausência explícitos, sem dados inventados |
| CA11 | Ver dashboard/estatísticas | Totais condizem com os dados no MySQL |
| CA12 | Clonar projeto e configurar banco | Aplicação abre após configurar `.env` e executar `sql/install.sql` |

**Situação da validação após a publicação:** o responsável informou oito testes funcionais aprovados no endereço público, abrangendo cadastro, autenticação, ocorrências, moderação, mapa, estatísticas, rotas e proteção de arquivos. Posteriormente informou que a nova busca de endereços na tela de ocorrências funcionou na hospedagem. O GitHub Actions validou a sintaxe PHP. Esses resultados não substituem uma instalação limpa independente, teste de carga, auditoria de segurança ou teste integral de acessibilidade. Detalhes em `docs/TESTES_E_SEGURANCA.md`.

## 9. Limitações conhecidas

1. O indicador numérico é didático e não deve ser utilizado para decisões críticas de segurança.
2. Não há navegação GPS ao vivo, alertas em tempo real ou previsão criminal.
3. A busca de paradas e trajetos de ônibus depende da cobertura OSM e da disponibilidade de Overpass; pode retornar zero itens até em locais com paradas reais.
4. Não há GTFS oficial integrado nem previsão de horários/baldeações no próprio aplicativo.
5. Perfis administrativos permitem listar usuários e criar categorias, mas não oferecem CRUD completo desses dois cadastros.
6. A hospedagem PHP/MySQL foi configurada no InfinityFree em `https://saferoute.gt.tc/`. GitHub Pages não executa o PHP; atualizações no GitHub não são transferidas automaticamente para o InfinityFree.
7. Existe automação de verificação de sintaxe PHP no GitHub Actions, mas ainda faltam testes automatizados de integração, teste de instalação por terceiro, auditoria complementar de segurança e validação completa de acessibilidade.

## 10. Escopo excluído e melhorias futuras

- Integração oficial de transporte público (GTFS/API) com horários, itinerários, direção e transbordos.
- Testes automatizados de integração e tratamento mais robusto de falhas das APIs externas; já existe teste automatizado de sintaxe PHP.
- Auditoria de alterações, recuperação de conta, controle de tentativas de login e revisão de segurança dos formulários (proteção CSRF básica já implementada).
- Monitoramento e manutenção da hospedagem PHP/MySQL publicada, avaliação de disponibilidade/HTTPS e aprimoramento da política de privacidade.
- Melhorias de acessibilidade, desempenho em rede lenta e avaliação do algoritmo didático.

## 11. Critério de entrega ao professor

- `SPEC.md` final alinhada com o código entregue.
- Arquivos-fonte em repositório GitHub (sem banco real nem credenciais).
- `README.md` e a seção **Instalação e execução** abaixo com passos; `.env.example`; `sql/install.sql` local e `sql/hosting.sql` para banco precriado na hospedagem.
- Evidências de testes locais (prints e resultados do checklist), com limitações descritas.
- **Entrega obrigatória:** link do repositório `https://github.com/nextenshi/SafeRoute`, link público `https://saferoute.gt.tc/` (funcionamento informado pelo responsável) e confirmação do convite do professor `rafaelpitwak` como colaborador.

## 12. Histórico desta versão

A especificação inicial previa cadastro, login, ocorrências, mapa e consulta de rotas. A implementação evoluiu com painel administrativo, aprovação/rejeição de registros, estatísticas, busca de origem/destino por mapa, modos de transporte e consulta colaborativa de paradas. A descrição foi atualizada para refletir **o funcionamento disponível e as pendências observadas**, sem tratar protótipos ou serviços externos como garantidos.


## 13. Instalação e execução (para o professor clonar em computador novo)

### 13.1 Ambiente e dependências

| Item | Requisito e finalidade |
| --- | --- |
| Sistema operacional local | Windows 10/11 com XAMPP; uso de MySQL Workbench também foi abordado nas aulas. Sintaxe PHP revista adicionalmente em Linux. |
| PHP | PHP 8.2 ou superior (código exige funções de PHP 8, extensões `pdo_mysql` e `mbstring`) |
| Servidor web | Apache, por exemplo no XAMPP; a alternativa PHP integrado exige configuração adequada de pasta/URL |
| SGBD | MySQL 8 ou MariaDB compatível, tabelas InnoDB, charset `utf8mb4` |
| Frontend | HTML5, CSS3, JavaScript (browser atualizado) |
| Bibliotecas de mapa | Leaflet 1.9.4 carregada por CDN; mapas/tokens OSM são remotos |
| Dependências por gerenciador | **Nenhuma**; não existe `composer install` nem `npm install` obrigatório |
| Conectividade | Internet para bibliotecas CDN, mapa, Nominatim, serviços de rotas e Overpass |

### 13.2 Banco de dados, tabelas e relacionamentos

Banco local: **`saferoute`**. Instalação local completa: **`sql/install.sql`** (cria o banco, as quatro tabelas e cinco categorias iniciais). No provedor, crie o banco pelo painel com o nome atribuído e importe **`sql/hosting.sql`**, que não usa `CREATE DATABASE` nem `USE saferoute`.

| Tabela | Campos principais | PK | Chaves estrangeiras |
| --- | --- | --- | --- |
| `usuario` | `id_usuario`, `nome`, `email` único, `senha` (hash), `tipo_usuario`, `data_cadastro` | `id_usuario` | — |
| `tipo_ocorrencia` | `id_tipo`, `nome_tipo` único | `id_tipo` | — |
| `ocorrencia` | `id_ocorrencia`, `id_usuario`, `id_tipo`, `descricao`, `latitude`, `longitude`, `data`, `horario`, `status`, `fonte` | `id_ocorrencia` | `id_usuario` → `usuario.id_usuario`; `id_tipo` → `tipo_ocorrencia.id_tipo` |
| `rota` | `id_rota`, `id_usuario`, `origem`, `destino`, `distancia`, `risco_estimado`, `data_consulta` | `id_rota` | `id_usuario` → `usuario.id_usuario` |

Categorias iniciais que o script insere: **Furto, Roubo, Vandalismo, Agressão, Outro**. A conta administrativa **não é inserida com senha fixa**: primeiro cadastre um usuário pelo site, depois promova esse usuário em banco de teste (passo 8).

### 13.3 Variáveis de ambiente

Copie o arquivo **`.env.example`** como **`.env`** na raiz de `SafeRoute`. O projeto lê automaticamente as entradas pela configuração `config/database.php`, ou aceita variáveis fornecidas diretamente pela hospedagem. **O `.env` real nunca deve ser commitado no GitHub.**

| Variável | Exemplo local | Descrição |
| --- | --- | --- |
| `APP_ENV` | `local` | Identificação do ambiente |
| `APP_BASE_PATH` | `/SafeRoute/` | Prefixo da URL local; no domínio principal da hospedagem use `/` |
| `DB_HOST` | `127.0.0.1` | Host do servidor MySQL, não confundir com o endereço do Workbench |
| `DB_PORT` | `3306` | Porta TCP do MySQL |
| `DB_NAME` | `saferoute` | Banco criado via script local ou painel do provedor |
| `DB_USER` | `root` | Usuário MySQL local (na hospedagem use o informado no painel) |
| `DB_PASS` | *(vazio)* | Senha do usuário MySQL; no ambiente da escola pode estar definida |

### 13.4 Procedimento local passo a passo (Windows/XAMPP)

1. Instale Git, XAMPP com PHP 8.2+ e MySQL 8/MariaDB compatível. Certifique-se de que `pdo_mysql` e `mbstring` estão habilitados no `php.ini`.
2. Abra PowerShell e clone o repositório **dentro de `htdocs`**: `cd C:\xampp\htdocs` e `git clone https://github.com/nextenshi/SafeRoute.git SafeRoute`. Se o repositório estiver vazio ou não tiver a versão final, é preciso enviá-la primeiro.
3. Abra `C:\xampp\htdocs\SafeRoute`, copie `.env.example` para `.env` (`Copy-Item .env.example .env` no PowerShell).
4. Edite o arquivo `.env` local no Bloco de Notas. Use as configurações do seu servidor MySQL; não publique as credenciais. Normalmente `APP_BASE_PATH=/SafeRoute/`.
5. Abra o **XAMPP Control Panel** e inicie **Apache**. Inicie também **um servidor MySQL**. Se o computador já tem MySQL Server funcionando através do serviço acessado pelo Workbench, não inicie a segunda instância MySQL do XAMPP.
6. Em instalação nova, abra `http://localhost/phpmyadmin` (se estiver usando o servidor MySQL do XAMPP) ou MySQL Workbench e execute o conteúdo completo de `sql/install.sql`. **Esse passo só vale para banco novo**; não execute por cima de dados anteriores sem backup.
7. Acesse exatamente `http://localhost/SafeRoute/`. Em `pages/cadastro.php`, crie um usuário fictício com e-mail de teste e senha temporária que você escolher (mínimo 8 caracteres). Faça login.
8. Se precisar testar o painel admin: no MySQL Workbench/phpMyAdmin do banco local execute `USE saferoute; UPDATE usuario SET tipo_usuario='admin' WHERE email='EMAIL-DE-TESTE-CADASTRADO';` substituindo o e-mail. Faça logout e login outra vez. **Não existe login administrativo público padrão.**
9. Registre uma ocorrência pela interface (será `pendente`), abra o painel administrador, aprove o registro e confira sua exibição no mapa público e estatísticas.
10. Se quiser verificar dados simulados, execute opcionalmente `sql/seed_demo.sql` **apenas em banco de demonstração com backup**. Não são ocorrências oficiais.

### 13.5 Publicação online

GitHub armazena o código e histórico, mas **não roda PHP/MySQL**. O projeto foi instalado no InfinityFree, com o endereço **`https://saferoute.gt.tc/`** e banco MySQL hospedado. O responsável informou testes funcionais nesse domínio. O procedimento para repetir a publicação está em `docs/PUBLICACAO_INFINITYFREE.md`: criar o banco pelo painel, importar `sql/hosting.sql`, configurar `.env` privado e enviar os arquivos para `htdocs`. **A hospedagem não se atualiza automaticamente quando o GitHub recebe um commit**; para atualizar o site, reenvie os arquivos alterados, mantendo o `.env` privado.

### 13.6 Como testar e avaliar

- **Usuário de teste:** não há senha compartilhada ou conta pré-semeada; cadastre `aluno.exemplo@teste.invalid` na instalação de teste, com senha temporária escolhida no momento.
- **Fluxo principal:** Cadastro → Login → Registrar ocorrência → Administrador aprova → Ocorrência no mapa público → Estatísticas → Rotas.
- **Teste de restrição:** usuário comum não deve acessar `/pages/admin/dashboard.php`.
- **Testes de ambiente e segurança:** roteiro e estado no arquivo `docs/TESTES_E_SEGURANCA.md`.
- **Problemas conhecidos:** dados OSM de ônibus incompletos; serviços externos sujeitos a falhas/cota; indicador de risco didático; dependência da hospedagem gratuita. Ainda não há comprovação de uma instalação limpa executada por outra pessoa nem de uma auditoria independente de segurança.

### 13.7 Histórico e revisão humana

Os arquivos de evolução do projeto e as decisões de revisão estão em `CHANGELOG.md`. O histórico autêntico original de 23/09/2026 foi integrado à branch pública `main` em 21/10/2026 pelo commit de merge `698263cb09c7c0961915b354393dccfa06c9343b`. Foram preservados sem alterações os commits `86076043d9048895d1fcb342d4d3d25c42ec53ec`, `111ff1731b573cb2c4cf6fb51e1973167bcaba14` e `62d74fbbe40628a5cf9ce5fd9db0ec2cb37c6df0` (autor e datas originais), além da sequência de outubro. A estratégia de merge `ours` mantém a versão atual do site, sem reverter o código. É possível conferir em `https://github.com/nextenshi/SafeRoute/commits/main`. As etapas de auditoria e testes relatados estão documentadas separadamente.