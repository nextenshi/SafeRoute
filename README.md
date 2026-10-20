# SafeRoute — Segurança inteligente para seus caminhos

Projeto escolar do Instituto Federal de Rondônia (Campus Porto Velho Calama) que reúne **ocorrências urbanas, mapa interativo e planejamento de trajetos**. Desenvolvido com **PHP, MySQL, JavaScript, Leaflet e OpenStreetMap**.

> **Uso educacional.** O indicador de risco é experimental e representa somente ocorrências registradas e aprovadas numa região. Não avalia a segurança real de uma rua, não prevê crimes e não substitui orientações oficiais.

## Funcionalidades

- Cadastro, login/logout, perfis `comum` e `admin` e edição do próprio perfil.
- Registro de ocorrências (pendentes de aprovação), categorias, mapa público com filtro e estatísticas.
- Área administrativa: dashboard, registros (aprovar/rejeitar/editar/excluir), listagem de usuários, cadastro de tipos, mapa e estatísticas.
- Planejador de rotas com pesquisa de lugares e seleção de pontos no mapa: carro, moto, caminhada, bicicleta e ônibus.
- Rotas com direções/duração/distância quando o provedor externo responde. Moto usa parâmetros de carro.
- Modo ônibus: consulta de paradas e linhas mapeadas no OpenStreetMap, podendo não carregar ou estar incompleto; **sem** previsão de chegada, tarifa ou itinerário completo.
- Marcadores de ocorrências aprovadas incluídas na análise do indicador escolar.

**Especificação completa:** [SPEC.md](SPEC.md). **Evolução:** [CHANGELOG.md](CHANGELOG.md).

## Requisitos

- PHP 8 ou superior, extensão PDO MySQL habilitada.
- MySQL ou MariaDB compatível.
- Servidor Apache (XAMPP no Windows é uma opção).
- Internet para tiles do mapa, busca de endereços, rotas e paradas.

## Instalação local no XAMPP

1. Coloque a pasta `SafeRoute` em `C:\xampp\htdocs\SafeRoute`.
2. Copie `config/database.example.php` para `config/database.php` e configure as credenciais **somente no arquivo local** (não o envie ao GitHub). O padrão é host `127.0.0.1`, banco `saferoute`, usuário `root`, senha vazia, para ambiente local sem senha. Se o MySQL da escola tiver outro usuário/senha, altere conforme sua conexão no Workbench.
3. Inicie o **Apache do XAMPP** e um servidor MySQL (o MySQL da escola, usado pelo Workbench, pode substituir o do XAMPP).
4. Em uma instalação nova, abra MySQL Workbench e execute **uma única vez** `sql/install.sql` para criar `saferoute`, tabelas e categorias padrão. **Se já tem o banco, não execute novamente sem fazer backup.**
5. Acesse `http://localhost/SafeRoute/` e cadastre uma conta pela interface.

### Tornar a primeira conta administradora

O repositório **não possui senha administrativa fixa**. Após registrar sua conta no próprio site, abra uma sessão do **MySQL Workbench** com acesso ao banco, e execute, substituindo pelo seu e-mail (não use aspas extras):

```sql
USE saferoute;
UPDATE usuario SET tipo_usuario = 'admin'
WHERE email = 'seu-email@exemplo.com';
```

Saia do site e faça login novamente para atualizar o papel da sessão. Faça essa alteração somente para uma conta que você controla.

### Dados opcionais para demonstração

Depois de criar o administrador, você **pode** executar `sql/seed_demo.sql`. As ocorrências de exemplo ficam identificadas como **DADOS DE DEMONSTRAÇÃO** e **não** representam fatos reais. Não use em um banco com dados reais sem backup.

## Usar no GitHub

Não faça upload de `config/database.php`, `.env`, dumps SQL de dados reais, backups ou senhas. A `.gitignore` ajuda no Git, mas **ao usar Upload files no site do GitHub, selecione somente os arquivos deste pacote**.

Para enviar a partir de uma pasta que já contém `SafeRoute` como raiz do projeto, com Git configurado e acesso ao repositório:

```bash
git init
git branch -M main
git remote add origin https://github.com/nextenshi/SafeRoute.git
git add .
git commit -m "Entrega final: SafeRoute e SPEC atualizada"
git push -u origin main
```

**Atenção:** se o repositório já tem commits, não use `push --force`. Faça `git clone` do repositório existente em outra pasta, copie/mescle os arquivos preparados para dentro e então `git add`, `git commit` e `git push`, preservando o histórico.

## Serviços externos e limites

Leaflet/OSM fornece o mapa, Nominatim auxilia na busca, roteadores OSRM e comunitários retornam percursos quando disponíveis, e Overpass/OSM oferece dados colaborativos de paradas/linhas. São serviços públicos, podem falhar ou limitar acesso. **O mapa do Google não é a mesma base do OpenStreetMap.** O cálculo de risco do SafeRoute **não acompanha o trajeto viário**, mas uma área entre dois pontos.

## Estado da entrega

Esta versão foi inspecionada por arquivos e verificada quanto à sintaxe. **Ainda é necessário testar** as funcionalidades completas com PHP/MySQL e internet no computador da escola. O código no GitHub **não hospeda** PHP/MySQL automaticamente: para URL pública funcional, use hospedagem específica.
