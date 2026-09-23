\# SPEC 1.0 — SafeRoute



\## Sistema Inteligente de Segurança Urbana e Planejamento de Rotas



\---



\# 1. Visão geral do projeto



\## Nome do sistema



SafeRoute



\## Slogan



"Segurança inteligente para seus caminhos."



\## Descrição



O SafeRoute é uma plataforma digital de segurança urbana que utiliza geolocalização, dados de ocorrências e análise de padrões para apresentar informações de risco estimado em determinadas regiões.



O objetivo do sistema é auxiliar usuários durante seus deslocamentos, permitindo visualizar informações relacionadas à segurança, consultar ocorrências e comparar trajetos considerando dados disponíveis.



O sistema não promete garantir segurança absoluta e não classifica locais como totalmente seguros ou perigosos.



O SafeRoute apresenta:



\- Dados de ocorrências;

\- Distribuição geográfica;

\- Estatísticas;

\- Tendências;

\- Níveis de risco estimados;

\- Comparação de rotas.



\---



\# 2. Objetivo do sistema



O SafeRoute busca resolver o problema da falta de acesso simples e organizado a informações de segurança urbana.



Muitas pessoas realizam deslocamentos diariamente sem possuir uma visão clara sobre possíveis concentrações de ocorrências nas regiões por onde passam.



O sistema tem como objetivo:



\- Centralizar informações de segurança;

\- Facilitar a visualização de ocorrências;

\- Auxiliar decisões de deslocamento;

\- Apresentar análises baseadas em dados;

\- Incentivar uma cultura de prevenção.



\---



\# 3. Público-alvo



O sistema será desenvolvido inicialmente para:



\## Usuários comuns



Pessoas que precisam se deslocar pela cidade:



\- Estudantes;

\- Trabalhadores;

\- Pedestres;

\- Ciclistas;

\- Usuários de transporte público;

\- Motoristas.



\## Usuários institucionais



Possíveis usuários futuros:



\- Escolas;

\- Empresas;

\- Instituições públicas.



\---



\# 4. Perfis de usuário



\## 4.1 Usuário comum



Permissões:



\- Criar conta;

\- Realizar login;

\- Visualizar mapa;

\- Consultar ocorrências;

\- Registrar ocorrências;

\- Planejar rotas;

\- Visualizar estatísticas públicas;

\- Gerenciar próprio perfil.



\---



\## 4.2 Administrador



Responsável pelo gerenciamento do sistema.



Permissões:



\- Acessar painel administrativo;

\- Visualizar ocorrências enviadas;

\- Analisar registros;

\- Aprovar ou rejeitar ocorrências;

\- Editar informações;

\- Remover registros inadequados;

\- Visualizar estatísticas gerais.



\---



\# 5. Casos de uso



\## UC01 — Cadastro



O usuário cria uma conta informando:



\- Nome;

\- Email;

\- Senha.



Resultado:



Conta criada no sistema.



\---



\## UC02 — Login



O usuário acessa o sistema através de suas credenciais.



\---



\## UC03 — Visualizar mapa de risco



O usuário acessa um mapa contendo:



\- Regiões;

\- Marcadores;

\- Níveis de risco;

\- Ocorrências.



\---



\## UC04 — Consultar ocorrência



O usuário visualiza informações de ocorrências:



\- Tipo;

\- Localização;

\- Data;

\- Horário;

\- Status.



\---



\## UC05 — Registrar ocorrência



O usuário envia um relato contendo:



\- Tipo;

\- Local;

\- Data;

\- Horário;

\- Descrição.



\---



\## UC06 — Planejar rota



O usuário informa:



\- Origem;

\- Destino.



O sistema apresenta opções de trajetos.



\---



\## UC07 — Analisar risco da rota



O sistema apresenta:



\- Nível de risco estimado;

\- Informações da região;

\- Alertas quando disponíveis.



\---



\# 6. Requisitos funcionais



\## Usuários



RF01 — O sistema deve permitir cadastro de usuários.



RF02 — O sistema deve permitir autenticação.



RF03 — O sistema deve permitir edição do perfil.



RF04 — O sistema deve armazenar informações dos usuários.



\---



\## Mapa



RF05 — O sistema deve apresentar um mapa interativo.



RF06 — O sistema deve exibir ocorrências por localização.



RF07 — O sistema deve apresentar níveis de risco.



RF08 — O sistema deve permitir filtros no mapa.



Filtros:



\- Tipo de ocorrência;

\- Data;

\- Região;

\- Status.



\---



\## Ocorrências



RF09 — O sistema deve permitir cadastro de ocorrências.



RF10 — O sistema deve permitir consultar ocorrências.



RF11 — O sistema deve armazenar localização da ocorrência.



RF12 — O sistema deve possuir status:



\- Pendente;

\- Aprovada;

\- Rejeitada.



RF13 — O sistema deve diferenciar relatos de usuários e dados oficiais.



\---



\## Rotas



RF14 — O sistema deve permitir informar origem e destino.



RF15 — O sistema deve apresentar trajetos disponíveis.



RF16 — O sistema deve apresentar uma estimativa de risco.



RF17 — O sistema deve explicar os fatores considerados na estimativa.



\---



\## Estatísticas



RF18 — O sistema deve apresentar dados agrupados.



Exemplos:



\- Quantidade de ocorrências;

\- Tipos mais registrados;

\- Regiões com maior concentração;

\- Horários com maior frequência.



\---



\## Administração



RF19 — O administrador deve visualizar registros.



RF20 — O administrador deve aprovar ou rejeitar ocorrências.



RF21 — O administrador deve editar informações.



RF22 — O administrador deve remover conteúdos inadequados.



\---



\# 7. Requisitos não funcionais



RNF01 — O sistema deve possuir interface intuitiva.



RNF02 — O sistema deve funcionar em computadores e dispositivos móveis.



RNF03 — O sistema deve validar informações enviadas pelos usuários.



RNF04 — Senhas devem ser armazenadas de forma segura.



RNF05 — Dados pessoais não devem ser expostos publicamente.



RNF06 — O sistema deve possuir controle de acesso administrativo.



RNF07 — O sistema deve possuir boa organização de código.



RNF08 — O sistema deve possuir mensagens claras para o usuário.



RNF09 — Dados demonstrativos devem ser identificados.



RNF10 — O sistema deve evitar apresentar informações falsas como fatos confirmados.



\---



\# 8. Modelo de dados



\## Tabela USUARIO



Campos:



\- id\_usuario (PK)

\- nome

\- email

\- senha

\- tipo\_usuario

\- data\_cadastro



\## Tabela OCORRENCIA



Campos:



\- id\_ocorrencia (PK)

\- id\_usuario (FK)

\- id\_tipo (FK)

\- descricao

\- latitude

\- longitude

\- data

\- horario

\- status

\- fonte



\## Tabela TIPO\_OCORRENCIA



Campos:



\- id\_tipo (PK)

\- nome\_tipo



Exemplos:



\- Furto

\- Roubo

\- Vandalismo

\- Agressão

\- Outro



\## Tabela ROTA



Campos:



\- id\_rota (PK)

\- id\_usuario (FK)

\- origem

\- destino

\- distancia

\- risco\_estimado



\## Relacionamentos



USUARIO 1:N OCORRENCIA



Um usuário pode registrar várias ocorrências.



TIPO\_OCORRENCIA 1:N OCORRENCIA



Um tipo pode possuir várias ocorrências.



USUARIO 1:N ROTA



Um usuário pode pesquisar várias rotas.



\---



\# 9. Sistema de análise de risco



O risco será uma estimativa calculada utilizando:



\- Quantidade de ocorrências;

\- Tipo de ocorrência;

\- Localização;

\- Frequência;

\- Recência.



Classificação:



0-25:

Baixo



26-50:

Moderado



51-75:

Alto



76-100:

Muito alto



O sistema deve mostrar:



"Risco estimado baseado nos dados disponíveis."



\---



\# 10. Interface do sistema



\## Página inicial



Apresentar:



\- Nome;

\- Objetivo;

\- Botões principais;

\- Resumo do funcionamento.



\## Dashboard



Mostrar:



\- Número de ocorrências;

\- Regiões analisadas;

\- Estatísticas;

\- Prévia do mapa.



\## Mapa



Possuir:



\- Marcadores;

\- Legenda;

\- Filtros;

\- Informações da ocorrência.



\## Rotas



Possuir:



\- Campo origem;

\- Campo destino;

\- Resultado da análise.



\## Ocorrências



Possuir:



\- Lista;

\- Pesquisa;

\- Cadastro.



\## Perfil



Mostrar:



\- Dados do usuário;

\- Histórico de registros.



\---



\# 11. Privacidade e segurança



O sistema deve:



\- Proteger informações pessoais;

\- Não revelar identidade de usuários;

\- Não permitir exposição de suspeitos;

\- Não incentivar denúncias falsas;

\- Separar dados oficiais de relatos.



\---



\# 12. Dados utilizados



Durante o protótipo:



Serão utilizados dados fictícios identificados como:



"DADOS DE DEMONSTRAÇÃO"



Possíveis fontes futuras:



\- Ministério da Justiça e Segurança Pública;

\- Sinesp;

\- Atlas da Violência (Ipea).



\---



\# 13. Critérios de aceite



\## Cadastro



Dado que o usuário esteja no cadastro,



quando preencher os campos obrigatórios,



então o sistema deve criar uma conta.



\## Mapa



Dado que existam ocorrências,



quando acessar o mapa,



então o usuário deve visualizar os registros.



\## Ocorrência



Dado que o usuário esteja autenticado,



quando enviar uma ocorrência,



então ela deve ser armazenada como pendente.



\## Rota



Dado que o usuário informe origem e destino,



quando solicitar uma rota,



então o sistema deve apresentar opções disponíveis.



\## Risco



Dado que existam dados suficientes,



quando o sistema analisar uma região,



então deve apresentar um risco estimado.



\## Administração



Dado que exista uma ocorrência pendente,



quando o administrador acessar,



então ele deve conseguir analisar o registro.



\---



\# 14. Limitações do sistema



O SafeRoute:



\- Não garante segurança absoluta;

\- Não substitui órgãos oficiais;

\- Não identifica criminosos;

\- Não realiza monitoramento de pessoas;

\- Não substitui serviços de emergência.



\---



\# 15. Futuras melhorias



Possíveis evoluções:



\- Integração com APIs oficiais;

\- Aplicativo mobile;

\- Inteligência Artificial avançada;

\- Alertas personalizados;

\- Integração com transporte público;

\- Histórico inteligente de rotas.

