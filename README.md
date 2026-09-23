# SafeRoute

Sistema Inteligente de Segurança Urbana e Planejamento de Rotas.

> "Segurança inteligente para seus caminhos."

---

## 📌 Sobre o Projeto
O SafeRoute é uma plataforma desenvolvida para fins educacionais com o objetivo de centralizar informações de segurança urbana, permitindo visualização de ocorrências em mapas e auxílio no planejamento de rotas.

> ⚠️ **Aviso Importante**: Conforme a SPEC.md, o sistema utiliza **DADOS DE DEMONSTRAÇÃO** para testes e prototipagem.

---

## 🛠️ Stack Tecnológica
- **Linguagem Back-end:** PHP 8+
- **Banco de Dados:** MySQL 8
- **Front-end:** HTML5, CSS3, JavaScript (Vanilla)
- **Ambiente de Execução:** XAMPP (Apache + MySQL)
- **Mapas:** Leaflet.js / OpenStreetMap

---

## 🚀 Como Executar no XAMPP

1. **Clonar ou copiar a pasta do projeto:**
   - Coloque a pasta `SafeRoute` dentro do diretório `htdocs` do seu XAMPP (geralmente `C:\xampp\htdocs\SafeRoute`).

2. **Iniciar os Serviços:**
   - Abra o **XAMPP Control Panel**.
   - Inicie os módulos **Apache** e **MySQL**.

3. **Configurar o Banco de Dados:**
   - Acesse o phpMyAdmin: `http://localhost/phpmyadmin/`.
   - Importe o arquivo [`sql/schema.sql`](file:///c:/Users/Administrator/Documents/SafeRoute/sql/schema.sql) para criar o banco `saferoute` e as tabelas (`usuario`, `tipo_ocorrencia`, `ocorrencia`, `rota`).
   - (Opcional) Importe o arquivo [`sql/seed.sql`](file:///c:/Users/Administrator/Documents/SafeRoute/sql/seed.sql) para carregar os tipos iniciais de ocorrência definidos na SPEC.

4. **Acessar a Aplicação:**
   - Acesse pelo navegador: `http://localhost/SafeRoute/`.
