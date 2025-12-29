# 🔧 حل مشاكل Visual Search

## ❌ المشكلة: "0 produit(s) trouvé(s)" رغم اكتشاف الأثاث

### ✅ الحلول:

#### 1. تأكد من استخراج Vectors للمنتجات

**هذا هو السبب الأكثر شيوعاً!**

افتح في المتصفح:
```
http://localhost/MeublesMaison/api/visual_search.php?action=extract_vectors
```

أو من صفحة الإعداد:
```
http://localhost/MeublesMaison/admin/visual_search_setup.php
```

**انتظر حتى يكتمل** - قد يستغرق وقتاً حسب عدد المنتجات.

#### 2. تأكد من وجود صور للمنتجات

تحقق من:
- المنتجات في قاعدة البيانات لها `image` path صحيح
- الصور موجودة في مجلد `images/`
- المسارات صحيحة (مثلاً: `images/product1.jpg`)

#### 3. Threshold تم خفضه

تم خفض threshold من **0.75** إلى **0.6** (60%) لنتائج أفضل.

يمكن تعديله في `visual_search_service.py`:
```python
SIMILARITY_THRESHOLD = 0.6  # غيّر حسب الحاجة
```

#### 4. Category Filter أصبح أكثر مرونة

- إذا لم يجد نتائج مع category filter، سيحاول بدون filter
- Category matching أصبح case-insensitive

#### 5. تحقق من Logs

افتح Terminal حيث يعمل `visual_search_service.py` وسترى:
```
🔍 Searching in X products...
📂 Category filter: Salon
📊 Search stats: checked=X, with_vectors=Y, below_threshold=Z, results=N
```

---

## 🔍 خطوات التشخيص

### الخطوة 1: تحقق من Python Service
```
http://localhost:5000/health
```

يجب أن ترى:
```json
{
  "status": "ok",
  "yolo_available": true,
  "cnn_available": true,
  "faiss_available": true
}
```

### الخطوة 2: تحقق من عدد المنتجات
افتح Terminal Python:
```python
python
>>> from visual_search_service import get_all_products
>>> products = get_all_products()
>>> print(f"Total products: {len(products)}")
```

### الخطوة 3: تحقق من Vectors
```python
>>> import os
>>> vectors_dir = "vectors"
>>> vectors = [f for f in os.listdir(vectors_dir) if f.endswith('.npy')]
>>> print(f"Vectors cached: {len(vectors)}")
```

### الخطوة 4: تحقق من Category Names
تأكد أن أسماء الفئات في قاعدة البيانات تطابق:
- `Salon` (وليس "salon" أو "Salons")
- `Chambre` (وليس "chambre")
- `Bureau` (وليس "bureau")
- `Salle à manger`
- `Décoration`

---

## 🐛 مشاكل شائعة أخرى

### "No vectors to index"
**الحل:** قم بتشغيل `/extract_all_vectors` أولاً

### "Image file not found"
**الحل:** تحقق من مسارات الصور في قاعدة البيانات

### "Category mismatch"
**الحل:** تحقق من أسماء الفئات في قاعدة البيانات

### "Similarity too low"
**الحل:** خفض threshold في `visual_search_service.py`

---

## 📝 Checklist

- [ ] Python service يعمل (`/health`)
- [ ] تم استخراج vectors (`/extract_vectors`)
- [ ] المنتجات لها صور في قاعدة البيانات
- [ ] الصور موجودة في مجلد `images/`
- [ ] أسماء الفئات صحيحة (Salon, Chambre, etc.)
- [ ] Threshold مناسب (0.6 أو أقل للاختبار)

---

## 💡 نصائح

1. **للاختبار:** خفض threshold إلى 0.5 مؤقتاً
2. **للإنتاج:** استخدم threshold 0.65-0.7
3. **للنتائج الممتازة فقط:** استخدم threshold 0.75

---

**إذا استمرت المشكلة، تحقق من logs في Terminal!**

