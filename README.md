# teste-log — POC de logs estruturados 

Mini API em Laravel 8 criada para validar, na prática, a arquitetura de logs proposta no estudo:

- **Correlation ID** (`X-Correlation-ID`) gerado ou reaproveitado por requisição, propagado para todos os logs e devolvido no header da resposta.
- **Log de diagnóstico estruturado** em JSON, uma linha por evento, gravado em arquivo — nunca no banco.
- **Auditoria de negócio** em tabela própria (SQLite), enxuta e indexada.
- **Masking** automático de campos sensíveis em qualquer profundidade do contexto.
- **Taxonomia por evento de domínio** (`account.closed`, `integration.pay.charge.failed`) em vez de por camada de código.

A ponte entre o log de diagnóstico e a auditoria é o `correlation_id`: com ele você sai de um registro de auditoria e chega em todo o rastro técnico daquela operação.

---

## 1. Como rodar em sua máquina

### Requisitos

- PHP 7.3 ou 8.0 (o `composer.json` declara `^7.3|^8.0`, igual ao PDV)
- Composer 2
- Extensão `pdo_sqlite` habilitada

### Instalação

```bash
git clone https://github.com/Samuel-Araujo-Silveira/teste-log.git
cd teste-log
composer install --ignore-platform-req=php
cp .env.example .env
php artisan key:generate
```

### Configuração do `.env`

O `.env.example` vem com a configuração padrão do Laravel (MySQL). Ajuste estas linhas:

```dotenv
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

DB_CONNECTION=sqlite
DB_DATABASE=/caminho/absoluto/para/teste-log/database/database.sqlite
```

> Use o caminho **absoluto** em `DB_DATABASE`. Um caminho relativo é resolvido a partir do diretório de trabalho atual e quebra quando você roda comandos de outra pasta.

### Banco de dados e servidor

```bash
touch database/database.sqlite
php artisan migrate
php artisan serve --port=8000
```

As migrations criam `accounts` (a entidade de negócio) e `audit_logs` (a trilha de auditoria).

### Sobre o `--ignore-platform-req=php`

O Laravel 8 está em EOL e só suporta PHP até a série 8.0. Se sua máquina tiver PHP 8.1+, o Composer recusa a instalação sem essa flag. A aplicação roda normalmente, mas o PHP emite avisos de *deprecation* vindos das dependências (principalmente `facade/ignition`) no stderr do `artisan serve`. Eles não afetam o comportamento e o canal `deprecations` do Laravel já está apontado para `null`.

Se o Composer bloquear a instalação por *security advisories* do Laravel 8, rode `composer config audit.block-insecure false` antes do `composer install`.

---

## 2. Comandos para testar as rotas

São duas rotas:

| Método | URI | Nome | O que exercita |
|---|---|---|---|
| POST | `/api/accounts` | `account.store` | Caminho feliz: abre a conta, loga o evento e grava a auditoria |
| POST | `/api/accounts/{account}/close` | `account.close` | Integração externa que pode falhar, conta já fechada e validação |

Todas as requisições exigem os headers `user-id` e `establishment-id`, que simulam o contexto de autenticação do PDV. O header `X-Correlation-ID` é opcional.

### 2.1 Abrir uma conta (201)

```bash
curl -i -X POST http://127.0.0.1:8000/api/accounts \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "user-id: 44" \
  -H "establishment-id: 12" \
  -d '{"name":"Mesa 12","total":89.90}'
```

Repare no header `X-Correlation-ID` da resposta: é por ele que você vai rastrear a operação nos logs.

### 2.2 Fechar a conta com sucesso (200)

```bash
curl -i -X POST http://127.0.0.1:8000/api/accounts/1/close \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "user-id: 44" \
  -H "establishment-id: 12" \
  -d '{"payment_method":"pix"}'
```

### 2.3 Fechar a conta com falha no gateway de pagamento (400)

O campo `force_gateway_failure` existe apenas nesta POC, para forçar o caminho de erro da integração externa de forma determinística.

```bash
curl -i -X POST http://127.0.0.1:8000/api/accounts/1/close \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "user-id: 44" \
  -H "establishment-id: 12" \
  -d '{"payment_method":"pix","force_gateway_failure":true}'
```

Este caso gera um log de nível `ERROR` **e** um registro de auditoria com `result = failure`. A auditoria da falha é gravada depois do `DB::rollBack()`, no `catch` do controller — se ficasse dentro do service, o rollback apagaria justamente a evidência da tentativa que falhou.

### 2.4 Enviando o seu próprio correlation ID

Quando o cliente manda um UUID válido em `X-Correlation-ID`, a aplicação reaproveita esse ID em vez de gerar um novo. É assim que o rastro atravessa a fronteira entre front e back.

```bash
curl -i -X POST http://127.0.0.1:8000/api/accounts/1/close \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "user-id: 44" \
  -H "establishment-id: 12" \
  -H "X-Correlation-ID: 11111111-2222-4333-8444-555555555555" \
  -d '{"payment_method":"pix","force_gateway_failure":true}'
```

### 2.5 Fechar uma conta já fechada (400)

Repita o comando 2.2 depois que a conta já estiver fechada. O fluxo gera um log de nível `WARNING` com o evento `account.close.rejected`.

### 2.6 Erro de validação (422)

```bash
curl -i -X POST http://127.0.0.1:8000/api/accounts/1/close \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "user-id: 44" \
  -H "establishment-id: 12" \
  -d '{}'
```

Validação não gera registro de auditoria — não é um fato de negócio, é uma requisição malformada.

### 2.7 Sem contexto de usuário (401)

```bash
curl -i -X POST http://127.0.0.1:8000/api/accounts \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Mesa 12"}'
```

---

## 3. Como ver os logs e os audits

### 3.1 Logs de diagnóstico (arquivo JSON)

Ficam em `storage/logs/diagnostic-YYYY-MM-DD.log`, uma linha JSON por evento.

```bash
tail -f storage/logs/diagnostic-*.log
```

Para ler formatado, com `jq`:

```bash
tail -5 storage/logs/diagnostic-*.log | jq
```

Sem `jq`, usando Python:

```bash
tail -5 storage/logs/diagnostic-*.log | python3 -c "
import sys, json
for line in sys.stdin:
    print(json.dumps(json.loads(line), ensure_ascii=False, indent=2))
"
```

Um evento tem esta cara:

```json
{
  "message": "Cobrança recusada pelo gateway de pagamento",
  "level_name": "ERROR",
  "context": {
    "correlation_id": "11111111-2222-4333-8444-555555555555",
    "event": "integration.pay.charge.failed",
    "component": "PayGatewayService",
    "entity_type": "account",
    "entity_id": 1,
    "http_status": 502,
    "payment_method": "pix"
  },
  "extra": {
    "type": "http",
    "user_id": 44,
    "establishment_id": 12,
    "http": { "method": "POST", "path": "api/accounts/1/close" }
  }
}
```

**Rastrear uma operação inteira** pelo correlation ID:

```bash
grep '11111111-2222-4333-8444-555555555555' storage/logs/diagnostic-*.log | jq
```

**Filtrar por evento de negócio**, que é o ganho prático da taxonomia escolhida:

```bash
jq -c 'select(.context.event == "integration.pay.charge.failed")' storage/logs/diagnostic-*.log
```

**Ver só os erros:**

```bash
jq -c 'select(.level_name == "ERROR")' storage/logs/diagnostic-*.log
```

### 3.2 Auditoria (SQLite)

A tabela `audit_logs` guarda o fato de negócio: quem fez, o quê, em qual entidade, com que resultado e sob qual correlation ID. Com `sqlite3` instalado:

```bash
sqlite3 -header -column database/database.sqlite \
  "SELECT id, correlation_id, user_id, action, subject_type, subject_id, result, metadata, created_at
   FROM audit_logs ORDER BY id;"
```

Se você não tiver o `sqlite3` na máquina, o mesmo resultado via PHP:

```bash
php -r '
$pdo = new PDO("sqlite:database/database.sqlite");
foreach ($pdo->query("SELECT * FROM audit_logs ORDER BY id") as $r) {
    printf("#%d %s | user=%s | %s %s#%s | %s | %s | %s\n",
        $r["id"], $r["correlation_id"], $r["user_id"], $r["action"],
        $r["subject_type"], $r["subject_id"], $r["result"], $r["metadata"], $r["created_at"]);
}
'
```

Saída esperada depois de rodar os testes da seção 2:

```
#1 c46e978a-… | user=44 | account.opened account#1 | success | {"total":89.9}
#2 11111111-… | user=44 | account.closed account#1 | failure | {"reason":"PaymentNotAuthorizedException"}
#3 1d561083-… | user=44 | account.closed account#1 | success | {"total":89.9,"payment_method":"pix"}
```

A tabela é propositalmente enxuta e indexada por `correlation_id`, `action`, `subject_type + subject_id` e `user_id + created_at` — sem nenhuma coluna `LONGTEXT` com payload cru, que é o que hoje trava as consultas na tabela de logs do PDV.

### 3.3 Cruzando auditoria e diagnóstico

Este é o fluxo real de investigação: você parte de um registro de auditoria que deu errado e recupera todo o rastro técnico daquela operação.

```bash
php -r '
$pdo = new PDO("sqlite:database/database.sqlite");
$row = $pdo->query("SELECT correlation_id FROM audit_logs WHERE result = \"failure\" ORDER BY id DESC LIMIT 1")->fetch();
echo $row["correlation_id"], PHP_EOL;
' | xargs -I {} grep {} storage/logs/diagnostic-*.log | jq -c '{level: .level_name, event: .context.event, message}'
```

### 3.4 Conferindo o masking

Campos sensíveis são mascarados mesmo quando alguém os coloca no contexto por engano:

```bash
php artisan tinker --execute='\Illuminate\Support\Facades\Log::warning("teste de masking", ["password" => "123456", "card" => "4111111111111111", "payment" => ["cvv" => "999", "amount" => 10.5]]);'
tail -1 storage/logs/diagnostic-*.log | jq .context
```

Resultado:

```json
{
  "password": "[REDACTED]",
  "card": "[REDACTED]",
  "payment": { "cvv": "[REDACTED]", "amount": 10.5 }
}
```

---

## Onde olhar no código

| Arquivo | Papel |
|---|---|
| `app/Http/Middleware/CorrelationId.php` | Gera ou reaproveita o correlation ID e injeta no contexto do Monolog |
| `app/Logging/UseJsonFormatter.php` | Tap que troca o formatter por JSON e empilha os processors |
| `app/Logging/RequestContextProcessor.php` | Adiciona `type`, `user_id`, `establishment_id` e método/rota |
| `app/Logging/RedactionProcessor.php` | Mascara campos sensíveis |
| `app/Helpers/LogHelper.php` | `logDiagnostic()`, a fachada que impõe a taxonomia `event` + `component` |
| `app/Services/AuditService.php` | Grava o fato auditável no banco |
| `config/logging.php` | Canal `diagnostic` |
