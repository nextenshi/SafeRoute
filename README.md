# SafeRoute — Sistema Inteligente de Segurança Urbana e Rotas

Projeto educacional do IFRO Campus Porto Velho Calama, feito em PHP 8.2+, MySQL 8, JavaScript e Leaflet/OpenStreetMap.

> Os registros são enviados por usuários ou são dados demonstrativos, quando explicitamente carregados. O indicador de risco é experimental e não determina a segurança real de uma via. Os dados de ônibus dependem do OpenStreetMap/Overpass e podem estar incompletos.

## Funcionalidades

- Cadastro, login, perfil e controle de acesso por tipo de usuário.
- Registro e moderação de ocorrências; visualização pública somente após aprovação.
- Painel administrativo, categorias, usuários, mapa e estatísticas.
- Planejamento de rotas com cinco opções (carro, moto, a pé, bicicleta e ônibus), busca de lugares e integração com serviços externos de mapas.
- Histórico de consultas de rotas e indicador didático baseado nas ocorrências próximas dos pontos analisados.

## Instalação local (Windows/XAMPP)

1. Instale Git e XAMPP com PHP 8.2+ e `pdo_mysql`/ `mbstring`.
2. No PowerShell: `cd C:\xampp\htdocs` e `git clone https://github.com/nextenshi/SafeRoute.git SafeRoute`.
3. Na pasta clonada: `Copy-Item .env.example .env`.
4. Edite `.env` com os dados reais do MySQL local, mantendo `APP_BASE_PATH=/SafeRoute/`.
5. Inicie o Apache do XAMPP e um servidor MySQL (pode ser o MySQL Server utilizado pelo Workbench).
6. Em um banco **novo**, importe `sql/install.sql` via phpMyAdmin ou MySQL Workbench.
7. Abra `http://localhost/SafeRoute/` e crie uma conta pelo cadastro.

O sistema não exige Composer nem npm. O primeiro administrador é configurado manualmente pelo banco após criar uma conta de teste, seguindo a SPEC.

## Documentação para avaliação

- [SPEC.md](SPEC.md): requisitos, modelo de dados, **Instalação e execução**, critérios de aceite e limitações.
- [.env.example](.env.example): nomes de variáveis (sem senhas).
- [CHANGELOG.md](CHANGELOG.md): evolução, correções e revisão humana.
- [sql/install.sql](sql/install.sql): instalação local com criação do banco.
- [sql/hosting.sql](sql/hosting.sql): importação em banco criado no painel da hospedagem.
- [docs/PUBLICACAO_INFINITYFREE.md](docs/PUBLICACAO_INFINITYFREE.md): guia de hospedagem PHP/MySQL.
- [docs/TESTES_E_SEGURANCA.md](docs/TESTES_E_SEGURANCA.md): checklist de verificação.

**Site publicado:** https://saferoute.gt.tc/ — o responsável informou testes funcionais de cadastro, login, ocorrências, mapa e rotas. O servidor usa hospedagem PHP/MySQL InfinityFree, separada do GitHub; alterações no repositório precisam ser enviadas manualmente para o servidor. Nunca publique `.env`, exportações com dados pessoais nem credenciais.

