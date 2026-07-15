# 📘 Guide: Comment l'algorithme traite Texte + Images dans les PDFs

## Vue d'ensemble du processus

```
PDF Input (Texte + Images)
        ↓
[Phase 1: Analyse & Classification]
  ├─ Détect type document (native, scanned, hybrid, complex)
  ├─ Analyse couverture (page 1)
  └─ Profil chaque page
        ↓
[Phase 2: Extraction de contenu]
  ├─ Texte: pymupdf4llm.to_markdown()
  ├─ Images: Détection & référencement
  └─ OCR: Si page scannée
        ↓
[Phase 3: Post-processing]
  ├─ Nettoyage Markdown
  ├─ Détection code & équations
  └─ Génération couverture (page 1)
        ↓
Output: Markdown + Cover Image + Métadonnées
```

## 🔍 Phase 1: Analyse Intelligente des Pages

### Profil de chaque page

```python
# Code: inspect_pdf() ligne 229-248
for page in doc:
    text_length = len(page.get_text('text').strip())
    image_count = len(page.get_images(full=True))
    drawing_count = len(page.get_drawings())
    image_coverage = page_image_coverage(page)
    
    is_scanned = (text_length < 40 AND image_coverage >= 0.5)
    is_complex = (image_coverage >= 0.08 OR drawing_count >= 8)
```

### Classification du document

Le système classe le PDF en fonction des ratios:

| Type | Caractéristiques | Stratégie |
|------|------------------|-----------|
| **Native** | 80%+ natif, peu d'images | `text-first` - Priorité au texte |
| **Scanned** | 80%+ scanned/images | `ocr-first` - OCR pour tout |
| **Hybrid** | Mix 50/50 texte & images | `mixed-text-ocr` - Mix adaptatif |
| **Complex** | 35%+ complexité visuelle | `layout-aware` - Préserve layouts |

## 📝 Phase 2: Extraction Adaptative

### Cas 1: Document avec texte natif + images

```python
# Traitement: pymupdf4llm.to_markdown()
chunks = pymupdf4llm.to_markdown(
    batch_doc,
    page_chunks=True,
    write_images=False,  # Images non sauvegardées (priorité texte)
    force_text=True,     # Force extraction texte
    image_path=str(output_dir),
    dpi=args.image_dpi
)

# Résultat pour une page avec texte + images:
# 
# # Titre
# 
# Voici le texte du document...
# ![Image](path/to/image.png)  ← Référence image en Markdown
# 
# Plus de texte...
```

**Exemple réel:** LinkedIn Posts PDF
- Page avec texte (titre, description)
- Images (screenshots de posts)
- Résultat: Texte en Markdown + références images

### Cas 2: Document scanné (texte dans images)

```python
# Si page est "scanned" (peu de texte natif):
if is_scanned:
    ocr_text, success, method = try_ocr(page, language='fra+eng')
    if success:
        text = f'{extracted_text}\n\n{ocr_text}'.strip()
        # Combine texte extrait + OCR
```

### Cas 3: Pages complexes (layouts, tableaux, schémas)

```python
# Détection complexité
is_complex = (
    image_coverage >= 0.08  # 8%+ en images
    OR drawing_count >= 8   # 8+ dessins/lignes
)

# Stratégie: Layout-aware extraction
# Préserve structure visuelle via pymupdf4llm
```

## 🎨 Traitement des images

### Extraction d'images

```python
# pymupdf4llm détecte et référence les images:
# 1. Détecte images dans page
# 2. Les extrait en PNG
# 3. Génère références Markdown
# 4. Insère dans texte aux bonnes positions

# Résultat dans Markdown:
# ![Description](assets/image-001.png)
```

### Image de couverture (Cover)

```python
# Spécification: SEULEMENT première page
cover_page = doc[0]  # Page 1
render_page(
    cover_page,
    output_dir / 'cover.png',
    dpi=110  # visual_dpi
)
```

## 📊 Exemple: Linkedin_Posts_2024_Blue.pdf

Ce PDF contient:
- **Titre & texte**: Posts LinkedIn
- **Images**: Screenshots des posts
- **Type détecté**: Probablement `hybrid` (mix texte natif + images)

### Traitement pour ce PDF:

```
Page 1 (Couverture)
├─ Rendu en cover.png
└─ Texte extrait + référence images

Pages 2-N (Posts)
├─ Texte natif: "Titre du post..."
├─ Images: Références markdown ![...](assets/img-XXX.png)
├─ Texte additionnel: description, commentaires
└─ Résultat: Markdown avec images intégrées
```

### Output final pour ce type:

```markdown
# LinkedIn Posts Collection 2024

## Post 1
**Titre du post**

Contenu du post...

![Screenshot du post](assets/linkedin-001.png)

Réactions: 1.2K likes, 234 comments

## Post 2
...
```

## 🚨 Gestion des cas limites

### Cas: Image sans texte associé

```python
# L'algorithme ajoute une référence même sans texte:
# ![Image](assets/image.png)
# 
# (L'utilisateur peut voir l'image via la référence)
```

### Cas: Texte dans images (scanned)

```python
# Détecté comme "scanned"
# → OCR appliqué automatiquement
# → Texte extrait des images via Tesseract

warnings.append(
    'Pages 45, 67, 89 sont scannées et ont nécessité OCR'
)
```

### Cas: Tableau visuel complexe

```python
# Si tableau est une image (pas de texte natif):
# 1. Reconnu comme "complex"
# 2. Layout-aware strategy appliquée
# 3. Préserve structure du tableau
# 4. Image sauvegardée en référence

# Résultat:
# [Tableau complexe - voir image ci-dessous]
# ![Tableau](assets/tableau-001.png)
```

## 📈 Statistiques & Métadonnées

L'algorithme retourne:

```json
{
  "document_type": "hybrid",           // Type détecté
  "extraction_strategy": "mixed-text-ocr",
  "page_count": 504,
  "word_count": 12345,
  "image_count": 142,                 // Nombre images extraites
  "visual_pages": [1],                // Pages rendues en images
  "ocr_pages": [45, 67, 89],         // Pages ayant besoin OCR
  "ocr_required_pages": [],           // Pages impossible OCR
  "warnings": [
    "142 images extraites du document",
    "3 pages ont nécessité OCR supplémentaire"
  ]
}
```

## 🎯 Points clés pour LinkedIn_Posts_2024_Blue.pdf

1. **Texte**: Titres, descriptions, commentaires → Markdown
2. **Images**: Screenshots de posts → Références ![...](assets/...)
3. **Couverture**: Page 1 rendue en PNG
4. **Stratégie**: `mixed-text-ocr` (texte natif + images)
5. **Output**: Markdown complet + 1 cover image

## 💡 Configuration pour ce type de PDF

```python
# Recommandations pour Mixed Text-Image PDFs:
--max-pages 0              # Traiter toutes pages
--batch-size 50            # Lots de 50 pages
--parallel 4               # 4 workers
--image-dpi 144            # Qualité images
--visual-dpi 110           # Qualité couverture
--ocr-language fra+eng     # OCR si besoin
```

## 🔧 Optimisations appliquées

| Optimisation | Bénéfice |
|--------------|----------|
| Batch processing | Traite 500 pages en ~30-60 sec |
| Parallel OCR | 4x plus rapide que séquentiel |
| Write_images=False | Sauve RAM, texte prioritaire |
| Layout awareness | Préserve structure visuellement |
| Hybrid strategy | Optimal pour mix texte/images |

## ⚠️ Limitations & Fallbacks

```python
# Si images corrompues ou invalides:
→ Référence Markdown vide, warning ajouté

# Si OCR échoue:
→ Texte original conservé, page marquée en warning

# Si page trop complexe:
→ Cover image générée, contenu texte extrait

# Si pas de couverture possible:
→ Fallback à première page valide
```

