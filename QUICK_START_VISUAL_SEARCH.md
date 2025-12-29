# ⚡ دليل البدء السريع - Visual Search

## 🎯 خطوات الإعداد (5 دقائق)

### الخطوة 1: تثبيت المكتبات
```bash
cd C:\xampp\htdocs\MeublesMaison
pip install -r requirements_visual_search.txt
```

### الخطوة 2: تشغيل الخدمة
```bash
python visual_search_service.py
```

**انتظر حتى ترى:**
```
✅ Service ready!
🌐 Starting server on http://0.0.0.0:5000
```

### الخطوة 3: استخراج Vectors (أول مرة فقط)
افتح في المتصفح:
```
http://localhost/MeublesMaison/api/visual_search.php?action=extract_vectors
```

### الخطوة 4: استخدام النظام
افتح في المتصفح:
```
http://localhost/MeublesMaison/visual_search.php
```

---

## ✅ التحقق من أن كل شيء يعمل

### 1. تحقق من Python Service
افتح: `http://localhost:5000/health`

يجب أن ترى:
```json
{
  "status": "ok",
  "yolo_available": true,
  "cnn_available": true,
  "faiss_available": true
}
```

### 2. تحقق من PHP API
افتح: `http://localhost/MeublesMaison/api/visual_search.php?action=health`

---

## 🔧 حل المشاكل السريع

### ❌ "Module not found"
```bash
pip install torch torchvision ultralytics faiss-cpu flask flask-cors
```

### ❌ "Port 5000 already in use"
غيّر Port في `visual_search_service.py`:
```python
app.run(host='0.0.0.0', port=5001)  # غيّر 5000 إلى 5001
```

### ❌ "No products found"
- تأكد من وجود صور في `images/`
- تحقق من قاعدة البيانات
- قم بتشغيل `/extract_all_vectors`

---

## 📝 ملاحظات

- ✅ Vectors تُحفظ تلقائياً في `vectors/`
- ✅ الصور المرفوعة تُحذف تلقائياً
- ✅ النماذج تُحمل تلقائياً عند أول استخدام
- ⚠️ أول استخراج للـ vectors قد يستغرق وقتاً (حسب عدد المنتجات)

---

## 🎉 جاهز!

الآن يمكنك:
1. فتح `visual_search.php`
2. رفع صورة
3. الحصول على نتائج دقيقة!

---

**للمزيد من التفاصيل:** راجع `VISUAL_SEARCH_SETUP.md`

