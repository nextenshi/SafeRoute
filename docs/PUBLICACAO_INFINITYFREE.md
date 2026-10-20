# Publicação do SafeRoute em hospedagem PHP + MySQL

O código está no GitHub, mas **não fica disponível ao público** até ser implantado num servidor que execute PHP e tenha MySQL.

## Passo a passo (exemplo: InfinityFree)

1. Crie uma conta diretamente no site https://www.infinityfree.com/ e ative um domínio/subdomínio disponível.
2. No painel da hospedagem, crie um banco em MySQL Databases. Registre separadamente o hostname, banco (incluindo prefixo), usuário, porta e senha. Nunca coloque senhas no GitHub.
3. Abra o phpMyAdmin do próprio provedor, selecione o banco que acabou de criar e importe `sql/hosting.sql` (não use `sql/install.sql` na hospedagem).
4. No gerenciador de arquivos/FTP, envie o CONTEÚDO do repositório para a pasta pública, normalmente `htdocs`. O arquivo `index.php` deve ficar diretamente na raiz pública.
5. No servidor, crie um arquivo privado `.env` a partir de `.env.example`, alterando `APP_BASE_PATH=/` e os dados do banco. NÃO envie esse `.env` ao GitHub.
6. Verifique se `https://SEU-DOMINIO/.env` é bloqueado pelo Apache. Se ele mostrar conteúdo, retire imediatamente o arquivo do diretório público e procure uma instalação mais segura.
7. Abra o site em aba anônima. Teste cadastro, login, registro pendente, aprovação como administrador, mapa, estatísticas e rotas.
8. Para testar administração, cadastre antes uma conta fictícia e, pelo phpMyAdmin da hospedagem, execute `UPDATE usuario SET tipo_usuario='admin' WHERE email='EMAIL-CADASTRADO';`. Saia e entre novamente.

O provedor pode impor limites de recursos ou desativar funcionalidades. O SafeRoute utiliza Leaflet e serviços OSM/OSRM/Overpass/Nominatim, cujos dados e disponibilidade variam. Não invente URL final nem afirme teste de produção sem realizá-lo.

**URL pública atual:** pendente de publicação e teste.
