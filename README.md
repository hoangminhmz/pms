# 🎓 Hotel PMS Training Platform

**Nền tảng thực hành quản lý khách sạn cho sinh viên ngành Hospitality Management**

Hệ thống mô phỏng PMS (Property Management System) thực tế, giúp sinh viên thực hành các nghiệp vụ front office trong môi trường an toàn.

---

## 🎯 Mục Đích

- ✅ Cung cấp môi trường thực hành PMS an toàn cho sinh viên
- ✅ Giảng viên tạo scenarios/exercises và đánh giá sinh viên
- ✅ Mỗi lớp có data riêng biệt (data isolation)
- ✅ Theo dõi và đánh giá quá trình học tập
- ✅ Không cần cài đặt phức tạp - chỉ upload lên cPanel

---

## 👥 Vai Trò Người Dùng

### 1. **Admin** (Quản trị viên)
- Quản lý tài khoản giảng viên và sinh viên
- Quản lý các lớp học
- Xem báo cáo tổng quan hệ thống

### 2. **Teacher** (Giảng viên)
- Tạo và quản lý lớp học
- Thêm/xóa sinh viên vào lớp
- **Generate dummy data** cho lớp (khách, phòng, booking)
- Tạo **scenarios/exercises** (kịch bản thực hành)
- Xem activity logs của sinh viên
- **Chấm điểm** và đánh giá
- Reset data lớp khi cần

### 3. **Student** (Sinh viên)
- Thực hành các nghiệp vụ PMS:
  - Check-in (có booking, walk-in)
  - Check-out (thanh toán, in hóa đơn)
  - Quản lý đặt phòng
  - Quản lý thông tin khách
  - Cập nhật trạng thái phòng
- Làm bài tập scenarios
- Xem điểm và feedback

---

## ✨ Tính Năng Chính

### 🏫 **Class Management**
- Giảng viên tạo lớp học
- Mỗi lớp có code riêng
- Sinh viên join bằng class code
- **Data isolation**: Mỗi lớp có data hoàn toàn riêng biệt

### 🎲 **Dummy Data Generator**
- Tạo data mẫu realistic:
  - 50-100 phòng với các loại phòng khác nhau
  - 50-200 hồ sơ khách (tên Việt + quốc tế)
  - 30-50 booking (quá khứ, hiện tại, tương lai)
  - 10-15 khách đang ở (in-house)
  - 5-10 arrivals hôm nay
- Data có thể reset và tạo lại

### 📚 **Training Scenarios**
- Giảng viên tạo kịch bản thực hành:
  - "Xử lý khách walk-in vào giờ cao điểm"
  - "Check-out có complaint về minibar"
  - "Xử lý overbooking"
- Có hướng dẫn, time limit, scoring
- Tự động log actions của sinh viên
- Chấm điểm theo rubric

### 📊 **Activity Tracking**
- Log tất cả thao tác của sinh viên
- Giảng viên xem timeline chi tiết
- Báo cáo theo từng sinh viên/lớp
- Export để đánh giá

### 🎯 **Grading System**
- Chấm điểm scenarios
- Track progress của sinh viên
- Feedback chi tiết
- Bảng xếp hạng lớp

---

## 📋 Yêu Cầu Hệ Thống

- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Web Server**: Apache (cPanel shared hosting OK)
- **Storage**: ~50MB
- **Không cần**: Composer, Node.js, framework

---

## 🚀 Cài Đặt Nhanh (3 Bước)

### Bước 1: Upload Files
```
1. Download ZIP từ GitHub
2. Upload vào cPanel File Manager
3. Giải nén vào public_html/pms-training
```

### Bước 2: Tạo Database
```
1. cPanel → MySQL Databases
2. Tạo database: pms_training
3. Tạo user và gán ALL PRIVILEGES
```

### Bước 3: Chạy Installer
```
1. Truy cập: http://yourdomain.com/pms-training/install.php
2. Điền thông tin database
3. Tạo tài khoản Admin
4. Hoàn tất!
```

---

## 📁 Cấu Trúc Project

```
pms-training/
├── index.php                 # Dashboard chính
├── install.php               # Trình cài đặt
│
├── config/
│   ├── database.php          # Cấu hình DB
│   └── app.php               # Cấu hình app
│
├── core/
│   ├── auth.php              # Authentication
│   ├── db.php                # Database helpers
│   ├── functions.php         # Utility functions
│   └── isolation.php         # Data isolation logic
│
├── modules/
│   ├── admin/                # Module Admin
│   │   ├── users.php
│   │   └── classes.php
│   │
│   ├── teacher/              # Module Giảng viên
│   │   ├── dashboard.php
│   │   ├── classes.php       # Quản lý lớp
│   │   ├── students.php      # Quản lý sinh viên
│   │   ├── scenarios.php     # Tạo scenarios
│   │   ├── grading.php       # Chấm điểm
│   │   ├── generator.php     # Generate dummy data
│   │   └── reports.php       # Báo cáo
│   │
│   ├── student/              # Module Sinh viên
│   │   ├── dashboard.php
│   │   ├── scenarios.php     # Làm bài tập
│   │   └── progress.php      # Xem tiến trình
│   │
│   └── pms/                  # Module PMS Operations
│       ├── guests.php        # Quản lý khách
│       ├── rooms.php         # Quản lý phòng
│       ├── reservations.php  # Đặt phòng
│       ├── checkin.php       # Check-in
│       ├── checkout.php      # Check-out
│       └── reports.php       # Báo cáo PMS
│
├── templates/
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
│
├── assets/
│   ├── css/                  # Styles
│   ├── js/                   # Scripts
│   └── images/               # Images
│
└── sql/
    └── schema.sql            # Database schema
```

---

## 💾 Database Schema

### **Core Tables**
- `users` - Admin, Teachers, Students
- `school_classes` - Lớp học
- `class_students` - Sinh viên trong lớp

### **Training Tables**
- `scenarios` - Kịch bản thực hành
- `scenario_attempts` - Bài làm của sinh viên
- `activity_logs` - Log hoạt động

### **PMS Tables** (có `class_id` cho isolation)
- `room_types` - Loại phòng
- `rooms` - Phòng
- `guests` - Thông tin khách
- `reservations` - Đặt phòng
- `checkins` - Check-in records
- `payments` - Thanh toán

---

## 🎨 Giao Diện

Sử dụng **AdminLTE 3** (CDN) - giao diện đẹp, responsive, không cần build.

---

## 🔒 Bảo Mật & Data Isolation

- **Data Isolation**: Mỗi lớp chỉ thấy data của lớp mình
- **Role-based Access**: Admin/Teacher/Student
- **Password Hash**: bcrypt
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: htmlspecialchars
- **CSRF Protection**: Token validation

---

## 📖 Quy Trình Sử Dụng

### Giảng Viên:
```
1. Đăng nhập → Tạo lớp học
2. Thêm sinh viên (import CSV hoặc thủ công)
3. Generate dummy data cho lớp
4. Tạo scenarios/exercises
5. Sinh viên thực hành
6. Xem activity logs và chấm điểm
```

### Sinh Viên:
```
1. Đăng nhập → Vào lớp của mình
2. Xem dashboard PMS (rooms, bookings, etc.)
3. Làm scenarios được giao
4. Thực hành các thao tác PMS
5. Xem điểm và feedback
```

---

## 🎯 Scenarios Mẫu

1. **Walk-in Check-in**: Khách đến không có booking
2. **Express Checkout**: Check-out nhanh có thanh toán
3. **Overbooking**: Xử lý tình huống hết phòng
4. **VIP Guest**: Chăm sóc khách VIP
5. **Complaint Handling**: Xử lý khiếu nại
6. **Group Booking**: Đặt phòng đoàn
7. **Early Check-out**: Khách trả phòng sớm
8. **Room Change**: Đổi phòng cho khách

---

## 📊 Báo Cáo

### Cho Giảng Viên:
- Activity timeline theo sinh viên
- Completion rate của scenarios
- Average scores
- Time spent per task
- Common mistakes

### Cho Sinh Viên:
- Personal progress
- Scores history
- Areas to improve

---

## ⚡ Không Cần

- ❌ Composer/NPM
- ❌ Framework phức tạp
- ❌ Terminal/SSH
- ❌ Build tools

## ✅ Chỉ Cần

- ✅ PHP + MySQL
- ✅ Upload lên cPanel
- ✅ Chạy installer
- ✅ Bắt đầu dạy học!

---

## 📞 Support

GitHub Issues: [Repository URL]

---

## 📜 License

MIT License - Free for educational use

---

**Made with ❤️ for Vietnamese hospitality education**
