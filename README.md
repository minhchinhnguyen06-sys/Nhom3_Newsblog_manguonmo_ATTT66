# Nhom3_Newsblog_manguonmo_ATTT66
# Dự Án Nhóm 3 - WordPress News Blog (Task 4)

Kho lưu trữ (Repository) này chứa mã nguồn Child Theme, cơ sở dữ liệu mới nhất và tài liệu hướng dẫn bàn giao cho hệ thống.

## 📁 Cấu trúc thư mục
- `Child Theme/`: Thư mục chứa mã nguồn Child Theme (`functions.php`, `style.css`, ...).
- `local-20261004-215757.sql`: File sao lưu cơ sở dữ liệu (Database) mới nhất của dự án.
- `README.md`: Hướng dẫn cài đặt và sử dụng hệ thống.

---

## 🚀 Hướng dẫn cài đặt dành cho Bạn 5 (Bàn giao hệ thống)

Để thiết lập và chạy trang web trên môi trường Local (ví dụ: LocalWP), cậu thực hiện theo các bước sau:

### Bước 1: Cài đặt Source Code Child Theme
1. Tải thư mục `Child Theme` về máy.
2. Đưa thư mục `Child Theme` vào đường dẫn chứa các theme của WordPress trên Local của cậu:
   `.../app/public/wp-content/themes/`
3. Vào trang quản trị WordPress (`Dashboard`) > **Appearance** > **Themes** và kích hoạt (Activate) theme **Nhóm 3 News Child**.

### Bước 2: Import Cơ sở dữ liệu (Database)
1. Tải file `local-20261004-215757.sql` từ kho lưu trữ này về máy tính.
2. Mở ứng dụng **LocalWP**, chọn trang web của nhóm và bấm vào **Open Adminer** ở mục Database.
3. Trong giao diện Adminer, chọn tab **Import**.
4. Chọn file `local-20261004-215757.sql` vừa tải về, sau đó nhấn nút **Execute** để tiến hành khôi phục cơ sở dữ liệu.

### Bước 3: Kiểm tra hoàn tất
Sau khi hoàn tất 2 bước trên, truy cập vào trang chủ website để kiểm tra lại toàn bộ giao diện, các tính năng trong `functions.php` và dữ liệu bài viết xem đã hoạt động mượt mà chưa nhé!
