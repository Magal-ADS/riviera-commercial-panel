# Arquitetura e operação

## Componentes

- Laravel 13 atende a página Blade e os endpoints internos.
- Apache e PHP 8.4 executam dentro da imagem Docker.
- SQLite é o padrão da imagem e fica em volume Docker persistente.
- Tailwind CSS, Chart.js, Font Awesome e Google Fonts permanecem carregados por CDN, como no protótipo.
- A sessão de autenticação é mantida no banco pelo Laravel.

## Persistência

| Tabela | Finalidade |
|---|---|
| `users` | diretoria, gestores, representantes, credenciais e troca obrigatória |
| `areas` | divisões ou regiões comerciais |
| `representatives` | cadastro comercial dos representantes |
| `skus` | catálogo de produtos e gramaturas |
| `global_goals` | meta total por mês |
| `area_goals` | parcela mensal destinada a cada área |
| `representative_goals` | meta mensal de cada representante |
| `manual_histories` | valores históricos ajustados pelo gestor |
| `sales` | faturamento, volume, semana, SKU, área e representante |
| `sessions` | sessões autenticadas |
| `cache`, `jobs` | infraestrutura padrão do Laravel |

Os identificadores textuais do protótipo foram mantidos para evitar conversões que alterassem sua lógica no navegador.

## Inicialização da imagem

O entrypoint cria o arquivo SQLite quando necessário, corrige permissões, executa `php artisan migrate --force` e aplica o seeder. O seeder só insere a base inicial quando ainda não existe usuário, portanto reinícios não recriam nem sobrescrevem cadastros.

## Endpoints internos

- `GET /app/session`: identifica a sessão ou troca de senha pendente.
- `POST /app/login`: autentica e cria a sessão.
- `POST /app/recover`: gera senha provisória.
- `POST /app/reset-password`: grava a nova senha.
- `POST /app/logout`: encerra a sessão.
- `GET /app/state`: entrega os dados necessários à interface autenticada.
- `PUT /app/state`: persiste alterações dos fluxos da tela.

Todas as mutações usam proteção CSRF. Senhas são processadas no backend e armazenadas por hash.

## Deploy

Construa com `docker build -t riviera-painel-comercial .`. Em produção, use volume permanente para `/var/www/html/storage/app/data`, forneça variáveis por secret/env e publique a porta 80 atrás de proxy reverso HTTPS.

```env
APP_NAME="Painel Comercial Riviera"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://painel.exemplo.com
APP_KEY=base64:CHAVE_DE_32_BYTES_EM_BASE64
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/storage/app/data/database.sqlite
SESSION_DRIVER=database
CACHE_STORE=database
```

Para escala horizontal ou alto volume, o esquema pode ser executado em MySQL/PostgreSQL após disponibilizar o respectivo driver PHP na imagem.
