# 📌 Reminder & Payment Tracker  
Ứng dụng nhắc nhở quá hạn đăng ký / thanh toán dịch vụ

---

## 📖 Overview

**Reminder & Payment Tracker** là ứng dụng giúp người dùng quản lý các dịch vụ đăng ký (subscription services) và theo dõi thời hạn thanh toán nhằm tránh quên gia hạn, bị gián đoạn dịch vụ hoặc mất dữ liệu.

Ứng dụng hỗ trợ:
- Quản lý danh sách dịch vụ đang sử dụng
- Theo dõi và cảnh báo trước khi hết hạn
- Lưu trữ lịch sử thanh toán
- Hỗ trợ thanh toán (tuỳ chọn)
- Dashboard quản trị dành cho admin

---

## 🚀 Key Features

---

## 1️⃣ User Account & Subscription Management

### 🔐 Authentication
- Đăng ký / Đăng nhập bằng:
  - Email & Password
  - Social Login (Google, Facebook, ...)
  - OTP Authentication

### 📦 Subscription Management
- Thêm dịch vụ thủ công
- Đồng bộ dịch vụ tự động qua API (nếu provider hỗ trợ)
- Quản lý các loại dịch vụ:
  - Cloud
  - Điện / Nước
  - Internet
  - SaaS
  - Phần mềm thuê bao
  - Các dịch vụ đăng ký khác

### 📋 Thông tin lưu trữ cho mỗi dịch vụ
- Tên dịch vụ
- Nhà cung cấp
- Gói dịch vụ (Free / Premium / Enterprise / ...)
- Ngày bắt đầu
- Ngày hết hạn
- Trạng thái:
  - `Active`
  - `Expiring Soon`
  - `Expired`
  - `Cancelled`
- Ghi chú
- Hóa đơn đính kèm (optional)

### 🔄 Service Actions
- Gia hạn thủ công
- Hủy dịch vụ
- Redirect đến trang chính thức của nhà cung cấp

---

## 2️⃣ Expiration Tracking & Notifications

### 📅 Automatic Expiration Calculation
- Tự động tính ngày hết hạn
- Cập nhật trạng thái theo thời gian thực

### ⚠️ Custom Alert Threshold
Người dùng có thể cấu hình:
- Nhắc trước 7 ngày
- Nhắc trước 3 ngày
- Nhắc trước 1 ngày
- Nhắc sau X ngày khi đã quá hạn

### 🔔 Notification Channels
- 📧 Email Notification
- 📱 SMS Notification (tùy chọn)
- 🔔 In-app Notification (Dashboard banner / popup)
- 📲 Push Notification (Mobile App – optional)

### ⚙️ Customization
- Tùy chỉnh tần suất nhắc nhở
- Chọn kênh thông báo mong muốn
- Bật/tắt cảnh báo khẩn

---

## 3️⃣ Payment & Service Linking (Optional Module)

### 💳 Payment Methods
- Credit/Debit Card
- PayPal
- VNPay
- Momo
- Các phương thức khác

### 🔁 Payment Features
- Thanh toán thủ công
- Auto-renew (nếu provider hỗ trợ API)
- Auto-charge (nếu được cho phép)
- Redirect đến trang thanh toán chính thức

### 🧾 Payment History
- Lưu lịch sử thanh toán
- Hóa đơn điện tử
- Chính sách hoàn tiền (nếu có)

---

## 4️⃣ Expired Service Handling

Khi dịch vụ quá hạn:

- Tự động cập nhật trạng thái `Expired` / `Inactive`
- Hiển thị cảnh báo nổi bật trên dashboard
- Tiếp tục gửi nhắc nhở sau X ngày (theo cấu hình người dùng)

### ✔️ User Actions
- Đánh dấu "Đã gia hạn"
- Đánh dấu "Đã hủy dịch vụ"
- Tự động cập nhật trạng thái khi xác nhận thanh toán

### 📊 History Tracking
- Lưu lịch sử:
  - Các lần hết hạn
  - Các lần gia hạn
  - Thay đổi trạng thái

---

## 5️⃣ Admin Dashboard

Dành cho quản trị hệ thống.

### 👥 User Management
- Quản lý toàn bộ người dùng
- Quản lý dịch vụ đã liên kết

### 📈 Monitoring & Analytics
- Danh sách:
  - Sắp hết hạn
  - Đã hết hạn
  - Không hoạt động lâu ngày
- Tỷ lệ:
  - Gia hạn
  - Hủy dịch vụ
- Thống kê theo loại dịch vụ
- Log gửi thông báo (Email / SMS / Push)

### 🛠 Admin Actions
- Gửi nhắc nhở thủ công
- Xuất báo cáo:
  - Doanh thu
  - Trạng thái dịch vụ
  - Tỷ lệ duy trì khách hàng

---

## 6️⃣ Advanced Features (Optional)

### 📅 Calendar Sync
- Google Calendar
- Outlook
- iCal

### 🤖 AI Smart Reminder
- Gợi ý dịch vụ quan trọng
- Ưu tiên cảnh báo theo mức độ rủi ro
- Phân tích hành vi thanh toán

### 🏢 Organization Management
- Quản lý nhiều người dùng trong cùng tổ chức
- Phân quyền theo vai trò

### 🔌 Public API
- Cung cấp API cho bên thứ ba tích hợp
- Webhook khi thay đổi trạng thái dịch vụ  
  (Active → Expired → Cancelled)

---

## 🏗️ Suggested System Architecture

- Frontend: Web Dashboard + Mobile App
- Backend: REST API / GraphQL
- Database: PostgreSQL / MySQL
- Notification Service:
  - Email Service
  - SMS Gateway
  - Push Service
- Payment Integration Module
- Scheduler / Cron Service để xử lý:
  - Kiểm tra hạn
  - Gửi nhắc tự động
  - Cập nhật trạng thái

---

## 🔐 Security Considerations

- Mã hóa thông tin thanh toán
- Token-based Authentication (JWT / OAuth2)
- Role-based Access Control (RBAC)
- Rate limiting API
- Logging & Monitoring

---

## 📊 Future Roadmap

- Ứng dụng mobile native
- Tích hợp thêm nhiều provider API
- Dashboard phân tích tài chính cá nhân
- Multi-currency support
- Smart budgeting assistant

---

## 📄 License

This project is licensed under the MIT License.
