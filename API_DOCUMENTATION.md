# Báo cáo API - Hệ thống Quản lý Công việc TaskFlow

## 1. Nền tảng và Thư viện

### Backend Framework
- Laravel 12.0
- PHP 8.2

### Các thư viện chính
- Laravel Sanctum: Xác thực API và quản lý token
- Laravel Tinker: Công cụ debug và tương tác
- Faker: Tạo dữ liệu mẫu cho testing

## 2. Danh sách API

### 2.1 Nhóm Authentication

| STT | Tên API | URL | Method | Input | Output | Ngoại lệ | Mô tả |
|-----|---------|-----|--------|--------|---------|-----------|-------|
| 1 | Đăng ký | `/api/auth/register` | POST | `email`, `password`, `name` | `user`, `token` | 422: Dữ liệu không hợp lệ | Đăng ký tài khoản mới |
| 2 | Đăng nhập | `/api/auth/login` | POST | `email`, `password` | `user`, `token` | 401: Thông tin không chính xác | Đăng nhập và nhận token |
| 3 | Thông tin user | `/api/auth/me` | GET | Token | `user` | 401: Chưa xác thực | Lấy thông tin người dùng hiện tại |
| 4 | Đăng xuất | `/api/auth/logout` | POST | Token | message | 401: Chưa xác thực | Hủy token hiện tại |

### 2.2 Nhóm Project

| STT | Tên API | URL | Method | Input | Output | Ngoại lệ | Mô tả |
|-----|---------|-----|--------|--------|---------|-----------|-------|
| 1 | Danh sách dự án | `/api/projects` | GET | Token | `projects[]` | 401: Chưa xác thực | Lấy danh sách dự án của user |
| 2 | Tạo dự án | `/api/projects` | POST | `name`, `description` | `project` | 422: Dữ liệu không hợp lệ | Tạo dự án mới |
| 3 | Chi tiết dự án | `/api/projects/{id}` | GET | Token | `project` | 403: Không có quyền | Xem thông tin chi tiết dự án |
| 4 | Cập nhật dự án | `/api/projects/{id}` | PUT | `name`, `description` | `project` | 403: Không có quyền | Cập nhật thông tin dự án |
| 5 | Xóa dự án | `/api/projects/{id}` | DELETE | Token | message | 403: Không có quyền | Xóa dự án |
| 6 | Thêm thành viên | `/api/projects/{id}/members` | POST | `user_id` | `project` | 403: Không có quyền | Thêm thành viên vào dự án |
| 7 | Xóa thành viên | `/api/projects/{id}/members` | DELETE | `user_id` | `project` | 403: Không có quyền | Xóa thành viên khỏi dự án |

### 2.3 Nhóm Task

| STT | Tên API | URL | Method | Input | Output | Ngoại lệ | Mô tả |
|-----|---------|-----|--------|--------|---------|-----------|-------|
| 1 | Danh sách công việc | `/api/tasks` | GET | `project_id`, `status`, `priority` | `tasks[]` | 401: Chưa xác thực | Lấy danh sách công việc có lọc |
| 2 | Tạo công việc | `/api/tasks` | POST | `title`, `description`, `status`, `priority`, `due_date`, `project_id`, `assigned_to` | `task` | 422: Dữ liệu không hợp lệ | Tạo công việc mới |
| 3 | Chi tiết công việc | `/api/tasks/{id}` | GET | Token | `task` | 403: Không có quyền | Xem chi tiết công việc |
| 4 | Cập nhật trạng thái | `/api/tasks/{id}/status` | PATCH | `status` | `task` | 403: Không có quyền | Cập nhật trạng thái công việc |

### 2.4 Nhóm Notification

| STT | Tên API | URL | Method | Input | Output | Ngoại lệ | Mô tả |
|-----|---------|-----|--------|--------|---------|-----------|-------|
| 1 | Danh sách thông báo | `/api/notifications` | GET | Token | `notifications[]` | 401: Chưa xác thực | Lấy tất cả thông báo |
| 2 | Thông báo chưa đọc | `/api/notifications/unread` | GET | Token | `notifications[]` | 401: Chưa xác thực | Lấy thông báo chưa đọc |
| 3 | Đánh dấu đã đọc | `/api/notifications/{id}/read` | PATCH | Token | `notification` | 403: Không có quyền | Đánh dấu thông báo đã đọc |
| 4 | Đánh dấu tất cả đã đọc | `/api/notifications/read-all` | PATCH | Token | message | 401: Chưa xác thực | Đánh dấu tất cả đã đọc |

## 3. Source Code

Link GitHub: [laptrinhweb-ptit-2025](https://github.com/snsar/laptrinhweb-ptit-2025)

## 4. Chú thích

- Tất cả các API (trừ đăng ký và đăng nhập) đều yêu cầu token xác thực
- Token được gửi trong header: `Authorization: Bearer {token}`
- Các mã lỗi chung:
  - 401: Chưa xác thực
  - 403: Không có quyền
  - 422: Dữ liệu không hợp lệ
  - 500: Lỗi server
