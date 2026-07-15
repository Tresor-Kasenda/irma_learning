# 🏗️ Architecture API - Communication Laravel ↔ Python

## Vue d'ensemble

```
┌─────────────────────────────────────────────────────────────┐
│                    Your Main Application                     │
│                      Laravel Server                          │
├─────────────────────────────────────────────────────────────┤
│  ExtractChapterPdf Job                                      │
│  └─> RemotePdfExtractionService                             │
│      └─> HTTP POST http://vps-ip:8001/extract               │
│          with PDF file                                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       │ HTTP/REST
                       │
┌──────────────────────▼──────────────────────────────────────┐
│                    VPS Server                                │
│              FastAPI Python Application                      │
├─────────────────────────────────────────────────────────────┤
│  POST /extract                                               │
│  ├─> Receive PDF file                                       │
│  ├─> Process with pdf_to_markdown algorithm                 │
│  ├─> Extract markdown & generate cover image                │
│  └─> Return JSON response                                   │
│                                                              │
│  GET /health  (monitoring)                                  │
│  GET /job/{job_id}  (status check)                          │
└─────────────────────────────────────────────────────────────┘
```

## 📡 Communication Flow

### 1️⃣ Laravel envoie la requête

```php
// app/Jobs/ExtractChapterPdf.php
$extractionService = app(RemotePdfExtractionService::class);

$result = $extractionService->extract(
    Storage::disk('public')->path($this->mediaPath),  // PDF file path
    $assetDirectory  // Output directory
);
```

### 2️⃣ Service Laravel fait une requête HTTP

```php
// app/Services/RemotePdfExtractionService.php
Http::timeout(900)
    ->attach('file', fopen($pdfPath, 'r'), basename($pdfPath))
    ->post('http://vps-ip:8001/extract', [
        'max_pages' => 0,
        'batch_size' => 50,
        'parallel' => 4,
        'ocr_language' => 'fra+eng',
    ]);
```

### 3️⃣ API FastAPI reçoit et traite

```python
# resources/python/pdf_extraction_api.py
@app.post("/extract")
async def extract_pdf(
    file: UploadFile = File(...),
    max_pages: int = 0,
    batch_size: int = 50,
    ...
):
    # Process PDF with pdf_to_markdown
    result = extract_document(args)
    
    # Return JSON response
    return {
        "status": "completed",
        "markdown": "...",
        "cover_file": "cover.png",
        "page_count": 395,
        ...
    }
```

### 4️⃣ Laravel reçoit et utilise le résultat

```php
$response->json(); // Récupère le JSON
// {
//   "status": "completed",
//   "markdown": "# Document Content...",
//   "page_count": 395,
//   "warnings": [],
//   ...
// }

// Sauvegarde dans la DB
$chapter->update([
    'content' => $result['markdown'],
    'cover_image' => $assetDirectory . '/' . $result['cover_file'],
    ...
]);
```

## 🔧 Configuration requise

### Laravel (.env)

```env
PDF_EXTRACTION_API_URL=http://192.168.1.100:8001
PDF_EXTRACTION_TIMEOUT=900
PDF_EXTRACTION_MAX_PAGES=0
PDF_EXTRACTION_BATCH_SIZE=50
```

### VPS (Déploiement Docker)

```bash
cd /opt/irma-learning/resources/python

# Builder l'image
docker build -t irma-pdf-api:latest .

# Lancer le service
docker run -d \
  --name irma-pdf-api \
  --restart always \
  -p 8001:8001 \
  irma-pdf-api:latest
```

## 📊 Avantages de cette architecture

| Aspect | Bénéfice |
|--------|----------|
| **Scalabilité** | Déployer plusieurs instances de l'API sur différents VPS |
| **Flexibilité** | Mettre à jour l'algo Python sans toucher à Laravel |
| **Indépendance** | Chaque service peut être déployé/redémarré indépendamment |
| **Réutilisabilité** | L'API peut être utilisée par d'autres applications |
| **Monitoring** | Monitorez chaque composant séparément |
| **Performance** | Pas de blocage du serveur principal pendant l'extraction |

## 🔄 Processus d'extraction pas à pas

```
1. Utilisateur upload un PDF via l'admin
   ↓
2. Laravel crée un job ExtractChapterPdf
   ↓
3. Job appelle RemotePdfExtractionService
   ↓
4. Service envoie le PDF au serveur VPS (HTTP POST)
   ↓
5. API FastAPI reçoit le fichier
   ↓
6. Algorithme pdf_to_markdown:
   - Analyse le PDF (395 pages)
   - Génère l'image de couverture (page 1)
   - Extrait le contenu textuel complet
   - Nettoie et formate en Markdown
   ↓
7. API retourne JSON avec:
   - Contenu Markdown complet
   - Chemin vers l'image de couverture
   - Nombre de pages
   - Avertissements (si besoin d'OCR, etc)
   ↓
8. Laravel reçoit et sauvegarde dans la DB
   ↓
9. Chapter est marquée comme "processing_completed"
```

## 🚀 Scaling - Déployer plusieurs instances

```bash
# VPS 1
docker run -d --name api-1 -p 8001:8001 irma-pdf-api:latest

# VPS 2
docker run -d --name api-2 -p 8001:8001 irma-pdf-api:latest

# VPS 3
docker run -d --name api-3 -p 8001:8001 irma-pdf-api:latest

# Puis utiliser Nginx load balancer côté Laravel
upstream pdf_extraction_api {
    server 192.168.1.100:8001;
    server 192.168.1.101:8001;
    server 192.168.1.102:8001;
}
```

## 🆘 Dépannage

### L'API ne répond pas

```bash
# Vérifier
curl http://your-vps-ip:8001/health

# Logs
docker logs irma-pdf-api

# Redémarrer
docker restart irma-pdf-api
```

### Timeout d'extraction

```bash
# Augmenter le timeout dans .env
PDF_EXTRACTION_TIMEOUT=1200

# Ou vérifier que l'API a assez de ressources
docker stats irma-pdf-api
```

## 📝 Configuration complète

Voir **DEPLOYMENT.md** pour:
- Instructions d'installation complètes
- Configuration Nginx/SSL
- Monitoring et logs
- Scaling horizontal
- Troubleshooting avancé
