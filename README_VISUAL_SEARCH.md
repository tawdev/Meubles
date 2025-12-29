# 🔍 نظام Visual Search المتقدم - Advanced Visual Search System

## 📋 نظرة عامة

نظام بحث بصري متقدم يشبه **IKEA Visual Search** لموقع MeublesMaison. النظام يستخدم:
- ✅ **Object Detection** (YOLO) لتحديد الأثاث في الصورة
- ✅ **CNN Feature Extraction** (EfficientNet-B3) لاستخراج Visual Embeddings
- ✅ **Vector Similarity Search** (FAISS) للمقارنة السريعة
- ✅ **Category Mapping** ذكي (desk → Bureau, sofa → Salon, etc.)
- ✅ **Threshold Filtering** (0.75 minimum similarity)
- ✅ **Smart Ranking** (similarity + category + popularity)

---

## 🚀 البدء السريع / Quick Start

### 1. تثبيت المتطلبات

```bash
cd C:\xampp\htdocs\MeublesMaison
pip install -r requirements_visual_search.txt
```

### 2. تشغيل الخدمة

```bash
python visual_search_service.py
```

يجب أن ترى:
```
🚀 Starting Advanced Visual Search Service...
✅ YOLO Model loaded successfully
✅ EfficientNet-B3 Model loaded successfully
✅ Service ready!
🌐 Starting server on http://0.0.0.0:5000
```

### 3. استخراج Vectors (أول مرة)

افتح في المتصفح:
```
http://localhost/MeublesMaison/api/visual_search.php?action=extract_vectors
```

أو استخدم curl:
```bash
curl -X POST http://localhost:5000/extract_all_vectors
```

### 4. بناء FAISS Index

```
http://localhost/MeublesMaison/api/visual_search.php?action=rebuild_index
```

### 5. استخدام النظام

افتح في المتصفح:
```
http://localhost/MeublesMaison/visual_search.php
```

---

## 📁 الملفات المضافة

### Python Service
- **`visual_search_service.py`** - الخدمة الرئيسية (Object Detection + CNN + FAISS)
- **`requirements_visual_search.txt`** - Python dependencies

### PHP Files
- **`visual_search.php`** - Frontend UI للبحث البصري
- **`api/visual_search.php`** - PHP API endpoint

### Database
- **`database_visual_search.sql`** - SQL schema للـ vectors (اختياري)

### Documentation
- **`VISUAL_SEARCH_SETUP.md`** - دليل الإعداد التفصيلي
- **`README_VISUAL_SEARCH.md`** - هذا الملف

---

## 🎯 المميزات الرئيسية

### 1. Object Detection
- يحدد كل عنصر أثاث في الصورة (desk, sofa, chair, bed, etc.)
- يستخدم YOLOv8n (nano) للسرعة
- Confidence threshold: 0.25

### 2. Category Mapping
```python
desk → Bureau
sofa → Salon
bed → Chambre
dining table → Salle à manger
lamp → Décoration
```

### 3. Feature Extraction
- **Model**: EfficientNet-B3
- **Output**: 1536-dimensional vector
- **Normalization**: L2 normalized

### 4. Vector Similarity Search
- **Method**: Cosine Similarity
- **Index**: FAISS (L2 distance)
- **Threshold**: 0.75 (75% minimum similarity)

### 5. Smart Ranking
- مرتب حسب:
  1. Similarity score (أولوية)
  2. Category match (bonus)
  3. Popularity (مستقبلاً)

---

## 🔧 الإعدادات / Configuration

### تغيير Similarity Threshold

في `visual_search_service.py`:
```python
SIMILARITY_THRESHOLD = 0.75  # غيّر إلى 0.7 أو 0.8 حسب الحاجة
```

### إضافة Category Mapping جديد

```python
CATEGORY_MAPPING = {
    'new_object': 'Category Name',
    # ...
}
```

### تغيير Port

```python
app.run(host='0.0.0.0', port=5000)  # غيّر 5000
```

---

## 📊 Performance

- **Object Detection**: ~100-200ms
- **Feature Extraction**: ~50-100ms
- **Vector Search**: ~10-50ms (with FAISS)
- **Total**: ~200-400ms per search

---

## 🔍 API Endpoints

### Python Service (Port 5000)

- `GET /health` - Health check
- `POST /search` - Visual search
- `POST /rebuild_index` - Rebuild FAISS index
- `POST /extract_all_vectors` - Extract vectors for all products

### PHP API

- `GET /api/visual_search.php?action=health` - Health check
- `POST /api/visual_search.php?action=search` - Visual search (with image file)
- `POST /api/visual_search.php?action=rebuild_index` - Rebuild index
- `POST /api/visual_search.php?action=extract_vectors` - Extract vectors

---

## 🐛 حل المشاكل

### "YOLO not available"
```bash
pip install ultralytics
```

### "PyTorch not available"
```bash
pip install torch torchvision
```

### "FAISS not available"
```bash
pip install faiss-cpu
```

### "Cannot connect to Python service"
- تأكد أن `visual_search_service.py` يعمل
- تحقق من Port 5000
- تحقق من Firewall

### "No vectors found"
- قم بتشغيل `/extract_all_vectors` أولاً
- تأكد من وجود صور المنتجات في `images/`

---

## 📝 ملاحظات مهمة

1. **Vectors تُحفظ في `vectors/`** كـ `.npy` files
2. **الصور المرفوعة تُحذف تلقائياً** بعد المعالجة
3. **النظام يستخدم Cosine Similarity** للمقارنة
4. **Threshold الافتراضي: 0.75** (75%)
5. **النماذج تُحمل تلقائياً** عند أول استخدام

---

## 🔐 الأمان / Security

- ✅ تقييد حجم الملفات (10MB max)
- ✅ التحقق من نوع الملف (JPEG, PNG, WebP)
- ⚠️ **يُنصح بإضافة authentication** للـ endpoints الإدارية
- ⚠️ **استخدم HTTPS** في الإنتاج

---

## 🚀 التطوير المستقبلي

- [ ] Fine-tuning للنماذج على بيانات الأثاث
- [ ] إضافة Color Matching
- [ ] إضافة Style Matching (modern, classic, etc.)
- [ ] تحسين Category Mapping
- [ ] إضافة Multi-object detection
- [ ] GPU support optimization
- [ ] Caching improvements

---

## 📞 الدعم

إذا واجهت مشاكل:
1. تحقق من logs في Terminal
2. تحقق من `api/visual_search.php?action=health`
3. راجع الأخطاء في Console المتصفح
4. راجع `VISUAL_SEARCH_SETUP.md` للتفاصيل

---

## ✅ Checklist للإعداد

- [ ] تثبيت Python 3.8+
- [ ] تثبيت المكتبات (`pip install -r requirements_visual_search.txt`)
- [ ] تشغيل `visual_search_service.py`
- [ ] استخراج vectors (`/extract_all_vectors`)
- [ ] بناء FAISS index (`/rebuild_index`)
- [ ] اختبار البحث (`visual_search.php`)

---

**تم إنشاء النظام بواسطة AI Assistant** 🤖

**Version**: 1.0.0  
**Last Updated**: 2024

