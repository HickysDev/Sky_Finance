<h1 align="center">
  <br>
  <img src="src/img/logo.png" alt="Sky Finance Logo" width="80">
  <br>
  Sky Finance
  <br>
</h1>

<p align="center">
  Sistema pessoal de gestão financeira com interface moderna, autenticação segura e controle completo de despesas, cartões, cofrinhos e orçamentos.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap-5.2.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/jQuery-3.6-0769AD?style=for-the-badge&logo=jquery&logoColor=white" />
</p>

---

## Telas

### Dashboard
> _Adicione aqui uma screenshot do dashboard_

![Dashboard](docs/screenshots/dashboard.png)

---

### Cartão de Crédito
> _Adicione aqui uma screenshot da tela de cartão de crédito_

![Cartão de Crédito](docs/screenshots/cartao_credito.png)

---

### Débito
> _Adicione aqui uma screenshot da tela de débito_

![Débito](docs/screenshots/debito.png)

---

### Contas Fixas
> _Adicione aqui uma screenshot da tela de contas fixas_

![Contas Fixas](docs/screenshots/contas_fixas.png)

---

### Finanças
> _Adicione aqui uma screenshot da tela de finanças_

![Finanças](docs/screenshots/financas.png)

---

### Resumo Anual
> _Adicione aqui uma screenshot do resumo anual_

![Resumo Anual](docs/screenshots/resumo_anual.png)

---

### Responsáveis
> _Adicione aqui uma screenshot da tela de responsáveis_

![Responsáveis](docs/screenshots/responsaveis.png)

---

### Gerenciamento
> _Adicione aqui uma screenshot da tela de gerenciamento_

![Gerenciamento](docs/screenshots/gerenciamento.png)

---

### Login
> _Adicione aqui uma screenshot da tela de login_

![Login](docs/screenshots/login.png)

---

## Funcionalidades

### Dashboard
- Saudação personalizada com nome do usuário e horário do dia
- Resumo financeiro do mês: total de gastos, renda cadastrada e saldo
- Gráfico de pizza com gastos por categoria
- Lista dos lançamentos mais recentes
- Painel de progresso de orçamentos por categoria
- Cofrinhos (metas de economia) com barra de progresso

### Cartão de Crédito
- Cadastro de múltiplos cartões com limite, dia de fechamento e vencimento
- Lançamento de gastos avulsos e parcelados
- Controle de faturas por mês
- Marcação de fatura como paga
- Gastos recorrentes que são lançados automaticamente todo mês

### Débito
- Registro de gastos com débito, dinheiro, Pix ou outros métodos
- Categorização de cada lançamento
- Filtro por mês/ano

### Contas Fixas
- Cadastro de contas fixas mensais (aluguel, internet, água, etc.)
- Marcação de pagamento mensal
- Controle de contas vencidas e a vencer

### Finanças
- Cadastro de renda mensal
- Orçamentos por categoria (valor máximo definido pelo usuário)
- Comparativo gasto vs. orçado por categoria
- Visão mensal completa de entradas e saídas

### Resumo Anual
- Visão ano a ano dos gastos mensais
- Gráfico de barras comparativo por mês
- Breakdown por categoria em cada mês

### Responsáveis
- Registro de valores que outras pessoas devem a você
- Suporte a lançamentos parcelados por responsável
- Badge com categoria e método de pagamento por item

### Cofrinhos
- Criação de metas de economia com nome, cor e valor-alvo
- Registro de aportes com data e descrição
- Barra de progresso visual por cofrinho

### Gerenciamento
- Cadastro e edição de categorias com cor e ícone personalizados
- Configuração de gastos recorrentes
- Gerenciamento de usuários do sistema (adicionar, remover)
- Perfil do usuário: edição de nome, e-mail e foto
- Troca de senha
- Reset completo dos dados (exclusivo para o usuário administrador)

### Autenticação
- Login seguro com bcrypt (cost 12)
- Proteção contra força bruta: bloqueio por IP após 5 tentativas (15 minutos)
- CSRF token em todos os formulários
- Sessão com `HttpOnly` e `SameSite=Strict`
- Registro de novo usuário disponível apenas quando não existe nenhum cadastrado
- Logout com destruição completa da sessão

---

## Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.2, PDO (MySQL) |
| Banco de dados | MySQL / MariaDB |
| Frontend | Bootstrap 5.2.3 (dark theme), jQuery 3.6 |
| Gráficos | Chart.js 4.4.0 |
| Tabelas | DataTables 2.2.2 |
| Alertas | SweetAlert2, Toastr |
| Tooltips | Tippy.js |
| Máscaras | Cleave.js |
| Datas | Moment.js |
| Ícones | Bootstrap Icons |
| Fontes | Google Fonts (Roboto, Bebas Neue) |
| Design | Glassmorphism + Aurora animada (CSS puro) |

---

## Estrutura do Projeto

```
Sky_Finance/
├── conn/
│   ├── conn.php              # Conexão PDO com o banco
│   └── config.php            # BASE_URL e constantes dos controllers
├── php/
│   ├── controllers/          # Endpoints AJAX (cada feature tem o seu)
│   ├── middleware/
│   │   └── auth.php          # Proteção de rota (redireciona se não logado)
│   ├── models/               # Queries SQL encapsuladas por entidade
│   ├── services/
│   │   └── RecorrentesService.php  # Lança gastos recorrentes automaticamente
│   ├── templates/
│   │   ├── header.php        # Navbar, aurora, scripts globais
│   │   ├── footer.php        # Scripts finais
│   │   ├── modalCadastra.php # Modal de lançamento de gastos
│   │   └── modalCategoria.php
│   └── views/                # Páginas do sistema
├── src/
│   └── img/
│       ├── logo.png
│       └── avatars/          # Fotos de perfil dos usuários
├── styles/
│   └── style.css             # Estilos globais + aurora + glassmorphism
├── sql/                      # Scripts SQL auxiliares (ALTER TABLE, etc.)
├── DB_FINANCAS.sql            # Dump completo do banco de dados
├── index.php                 # Dashboard
├── login.php                 # Tela de login / criação da conta inicial
└── logout.php                # Logout e destruição de sessão
```

---

## Instalação (XAMPP / Local)

### Pré-requisitos
- PHP 8.2+
- MySQL / MariaDB
- Apache (XAMPP, Laragon, etc.)

### Passo a passo

**1. Clone o repositório dentro do diretório web:**
```bash
git clone https://github.com/HickysDev/Sky_Finance.git
cd Sky_Finance
```

**2. Crie o banco de dados:**

Acesse o phpMyAdmin ou MySQL CLI e importe o arquivo principal:
```sql
source DB_FINANCAS.sql
```

Em seguida, rode os scripts complementares na ordem:
```sql
source sql/usuarios.sql
source sql/usuarios_foto.sql
source sql/responsaveis.sql
source sql/contas_fixas.sql
source sql/faturas_pagas.sql
source sql/contas_pessoa_alter.sql
```

**3. Configure a conexão:**

Edite o arquivo `conn/conn.php` com suas credenciais:
```php
private static string $host = '127.0.0.1';
private static string $db   = 'projeto';
private static string $user = 'root';
private static string $pass = '';
```

**4. Acesse no navegador:**
```
http://localhost/Sky_Finance/
```

Na primeira vez, você será direcionado para criar o usuário administrador.

---

## Instalação (Servidor Linux)

### Pré-requisitos no servidor
```bash
# Apache + PHP + MySQL
sudo dnf install -y httpd php php-mysqlnd php-pdo php-mbstring php-gd php-fileinfo
sudo dnf install -y mysql-server

# Iniciar serviços
sudo systemctl enable --now httpd mysqld

# Liberar porta 80
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --reload
```

### Upload dos arquivos
Use FileZilla ou `scp` para enviar os arquivos para `/var/www/html/Sky_Finance/`.

### Permissões para upload de fotos
```bash
sudo chown -R apache:apache /var/www/html/Sky_Finance/src/img/avatars/
sudo chmod -R 755 /var/www/html/Sky_Finance/src/img/avatars/
```

---

## Segurança

- Senhas armazenadas com `password_hash()` (bcrypt, cost 12)
- Rate limiting por IP: 5 tentativas incorretas → bloqueio de 15 minutos
- CSRF token em todos os formulários (`hash_equals` na validação)
- Sessão com cookies `HttpOnly` e `SameSite=Strict`
- Todas as queries usam PDO prepared statements (sem SQL injection)
- Upload de imagens com validação de MIME type (whitelist)
- Acesso a controllers protegido por middleware de autenticação

---

## Licença

Este projeto é de uso pessoal. Sinta-se livre para usar como referência ou base para seus próprios projetos.
