# 🚀 Déploiement de l'API PDF Extraction sur VPS

## Architecture

L'algorithme Python est maintenant une API REST indépendante, hébergée sur un VPS séparé:

```
Laravel App (Server A) ──HTTP──> FastAPI Server (VPS - Server B)
```

## 📋 Prérequis VPS

- **OS:** Ubuntu 20.04 LTS ou supérieur
- **CPU:** 2+ cores recommandé
- **RAM:** 4GB minimum
- **Docker:** Installé et configuré
- **Ports:** 8001 disponible

## 🐳 Déploiement avec Docker (Recommandé)

### 1. Sur le VPS, cloner le repo et naviguer au dossier Python

```bash
cd /opt/irma-learning
git clone <repo-url> .
cd resources/python
```

### 2. Builder l'image Docker

```bash
docker build -t irma-pdf-api:latest .
```

### 3. Lancer le container

```bash
docker run -d \
  --name irma-pdf-api \
  --restart always \
  -p 8001:8001 \
  -e WORKERS=4 \
  irma-pdf-api:latest
```

### 4. Vérifier que l'API fonctionne

```bash
curl http://localhost:8001/health
# Réponse: {"status":"ok","service":"PDF Extraction API"}
```

## 🔧 Configuration Laravel

### 1. Mettre à jour `config/learning.php`

```php
'pdf_extraction' => [
    'api_url' => env('PDF_EXTRACTION_API_URL', 'http://localhost:8001'),
    'timeout' => (int) env('PDF_EXTRACTION_TIMEOUT', 900),
    'max_pages' => (int) env('PDF_EXTRACTION_MAX_PAGES', 0),
    // ... autres configs
],
```

### 2. Ajouter `.env`

```bash
PDF_EXTRACTION_API_URL=http://your-vps-ip:8001
PDF_EXTRACTION_TIMEOUT=900
```

### 3. Changer le service utilisé dans `ExtractChapterPdf` job

```php
// app/Jobs/ExtractChapterPdf.php
use App\Services\RemotePdfExtractionService;

public function handle(
    RemotePdfExtractionService $extractionService,  // ← Change ici
    ReadingDurationCalculatorService $durationService,
): void {
    // Le reste du code reste identique
    $result = $extractionService->extract(
        Storage::disk('public')->path($this->mediaPath),
        $assetDirectory,
    );
}
```

## 📊 Monitoring et Logs

### Vérifier les logs du container

```bash
docker logs -f irma-pdf-api
```

### Vérifier la santé de l'API

```bash
curl http://your-vps-ip:8001/health
curl http://your-vps-ip:8001/job/{job_id}
```

## 🔐 Sécurité en Production

### 1. Ajouter Nginx comme reverse proxy

```nginx
server {
    listen 8001;
    server_name _;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_read_timeout 900s;
    }
}
```

### 2. Limiter l'accès à l'API (optionnel)

```nginx
location /extract {
    allow 192.168.1.0/24;  # Votre serveur Laravel
    deny all;
}
```

### 3. Ajouter SSL/TLS

```bash
certbot certonly --standalone -d api.yourdomain.com
```

## 🚀 Scaling (Optionnel)

Pour traiter plusieurs PDFs en parallèle:

### 1. Utiliser Celery + Redis

```bash
# Installer Redis sur le VPS
apt-get install redis-server

# Modifier pdf_extraction_api.py pour utiliser Celery
# Lancer les workers Celery
celery -A pdf_extraction_api worker --loglevel=info
```

### 2. Docker Compose pour orchestration

```yaml
version: '3.8'
services:
  pdf-api:
    build: .
    ports:
      - "8001:8001"
    environment:
      - WORKERS=4
    restart: always

  redis:
    image: redis:latest
    ports:
      - "6379:6379"
    restart: always
```

## 📈 Performances

### Benchmarks (sur un serveur 4-core, 8GB RAM)

| Taille PDF | Temps d'extraction |
|------------|-------------------|
| 50 pages   | ~5 secondes       |
| 200 pages  | ~15 secondes      |
| 395 pages  | ~30 secondes      |

### Optimisations possibles

- Augmenter `--parallel` (nombre de workers)
- Augmenter `--batch-size` (pages par lot)
- Ajouter plus de RAM au VPS
- Utiliser SSD pour les fichiers temporaires

## 🆘 Troubleshooting

### API ne répond pas

```bash
# Vérifier le container
docker ps | grep irma-pdf-api

# Redémarrer
docker restart irma-pdf-api

# Vérifier les logs
docker logs irma-pdf-api
```

### Timeout d'extraction

```bash
# Augmenter le timeout Laravel
PDF_EXTRACTION_TIMEOUT=1200

# Vérifier les ressources du VPS
free -h
df -h
```

### Erreurs de dépendances

```bash
# Reconstruire l'image
docker build --no-cache -t irma-pdf-api:latest .
docker restart irma-pdf-api
```

## 🔄 Mise à jour de l'API

```bash
cd /opt/irma-learning
git pull origin main
docker build -t irma-pdf-api:latest .
docker rm -f irma-pdf-api
docker run -d --name irma-pdf-api --restart always -p 8001:8001 irma-pdf-api:latest
```

## 📝 Notes

- L'API est **stateless** - peut être déployée sur plusieurs serveurs
- Utilisez un load balancer (Nginx, HAProxy) pour distribuer les requêtes
- Stockez les fichiers temporaires sur `/tmp` (avec nettoyage régulier)
- Surveillez l'utilisation CPU/RAM - augmentez les ressources si nécessaire
