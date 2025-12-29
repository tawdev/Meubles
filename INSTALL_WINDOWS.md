# 🔧 دليل التثبيت على Windows - Installation Guide for Windows

## ⚠️ ملاحظة مهمة / Important Note

إذا كنت تستخدم **Python 3.14** (أو أحدث)، قد تواجه مشاكل مع بعض المكتبات التي تحاول بناء numpy من المصدر.

## ✅ الحل السريع / Quick Solution

استخدم `python -m pip` بدلاً من `pip` مباشرة:

```powershell
# في PowerShell
python -m pip install opencv-python --no-deps
python -m pip install torch torchvision --no-deps
python -m pip install ultralytics --no-deps
python -m pip install faiss-cpu matplotlib pyyaml requests psutil polars ultralytics-thop
```

## 📋 خطوات التثبيت الكاملة / Full Installation Steps

### 1. التحقق من Python
```powershell
python --version
# يجب أن يكون 3.8 أو أحدث
```

### 2. تثبيت المكتبات الأساسية
```powershell
python -m pip install flask flask-cors mysql-connector-python
```

### 3. تثبيت OpenCV
```powershell
python -m pip install opencv-python --no-deps
```

### 4. تثبيت PyTorch و TorchVision
```powershell
python -m pip install torch torchvision --no-deps
```

### 5. تثبيت Ultralytics
```powershell
python -m pip install ultralytics --no-deps
```

### 6. تثبيت باقي المكتبات
```powershell
python -m pip install faiss-cpu matplotlib pyyaml requests psutil polars ultralytics-thop
```

### 7. التحقق من التثبيت
```powershell
python -c "import torch; import torchvision; import ultralytics; import faiss; import cv2; print('✅ All libraries installed!')"
```

## 🐛 حل المشاكل / Troubleshooting

### المشكلة: "pip is not recognized"
**الحل:** استخدم `python -m pip` بدلاً من `pip`

### المشكلة: "Unknown compiler" عند تثبيت numpy
**الحل:** 
- numpy 2.4.0 مثبت بالفعل مع Python 3.14
- استخدم `--no-deps` لتجنب إعادة تثبيت numpy

### المشكلة: "Module not found" بعد التثبيت
**الحل:** 
```powershell
python -m pip install <module_name>
```

## 📝 ملاحظات / Notes

- ✅ numpy 2.4.0 يعمل مع Python 3.14
- ✅ لا حاجة لتثبيت Visual Studio Build Tools
- ✅ استخدم `--no-deps` لتجنب مشاكل numpy
- ⚠️ بعض المكتبات قد تحتاج dependencies إضافية

## 🚀 بعد التثبيت

بعد تثبيت جميع المكتبات:

1. **تشغيل الخدمة:**
```powershell
python visual_search_service.py
```

2. **استخراج Vectors:**
```
http://localhost/MeublesMaison/api/visual_search.php?action=extract_vectors
```

3. **استخدام النظام:**
```
http://localhost/MeublesMaison/visual_search.php
```

---

**تم إنشاء هذا الدليل لحل مشاكل Python 3.14 على Windows** 🪟

