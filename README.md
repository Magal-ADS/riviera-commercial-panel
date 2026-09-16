# Painel Comercial Riviera

Painel de gestão comercial desenvolvido para a Riviera Pescados. A aplicação centraliza metas, faturamento, produtos e a estrutura da força de vendas em uma interface única, com visões específicas para Diretoria, Gestores e Representantes.

## Funcionalidades

- dashboard consolidada com metas, faturamento, atingimento e projeção;
- filtros por período, área, gestor e representante;
- mapa de hierarquia e desempenho da equipe;
- planejamento de metas globais, por área e por representante;
- distribuição automática de metas com base no histórico recente;
- cadastro de áreas, gestores, representantes e produtos;
- lançamento semanal de vendas, volume, cliente e SKU;
- relatórios CSV de faturamento, equipe, produtos e estrutura comercial;
- autenticação por sessão, troca de senha inicial e recuperação simulada;
- controle de acesso conforme o perfil do usuário;
- banco SQLite persistente no ambiente Docker;
- base demonstrativa com 12 meses de histórico comercial.

## Tecnologias

- PHP 8.4+
- Laravel 13
- SQLite
- Blade e JavaScript
- Tailwind CSS
- Chart.js
- Docker e Docker Compose
- PHPUnit

## Executando com Docker

O caminho mais rápido para iniciar o projeto é pelo Docker. É necessário ter o Docker com o plugin Compose instalado.

```bash
docker compose up --build -d
```

A aplicação estará disponível em:

```text
http://localhost:8011
```

Na primeira inicialização, o container:

1. cria o banco SQLite persistente;
2. executa todas as migrations;
3. cadastra os usuários locais;
4. insere a base demonstrativa;
5. inicia o servidor Apache.

Comandos úteis:

```bash
# Acompanhar a inicialização
docker compose logs -f app

# Verificar o estado do container
docker compose ps

# Executar os testes
docker compose exec app php artisan test

# Encerrar a aplicação
docker compose down
```

Os dados ficam armazenados no volume `panel-data`. O comando abaixo também exclui esse volume e deve ser utilizado somente quando a intenção for zerar o banco:

```bash
docker compose down -v
```

## Acessos para demonstração

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Diretoria | `diretor@local.test` | `123456` |
| Gestor | `gerente@local.test` | `123456` |
| Representante | `representante@local.test` | `123456` |
| Diretoria da base demo | `diretoria@demo.riviera.local` | `123456` |

Essas credenciais são destinadas exclusivamente ao ambiente local. Não devem ser utilizadas em produção.

## Executando sem Docker

Pré-requisitos: PHP 8.3 ou superior, Composer e Node.js.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## Testes e qualidade

```bash
php artisan test --compact
vendor/bin/pint --format agent
composer audit
```

A suíte cobre autenticação, troca de senha, autorização, persistência relacional e a carga dos dados demonstrativos.

## Estrutura principal

```text
app/Http/Controllers/    Autenticação e persistência do painel
app/Models/              Modelos da aplicação
database/migrations/     Estrutura relacional do banco
database/seeders/        Usuários locais e base demonstrativa
resources/views/         Interface principal em Blade
tests/Feature/           Testes dos fluxos essenciais
docker/                  Inicialização do container
docs/                    Documentação funcional e técnica
```

## Documentação complementar

- [Visão do projeto e regras funcionais](docs/PROJETO.md)
- [Arquitetura, banco de dados e implantação](docs/ARQUITETURA.md)

## Configuração de produção

Antes de publicar a aplicação, configure ao menos:

- `APP_ENV=production`;
- `APP_DEBUG=false`;
- uma `APP_KEY` exclusiva;
- HTTPS no proxy reverso;
- credenciais próprias, removendo os acessos demonstrativos;
- banco, sessão, cache e filas adequados ao ambiente de produção.
