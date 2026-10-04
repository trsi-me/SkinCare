# خطوات إعداد SSL لمشروع SkinCare

## الطريقة الموصى بها: استخدام mkcert (للتطوير المحلي)

### الخطوة 1: تثبيت mkcert

**Windows (Chocolatey):**
```powershell
choco install mkcert
```

**Windows (Scoop):**
```powershell
scoop install mkcert
```

**أو تحميل مباشر:**
- اذهب إلى: https://github.com/FiloSottile/mkcert/releases
- حمّل `mkcert-v1.4.4-windows-amd64.exe`
- ضعه في مجلد في PATH أو استخدمه مباشرة

### الخطوة 2: إنشاء Certificate Authority محلي

```powershell
mkcert -install
```

هذا سينشئ CA محلي ويضيفه لـ Windows Trust Store.

### الخطوة 3: إنشاء شهادة SSL للمشروع

```powershell
cd C:\Users\TRSI\VSCode\Projects\SkinCare
mkcert -key-file ssl-key.pem -cert-file ssl-cert.pem localhost 127.0.0.1 ::1
```

سيُنشئ ملفين:
- `ssl-cert.pem` - الشهادة
- `ssl-key.pem` - المفتاح الخاص

### الخطوة 4: إعداد XAMPP Apache

**1. افتح ملف:** `C:\xampp\apache\conf\httpd.conf`

**2. تأكد من تفعيل SSL Module:**
```apache
LoadModule ssl_module modules/mod_ssl.so
Include conf/extra/httpd-ssl.conf
```

**3. افتح ملف:** `C:\xampp\apache\conf\extra\httpd-ssl.conf`

**4. عدّل VirtualHost:**
```apache
<VirtualHost _default_:443>
    DocumentRoot "C:/Users/TRSI/VSCode/Projects/SkinCare"
    ServerName localhost:443
    ServerAdmin admin@localhost
    
    SSLEngine on
    SSLCertificateFile "C:/Users/TRSI/VSCode/Projects/SkinCare/ssl-cert.pem"
    SSLCertificateKeyFile "C:/Users/TRSI/VSCode/Projects/SkinCare/ssl-key.pem"
    
    <Directory "C:/Users/TRSI/VSCode/Projects/SkinCare">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**5. أعد تشغيل Apache من XAMPP Control Panel**

### الخطوة 5: الوصول للمشروع

افتح المتصفح وانتقل إلى:
```
https://localhost/SkinCare
```

**ملاحظة:** لن يظهر تحذير لأن mkcert يضيف الشهادة لـ Windows Trust Store.

---

## الطريقة البديلة: استخدام OpenSSL (بدون mkcert)

### الخطوة 1: إنشاء شهادة SSL

```powershell
cd C:\Users\TRSI\VSCode\Projects\SkinCare

# إنشاء private key
openssl genrsa -out ssl-key.pem 2048

# إنشاء certificate signing request
openssl req -new -key ssl-key.pem -out ssl-csr.pem -subj "/CN=localhost"

# إنشاء self-signed certificate
openssl x509 -req -days 365 -in ssl-csr.pem -signkey ssl-key.pem -out ssl-cert.pem
```

### الخطوة 2: إعداد Apache (نفس الخطوات أعلاه)

**ملاحظة:** مع هذه الطريقة سيظهر تحذير في المتصفح لأن الشهادة self-signed.

---

## الطريقة الثالثة: استخدام stunnel مع PHP Built-in Server

إذا كنت تستخدم `php -S localhost:8000`:

### الخطوة 1: ثبت stunnel
```powershell
choco install stunnel
```

### الخطوة 2: أنشئ ملف إعداد stunnel.conf
```conf
cert = C:/Users/TRSI/VSCode/Projects/SkinCare/ssl-cert.pem
key = C:/Users/TRSI/VSCode/Projects/SkinCare/ssl-key.pem

[https]
accept = 8443
connect = 8000
```

### الخطوة 3: شغّل المشروع
```powershell
# Terminal 1: PHP Server
php -S localhost:8000

# Terminal 2: stunnel
stunnel stunnel.conf
```

افتح: `https://localhost:8443`

---

## إضافة .gitignore للشهادات

أضف للملف `.gitignore`:
```
ssl-*.pem
*.pem
ssl/
```

---

## التحقق من SSL

بعد الإعداد، تحقق من:
1. افتح `https://localhost/SkinCare`
2. اضغط F12 → Security tab
3. يجب أن ترى "The connection to this site is secure"

---

## استكشاف الأخطاء

**مشكلة: Apache لا يبدأ**
- تأكد من تفعيل `mod_ssl` في `httpd.conf`
- تحقق من مسارات الشهادات في `httpd-ssl.conf`

**مشكلة: تحذير في المتصفح**
- مع mkcert: تأكد من تشغيل `mkcert -install`
- مع OpenSSL: هذا طبيعي، اضغط "Advanced" → "Proceed"

**مشكلة: 404 Not Found**
- تحقق من `DocumentRoot` في `httpd-ssl.conf`
- تأكد من أن المسار صحيح

---

## للبيئة الإنتاجية

للإنتاج، استخدم:
- **Let's Encrypt** (مجاني)
- **Cloudflare SSL** (مجاني)
- شهادة مدفوعة من CA معتمد

