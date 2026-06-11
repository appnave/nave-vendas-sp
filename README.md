# SP Vendas

Pacote Laravel privado para sincronização e manutenção dos dados de vendas do ecossistema SP. Ele é consumido por projetos Laravel via Composer com repositório VCS.

## Visão geral

- Pacote privado: `appnave/nave-vendas-sp`
- Namespace principal: `BildVitta\SpVendas`
- Fornece:
  - configuração publicada em `config/sp-vendas.php`
  - migrations do módulo
  - models e factories
  - comando de instalação do pacote
  - importação inicial de vendas
  - worker para consumo de mensagens via RabbitMQ

## Requisitos

- PHP `^8.0` ou `^8.1`
- Laravel `8`, `9`, `10`, `11` ou `12`
- Composer
- Banco de dados configurado no projeto cliente
- RabbitMQ configurado para o worker de vendas
- Dependências privadas do ecossistema, quando não estiverem disponíveis no Packagist:
  - `bildvitta/iss-sdk`
  - `bildvitta/sp-crm`
  - `bildvitta/sp-hub`
  - `bildvitta/sp-produto`

## Acesso Privado

No projeto cliente, adicione o repositório VCS do pacote em `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/appnave/nave-vendas-sp"
    }
  ]
}
```

Se as dependências privadas também não estiverem no Packagist, adicione os repositórios delas no mesmo arquivo.

Instalação:

```bash
composer require appnave/nave-vendas-sp
```

Autenticação local do Composer com GitHub:

```bash
composer config -g github-oauth.github.com <YOUR_TOKEN>
```

GitHub Actions:

```yaml
env:
  COMPOSER_AUTH: >-
    {"github-oauth":{"github.com":"${{ secrets.COMPOSER_TOKEN }}"}}
```

Se o pacote cliente também precisar acessar repositórios privados da organização, use um token com permissão de leitura nesses repositórios.

## Instalação Local

Após adicionar o repositório e instalar o pacote no projeto cliente:

```bash
php artisan sp-vendas:install
```

O comando:

- publica a configuração do pacote
- publica as migrations
- executa as migrations
- publica e executa o seeder, quando confirmado

## Configuração

O pacote lê estas variáveis de ambiente:

```env
MS_SP_VENDAS_TABLE_PREFIX=vendas_

VENDAS_DB_HOST=127.0.0.1
VENDAS_DB_PORT=3306
VENDAS_DB_DATABASE=forge
VENDAS_DB_USERNAME=forge
VENDAS_DB_PASSWORD=

RABBITMQ_HOST=
RABBITMQ_PORT=5672
RABBITMQ_USER=
RABBITMQ_PASSWORD=
RABBITMQ_VIRTUALHOST=/
RABBITMQ_EXCHANGE_SALES=sales
RABBITMQ_QUEUE_SALES=sales.crm
```

Observações:

- O prefixo padrão das tabelas é `vendas_`
- O pacote depende dos pacotes `sp-hub`, `sp-crm` e `sp-produto` já configurados no projeto cliente
- As tabelas do módulo usam o prefixo definido em `MS_SP_VENDAS_TABLE_PREFIX`

## Comandos Úteis

```bash
php artisan sp-vendas:install
php artisan dataimport:vendas_sales
php artisan rabbitmqworker:sales
php artisan db:seed --class=SpVendasSeeder
```

### Importação Inicial

Use a importação inicial após configurar a conexão com o banco das vendas e garantir que as dependências do ecossistema já estejam instaladas:

```bash
php artisan dataimport:vendas_sales
```

Opções disponíveis:

```bash
php artisan dataimport:vendas_sales --select=500 --offset=0 --tables=sales,sale_accessories,sale_periodicities
```

### Worker RabbitMQ

Depois de configurar as credenciais do RabbitMQ, rode o worker:

```bash
php artisan rabbitmqworker:sales
```

## Informações Adicionais

- As migrations do pacote são registradas automaticamente pelo provider
- O pacote publica um seeder chamado `SpVendasSeeder`
- O worker processa mensagens do tipo `sales.created` e `sales.updated`
- O relacionamento entre os modelos do pacote depende dos modelos/configurações dos pacotes `sp-hub`, `sp-crm` e `sp-produto`
