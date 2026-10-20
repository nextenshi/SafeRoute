# Passos para entregar no GitHub — SafeRoute

1. Acesse https://github.com/nextenshi/SafeRoute e confirme que está autenticado na conta correta.
2. Confira se há arquivos antigos: **não exclua o histórico Git**. Se o projeto já está no repositório, prefira cloná-lo e copiar os novos arquivos por cima, mantendo `.git`.
3. Coloque na **raiz do repositório** o conteúdo da pasta `SafeRoute` deste pacote (não crie `SafeRoute/SafeRoute/`).
4. Confirme a presença de `SPEC.md`, `README.md`, `CHANGELOG.md`, `.gitignore`, `assets/`, `pages/`, `includes/`, `sql/` e `config/database.example.php`.
5. Verifique que `config/database.php`, dados reais e credenciais **não** aparecem entre os arquivos preparados para envio.
6. Faça commit com mensagem sugestiva: **Entrega final — SafeRoute com SPEC atualizada**.
7. Abra o repositório no navegador e verifique que a `SPEC.md` está legível e que os arquivos foram enviados.
8. Teste o projeto localmente; se o professor pedir hospedagem, prepare também um endereço PHP/MySQL operacional.

## Se usar Git Bash e o repositório já existir

```bash
git clone https://github.com/nextenshi/SafeRoute.git
cd SafeRoute
# Copie para esta pasta os arquivos desta entrega, sem apagar .git
git status
git add .
git commit -m "Entrega final — SafeRoute com SPEC atualizada"
git push origin main
```

**Não execute `git push --force`** para substituir histórico existente. Se sua branch principal tiver outro nome, ajuste o último comando.

## Checklist de demonstracao

- [ ] Cadastro, login e logout.
- [ ] Negar acesso admin para conta comum.
- [ ] Criar e aprovar uma ocorrência de teste.
- [ ] Conferir ponto no mapa público e dados nas estatísticas.
- [ ] Selecionar origem/destino, calcular uma rota rodoviária e mostrar orientações.
- [ ] Exibir os registros que compõem o indicador escolar.
- [ ] Tentar consulta de ônibus e explicar limitações do OSM/Overpass.
- [ ] Conferir SPEC e README no GitHub.
