# 🔌 API Examples - نظام تتبع المشاعر

## 📡 Endpoints المتاحة:

### 1. عرض صفحة اختيار المشاعر
```http
GET /mood/{patientId}
```

**مثال:**
```
http://localhost:8000/mood/5
```

**Response:** HTML page مع 3 أزرار


---

### 2. حفظ المشاعر
```http
POST /mood/save
Content-Type: application/x-www-form-urlencoded
```

**Parameters:**
- `patient_id`: integer (required)
- `mood`: string (required) - "happy" | "neutral" | "sad"

**مثال cURL:**
```bash
curl -X POST http://localhost:8000/mood/save \
  -d "patient_id=5" \
  -d "mood=happy"
```

**مثال JavaScript:**
```javascript
fetch('http://localhost:8000/mood/save', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
  body: 'patient_id=5&mood=happy'
})
```

**Response:** HTML success page


---

### 3. Dashboard - الإحصائيات
```http
GET /mood/dashboard
```

**مثال:**
```
http://localhost:8000/mood/dashboard
```

**Response:** HTML page مع:
- إحصائيات المشاعر
- آخر 50 تسجيل


---

### 4. QR Codes
```http
GET /mood/qr-codes
```

**مثال:**
```
http://localhost:8000/mood/qr-codes
```

**Response:** HTML page مع QR codes لكل المرضى


---

### 5. صفحة الاختبار
```http
GET /mood/test
```

**مثال:**
```
http://localhost:8000/mood/test
```

**Response:** HTML page للاختبار


---

## 🔍 Repository Methods:

### MoodTrackingRepository

#### 1. findByPatientLastDays
```php
// الحصول على مشاعر مريض في آخر X أيام
$moods = $moodRepo->findByPatientLastDays(
    patientId: 5,
    days: 7
);

// Returns: MoodTracking[]
```

**مثال:**
```php
// آخر 7 أيام للمريض رقم 5
$moods = $moodRepo->findByPatientLastDays(5, 7);

foreach ($moods as $mood) {
    echo $mood->getMood(); // happy, neutral, sad
    echo $mood->getCreatedAt()->format('Y-m-d');
}
```


#### 2. getMoodStats
```php
// إحصائيات عامة لكل المشاعر
$stats = $moodRepo->getMoodStats();

// Returns: [
//   ['mood' => 'happy', 'count' => 15],
//   ['mood' => 'neutral', 'count' => 8],
//   ['mood' => 'sad', 'count' => 3]
// ]
```

**مثال:**
```php
$stats = $moodRepo->getMoodStats();

foreach ($stats as $stat) {
    echo "{$stat['mood']}: {$stat['count']}";
}
```


---

## 💾 Database Queries:

### إضافة تسجيل جديد
```php
$mood = new MoodTracking();
$mood->setPatient($patient);
$mood->setMood('happy');

$em->persist($mood);
$em->flush();
```


### الحصول على آخر 10 تسجيلات
```php
$recentMoods = $moodRepo->findBy(
    [],
    ['createdAt' => 'DESC'],
    10
);
```


### الحصول على تسجيلات مريض معين
```php
$patientMoods = $moodRepo->findBy(
    ['patient' => $patient],
    ['createdAt' => 'DESC']
);
```


### عد المشاعر السعيدة
```php
$happyCount = $moodRepo->count(['mood' => 'happy']);
```


---

## 🎯 Use Cases:

### Use Case 1: طفل يسجل حالته
```php
// 1. الطفل يمسح QR ويفتح /mood/5
// 2. يضغط على زر "سعيد"
// 3. Form يرسل POST إلى /mood/save

// في Controller:
public function save(Request $request) {
    $patientId = $request->request->get('patient_id'); // 5
    $mood = $request->request->get('mood'); // 'happy'
    
    $patient = $patientRepo->find($patientId);
    
    $moodTracking = new MoodTracking();
    $moodTracking->setPatient($patient);
    $moodTracking->setMood($mood);
    
    $em->persist($moodTracking);
    $em->flush();
    
    return $this->render('mood/success.html.twig');
}
```


### Use Case 2: معالج يشاهد الإحصائيات
```php
// في Dashboard Controller:
public function dashboard() {
    // إحصائيات عامة
    $stats = $moodRepo->getMoodStats();
    // [
    //   ['mood' => 'happy', 'count' => 15],
    //   ['mood' => 'neutral', 'count' => 8],
    //   ['mood' => 'sad', 'count' => 3]
    // ]
    
    // آخر التسجيلات
    $recentMoods = $moodRepo->findBy(
        [],
        ['createdAt' => 'DESC'],
        50
    );
    
    return $this->render('mood/dashboard.html.twig', [
        'stats' => $stats,
        'recentMoods' => $recentMoods
    ]);
}
```


### Use Case 3: تحليل مريض معين
```php
// الحصول على مشاعر مريض في آخر أسبوع
$patientId = 5;
$moods = $moodRepo->findByPatientLastDays($patientId, 7);

// تحليل
$happyDays = 0;
$sadDays = 0;

foreach ($moods as $mood) {
    if ($mood->getMood() === 'happy') {
        $happyDays++;
    } elseif ($mood->getMood() === 'sad') {
        $sadDays++;
    }
}

// إذا كان حزين 3 أيام متتالية → إشعار
if ($sadDays >= 3) {
    // إرسال إشعار للمعالج
}
```


---

## 🔗 QR Code Generation:

### استخدام API خارجي
```php
// في Template:
$url = $this->generateUrl('mood_select', [
    'patientId' => $patient->getId()
], UrlGeneratorInterface::ABSOLUTE_URL);

$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url);
```

**مثال URL:**
```
https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=http://localhost:8000/mood/5
```


### استخدام مكتبة Endroid (موجودة في المشروع)
```php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$qrCode = QrCode::create($url);
$writer = new PngWriter();
$result = $writer->write($qrCode);

// حفظ كملف
$result->saveToFile('qr-codes/patient-5.png');

// أو عرض مباشر
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
```


---

## 📊 تقارير مخصصة:

### تقرير أسبوعي لمريض
```php
public function weeklyReport(int $patientId) {
    $moods = $moodRepo->findByPatientLastDays($patientId, 7);
    
    $report = [
        'patient_id' => $patientId,
        'period' => '7 days',
        'total' => count($moods),
        'happy' => 0,
        'neutral' => 0,
        'sad' => 0
    ];
    
    foreach ($moods as $mood) {
        $report[$mood->getMood()]++;
    }
    
    return $report;
}
```


### تقرير شهري لكل المرضى
```php
public function monthlyReport() {
    $startDate = new \DateTime('-30 days');
    
    $qb = $moodRepo->createQueryBuilder('m')
        ->select('p.id, p.nom, p.prenom, COUNT(m.id) as total')
        ->join('m.patient', 'p')
        ->where('m.createdAt >= :startDate')
        ->setParameter('startDate', $startDate)
        ->groupBy('p.id')
        ->orderBy('total', 'DESC');
    
    return $qb->getQuery()->getResult();
}
```


---

## 🎉 جاهز للاستخدام!

**ابدأ الاختبار:**
```bash
# 1. افتح المتصفح
http://localhost:8000/mood/test

# 2. جرب الـ API
curl -X POST http://localhost:8000/mood/save \
  -d "patient_id=1" \
  -d "mood=happy"

# 3. شاهد النتائج
http://localhost:8000/mood/dashboard
```
