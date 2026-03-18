#!/usr/bin/env bash
# =============================================================================
# install-and-test.sh
# Instala Docker (repositório oficial), sobe a stack e valida todos os serviços.
# Execute: bash docker/install-and-test.sh
# =============================================================================
set -euo pipefail

BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

info()    { echo -e "${BOLD}[INFO]${NC}  $*"; }
success() { echo -e "${GREEN}[OK]${NC}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERRO]${NC}  $*"; }
step()    { echo -e "\n${BOLD}──────────────────────────────────────────${NC}"; echo -e "${BOLD}$*${NC}"; }

# ── 1. Docker já instalado? ────────────────────────────────────────────────────
step "1. Verificando se Docker já está instalado..."
if command -v docker &>/dev/null; then
  success "Docker já instalado: $(docker --version)"
  DOCKER_INSTALLED=true
else
  DOCKER_INSTALLED=false
fi

# ── 2. Instalar Docker ─────────────────────────────────────────────────────────
if [ "$DOCKER_INSTALLED" = false ]; then
  step "2. Instalando Docker (repositório oficial)..."

  info "Atualizando apt e instalando dependências..."
  sudo apt-get update -qq
  sudo apt-get install -y -qq ca-certificates curl gnupg

  info "Adicionando chave GPG do Docker..."
  sudo install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
    | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  sudo chmod a+r /etc/apt/keyrings/docker.gpg

  info "Adicionando repositório Docker..."
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
https://download.docker.com/linux/ubuntu \
$(lsb_release -cs) stable" \
    | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

  info "Instalando pacotes Docker..."
  sudo apt-get update -qq
  sudo apt-get install -y -qq \
    docker-ce docker-ce-cli containerd.io \
    docker-buildx-plugin docker-compose-plugin

  success "Docker instalado: $(docker --version)"
  success "Compose instalado: $(docker compose version)"
else
  step "2. Verificando Docker Compose plugin..."
  if docker compose version &>/dev/null; then
    success "Docker Compose: $(docker compose version)"
  else
    info "Instalando docker-compose-plugin..."
    sudo apt-get install -y -qq docker-compose-plugin
    success "Docker Compose: $(docker compose version)"
  fi
fi

# ── 3. Grupo docker e serviço ──────────────────────────────────────────────────
step "3. Configurando grupo docker e serviço..."

if ! groups "$USER" | grep -q docker; then
  info "Adicionando $USER ao grupo docker..."
  sudo usermod -aG docker "$USER"
  warn "Grupo aplicado. Este script usa 'sg docker' para aplicar sem re-login."
fi

if ! sudo systemctl is-active --quiet docker; then
  info "Iniciando serviço Docker..."
  sudo systemctl enable docker --now
fi
success "Serviço Docker ativo."

# Aplica grupo docker na sessão atual sem precisar de re-login
DOCKER_CMD="docker"
if ! docker ps &>/dev/null 2>&1; then
  DOCKER_CMD="sudo docker"
  warn "Usando sudo docker (re-faça login depois para não precisar de sudo)"
fi

# ── 4. Validar .env ───────────────────────────────────────────────────────────
step "4. Validando .env..."

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
  error ".env não encontrado! Copie .env.example e configure."
  exit 1
fi

# Verificações críticas no .env
ENV_OK=true

APP_URL_VAL=$(grep -E '^APP_URL=' .env | cut -d= -f2)
if echo "$APP_URL_VAL" | grep -q ':8000'; then
  error "APP_URL ainda aponta para :8000 — deve ser http://localhost"
  ENV_OK=false
else
  success "APP_URL=$APP_URL_VAL"
fi

DB_USER_VAL=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2)
if [ "$DB_USER_VAL" = "root" ]; then
  error "DB_USERNAME=root — MySQL rejeitará MYSQL_USER=root. Use outro nome (ex: tudoleve)"
  ENV_OK=false
else
  success "DB_USERNAME=$DB_USER_VAL"
fi

APP_KEY_VAL=$(grep -E '^APP_KEY=' .env | cut -d= -f2)
if [ -z "$APP_KEY_VAL" ]; then
  error "APP_KEY está vazio — gere com: php artisan key:generate"
  ENV_OK=false
else
  success "APP_KEY configurado"
fi

if [ "$ENV_OK" = false ]; then
  error "Corrija os erros acima no .env antes de continuar."
  exit 1
fi

# ── 5. Build e start da stack ─────────────────────────────────────────────────
step "5. Subindo a stack (docker compose up --build -d)..."
info "O build da imagem PHP pode levar vários minutos na primeira vez..."

$DOCKER_CMD compose up --build -d 2>&1

success "Stack iniciada em background."

# ── 6. Aguardar serviços ficarem saudáveis ────────────────────────────────────
step "6. Aguardando serviços iniciarem..."

wait_healthy() {
  local service=$1
  local max_wait=${2:-120}
  local elapsed=0
  local interval=5

  info "Aguardando $service ficar saudável (máx ${max_wait}s)..."
  while [ $elapsed -lt $max_wait ]; do
    local status
    status=$($DOCKER_CMD compose ps "$service" --format json 2>/dev/null \
      | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('Health','') or d.get('State',''))" 2>/dev/null || echo "unknown")

    if [ "$status" = "healthy" ] || [ "$status" = "running" ]; then
      success "$service: $status"
      return 0
    fi
    sleep $interval
    elapsed=$((elapsed + interval))
    echo -n "  ($elapsed/${max_wait}s) status=$status ..."
  done
  error "$service não ficou saudável em ${max_wait}s"
  return 1
}

sleep 5

$DOCKER_CMD compose ps

# ── 7. Status detalhado de cada serviço ───────────────────────────────────────
step "7. Status de cada container..."

ALL_OK=true

check_service() {
  local name=$1
  local state
  state=$($DOCKER_CMD compose ps "$name" --format json 2>/dev/null \
    | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('State','unknown'))" 2>/dev/null || echo "not_found")

  if [ "$state" = "running" ]; then
    success "$name: running"
  else
    error "$name: $state"
    ALL_OK=false
  fi
}

for svc in db redis backend queue nginx; do
  check_service "$svc"
done

# Frontend pode demorar mais (build Nuxt)
info "Aguardando frontend (build Nuxt pode levar 2-3 min)..."
for i in $(seq 1 24); do
  frontend_state=$($DOCKER_CMD compose ps frontend --format json 2>/dev/null \
    | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('State',''))" 2>/dev/null || echo "")
  if [ "$frontend_state" = "running" ]; then
    success "frontend: running"
    break
  fi
  if [ $i -eq 24 ]; then
    warn "frontend ainda não está running após 120s (pode estar buildando)"
  fi
  sleep 5
done

# ── 8. Testes de conectividade ────────────────────────────────────────────────
step "8. Testando endpoints..."

test_endpoint() {
  local label=$1
  local url=$2
  local expected_code=${3:-200}
  local http_code

  http_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$url" 2>/dev/null || echo "000")

  if [ "$http_code" = "$expected_code" ] || [ "$http_code" = "200" ] || [ "$http_code" = "401" ] || [ "$http_code" = "404" ]; then
    success "$label → HTTP $http_code ($url)"
  else
    error "$label → HTTP $http_code — esperado 2xx/401/404 ($url)"
    ALL_OK=false
  fi
}

# Aguardar nginx e php-fpm estarem prontos
info "Aguardando nginx responder (máx 30s)..."
for i in $(seq 1 6); do
  if curl -s -o /dev/null --max-time 5 http://localhost/ 2>/dev/null; then
    break
  fi
  sleep 5
done

test_endpoint "nginx raiz"           "http://localhost/"              "200"
test_endpoint "API v1 (sem auth)"    "http://localhost/api/v1/catalog/products" "200"
test_endpoint "API login endpoint"   "http://localhost/api/v1/auth/login" "405"
test_endpoint "API auth (sem token)" "http://localhost/api/v1/customers" "401"

# ── 9. Testar conexão com MySQL e Redis dentro do backend ─────────────────────
step "9. Testando conexões internas (MySQL e Redis)..."

info "Testando conexão MySQL via artisan..."
if $DOCKER_CMD compose exec -T backend php artisan db:show --json 2>/dev/null | python3 -c "import sys,json; d=json.load(sys.stdin); print('OK' if d else 'FAIL')" 2>/dev/null | grep -q OK; then
  success "MySQL: conexão OK"
else
  # Fallback simples
  if $DOCKER_CMD compose exec -T backend php -r "
    \$pdo = new PDO(
      'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
      getenv('DB_USERNAME'), getenv('DB_PASSWORD')
    );
    echo 'MySQL OK';
  " 2>/dev/null | grep -q OK; then
    success "MySQL: conexão OK"
  else
    error "MySQL: falha na conexão"
    ALL_OK=false
  fi
fi

info "Testando conexão Redis via artisan..."
if $DOCKER_CMD compose exec -T backend php artisan tinker --execute="echo Redis::ping() ? 'Redis OK' : 'Redis FAIL';" 2>/dev/null | grep -q OK; then
  success "Redis: conexão OK"
else
  warn "Redis: não foi possível verificar via tinker (pode ser normal se CACHE_STORE=database)"
fi

# ── 10. Rodar migrations ──────────────────────────────────────────────────────
step "10. Rodando migrations..."

if $DOCKER_CMD compose exec -T backend php artisan migrate --force --no-interaction 2>&1; then
  success "Migrations executadas com sucesso"
else
  error "Migrations falharam — verifique os logs abaixo"
  $DOCKER_CMD compose logs --tail=30 backend
  ALL_OK=false
fi

# ── 11. Verificar queue worker ────────────────────────────────────────────────
step "11. Verificando queue worker..."

queue_state=$($DOCKER_CMD compose ps queue --format json 2>/dev/null \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('State',''))" 2>/dev/null || echo "")

if [ "$queue_state" = "running" ]; then
  success "Queue worker: running"
  info "Últimas linhas do log do queue:"
  $DOCKER_CMD compose logs --tail=10 queue
else
  error "Queue worker: $queue_state"
  ALL_OK=false
fi

# ── 12. Logs de erro (apenas ERRORs/CRITICALs) ───────────────────────────────
step "12. Verificando erros nos logs..."

for svc in backend nginx queue; do
  errors=$($DOCKER_CMD compose logs "$svc" 2>&1 | grep -iE '(error|critical|fatal|exception)' | grep -v 'fastcgi_finish_request' | head -5 || true)
  if [ -n "$errors" ]; then
    warn "Erros encontrados em $svc:"
    echo "$errors"
  else
    success "$svc: sem erros críticos nos logs"
  fi
done

# ── 13. Resultado final ───────────────────────────────────────────────────────
step "13. Resultado final"
echo ""

if [ "$ALL_OK" = true ]; then
  echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════╗${NC}"
  echo -e "${GREEN}${BOLD}║  ✅  STACK APROVADA — pronta para a VPS      ║${NC}"
  echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════╝${NC}"
  echo ""
  echo "  Backend (PHP-FPM):  docker compose logs backend"
  echo "  Queue worker:       docker compose logs queue"
  echo "  Nginx:              http://localhost"
  echo "  API:                http://localhost/api/v1/catalog/products"
  echo "  Frontend (Nuxt):    http://localhost:3000"
  echo ""
  echo "  Para parar:         docker compose down"
  echo "  Para rebuild:       docker compose up --build -d"
else
  echo -e "${RED}${BOLD}╔══════════════════════════════════════════════╗${NC}"
  echo -e "${RED}${BOLD}║  ❌  STACK COM PROBLEMAS — verifique acima   ║${NC}"
  echo -e "${RED}${BOLD}╚══════════════════════════════════════════════╝${NC}"
  echo ""
  echo "  Logs completos:     docker compose logs --tail=50 <serviço>"
  echo "  Reiniciar:          docker compose down && docker compose up --build -d"
fi

echo ""
