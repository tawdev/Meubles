# دليل إعداد نظام Visual Search المتقدم
# Advanced Visual Search Setup Guide

## 📋 نظرة عامة / Overview

نظام البحث البصري المتقدم يشبه IKEA Visual Search ويشمل:
- **Object Detection** (YOLO) لتحديد الأثاث في الصورة
- **CNN Feature Extraction** (EfficientNet-B3) لاستخراج Visual Embeddings
- **Vector Similarity Search** (FAISS) للمقارنة السريعة
- **Category Mapping** ذكي (desk → Bureau, sofa → Salon, etc.)
- **Threshold Filtering** (0.75 minimum similarity)
- **Smart Ranking** (similarity + category + popularity)

---

## 🔧 المتطلبات / Requirements

### 1. Python 3.8+ 
```bash
python --version  # يجب أن يكون 3.8 أو أحدث
```

### 2. تثبيت المكتبات / Install Libraries

```bash
# الانتقال إلى مجلد المشروع
cd C:\xampp\htdocs\MeublesMaison

# تثبيت المكتبات
pip install -r requirements_visual_search.txt
```

**ملاحظة:** إذا كان لديك GPU (NVIDIA CUDA)، يمكنك استخدام:
```bash
pip install faiss-gpu  # بدلاً من faiss-cpu
```

### 3. تحميل النماذج / Download Models

النماذج ستحمل تلقائياً عند أول استخدام:
- **YOLOv8n** (Object Detection) - ~6MB
- **EfficientNet-B3** (Feature Extraction) - ~50MB

---

## 🚀 الإعداد / Setup

### الخطوة 1: إنشاء جدول Vectors في قاعدة البيانات (اختياري)

```sql
-- إنشاء جدول لتخزين معلومات الـ vectors (اختياري)
CREATE TABLE IF NOT EXISTS product_embeddings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    vector_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### الخطوة 2: تشغيل خدمة Python

```bash
# في Terminal جديد
cd C:\xampp\htdocs\MeublesMaison
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

### الخطوة 3: استخراج Vectors لجميع المنتجات (أول مرة)

افتح متصفح أو استخدم curl:

```bash
# استخراج vectors لجميع المنتجات
curl -X POST http://localhost:5000/extract_all_vectors
```

أو من PHP:
```bash
# افتح في المتصفح
http://localhost/MeublesMaison/api/visual_search.php?action=extract_vectors
```

### الخطوة 4: بناء FAISS Index

```bash
# بناء الـ index
curl -X POST http://localhost:5000/rebuild_index
```

أو من PHP:
```bash
http://localhost/MeublesMaison/api/visual_search.php?action=rebuild_index
```

---

## 📁 هيكل الملفات / File Structure

```
MeublesMaison/
├── visual_search_service.py      # Python service الرئيسي
├── requirements_visual_search.txt # Python dependencies
├── visual_search.php              # Frontend UI
├── api/
│   └── visual_search.php         # PHP API endpoint
├── uploads/                       # الصور المرفوعة مؤقتاً
├── features_cache/                # Cache للـ features القديمة
├── vectors/                       # Vectors المحفوظة (.npy files)
└── models/                        # النماذج المحملة (YOLO, etc.)
```

---

## 🎯 الاستخدام / Usage

### 1. من Frontend

افتح في المتصفح:
```
http://localhost/MeublesMaison/visual_search.php
```

1. ارفع صورة
2. اضغط "Rechercher"
3. شاهد النتائج

### 2. من API مباشرة

```bash
curl -X POST http://localhost:5000/search \
  -F "image=@path/to/image.jpg"
```

---

## 🔍 Category Mapping

النظام يربط تلقائياً بين:
- `desk`, `table` → **Bureau**
- `sofa`, `couch`, `armchair` → **Salon**
- `bed`, `mattress`, `wardrobe` → **Chambre**
- `dining table`, `dining chair` → **Salle à manger**
- `lamp`, `vase`, `mirror` → **Décoration**

يمكن تعديل الـ mapping في `visual_search_service.py`:
```python
CATEGORY_MAPPING = {
    'desk': 'Bureau',
    'sofa': 'Salon',
    # ... إضافة المزيد
}
```

---

## ⚙️ الإعدادات / Configuration

### تغيير Similarity Threshold

في `visual_search_service.py`:
```python
SIMILARITY_THRESHOLD = 0.75  # 75% minimum similarity
```

### تغيير Port

```python
app.run(host='0.0.0.0', port=5000)  # غيّر 5000 إلى أي port
```

### تغيير Database Config

في `visual_search_service.py`:
```python
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'meubles_db'
}
```

---

## 🐛 حل المشاكل / Troubleshooting

### 1. "YOLO not available"
```bash
pip install ultralytics
```

### 2. "PyTorch not available"
```bash
pip install torch torchvision
```

### 3. "FAISS not available"
```bash
pip install faiss-cpu
# أو للـ GPU:
pip install faiss-gpu
```

### 4. "Cannot connect to Python service"
- تأكد أن `visual_search_service.py` يعمل
- تحقق من Port 5000
- تحقق من Firewall

### 5. "No vectors found"
- قم بتشغيل `/extract_all_vectors` أولاً
- تأكد من وجود صور المنتجات في `images/`

---

## 📊 Performance

- **Object Detection**: ~100-200ms per image
- **Feature Extraction**: ~50-100ms per image
- **Vector Search**: ~10-50ms (with FAISS)
- **Total Search Time**: ~200-400ms

---

## 🔐 Security Notes

- أضف authentication للـ endpoints الإدارية (`rebuild_index`, `extract_vectors`)
- قم بتقييد حجم الملفات المرفوعة
- استخدم HTTPS في الإنتاج

---

## 📝 ملاحظات / Notes

- Vectors تُحفظ في `vectors/` كـ `.npy` files
- الصور المرفوعة تُحذف تلقائياً بعد المعالجة
- النظام يستخدم Cosine Similarity للمقارنة
- Threshold الافتراضي: 0.75 (75%)

---

## 🚀 التطوير المستقبلي / Future Improvements

- [ ] إضافة GPU support
- [ ] Fine-tuning للنماذج على بيانات الأثاث
- [ ] إضافة Color Matching
- [ ] إضافة Style Matching (modern, classic, etc.)
- [ ] تحسين Category Mapping
- [ ] إضافة Multi-object detection

---

## 📞 الدعم / Support

إذا واجهت مشاكل:
1. تحقق من logs في Terminal
2. تحقق من `api/visual_search.php?action=health`
3. راجع الأخطاء في Console المتصفح

---

**تم إنشاء النظام بواسطة AI Assistant** 🤖

