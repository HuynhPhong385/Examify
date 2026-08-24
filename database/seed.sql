USE examify;

INSERT INTO users (name,email,password,role) VALUES
('System Admin','admin@examify.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqKkzvYgW6kX8q7cS2W','admin'),
('Nguyễn Văn Giáo','teacher@examify.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqKkzvYgW6kX8q7cS2W','teacher'),
('Trần Minh Học','student@examify.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqKkzvYgW6kX8q7cS2W','student');

INSERT INTO subjects (name,description) VALUES
('Tin học cơ bản','Kiến thức máy tính và CNTT cơ bản'),
('Lập trình PHP','PHP, MySQL, MVC và REST API'),
('Lý thuyết lái xe','Bộ câu hỏi trắc nghiệm mô phỏng');

INSERT INTO questions
(subject_id,content,difficulty,option_a,option_b,option_c,option_d,correct_option,created_by)
VALUES
(1,'HTML là viết tắt của cụm từ nào?','easy','Hyper Text Markup Language','High Text Machine Language','Hyper Tool Multi Language','Home Text Markup Language','A',2),
(1,'CSS chủ yếu dùng để làm gì?','easy','Quản lý database','Tạo và định dạng giao diện','Tạo máy chủ','Mã hóa mật khẩu','B',2),
(2,'PDO trong PHP thường được dùng để làm gì?','medium','Kết nối và thao tác database','Tạo ảnh','Chạy CSS','Nén file','A',2),
(2,'HTTP method nào thường dùng để cập nhật tài nguyên?','medium','GET','POST','PUT','TRACE','C',2),
(2,'Mục tiêu chính của prepared statement là gì?','hard','Tăng kích thước ảnh','Giảm nguy cơ SQL Injection','Tạo cookie','Tạo session','B',2);

INSERT INTO exams (subject_id,title,description,duration_minutes,total_questions,status,created_by)
VALUES
(1,'Thi thử Tin học cơ bản','Đề mẫu để kiểm tra hệ thống.',10,3,'published',2);

INSERT INTO exam_questions (exam_id,question_id,sort_order) VALUES
(1,1,1),(1,2,2),(1,3,3);
