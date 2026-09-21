USE examify;

INSERT INTO users (name, email, password, role) VALUES
('Quản trị viên', 'admin@examify.local', '123456', 'admin'),
('Giáo viên Demo', 'teacher@examify.local', '123456', 'teacher'),
('Học sinh Demo', 'student@examify.local', '123456', 'student')
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET @teacher_id = (SELECT id FROM users WHERE email='teacher@examify.local' LIMIT 1);

INSERT INTO exams (title, slug, description, category, duration_minutes, passing_score, is_published, created_by)
SELECT 'Đề mẫu Kiến thức CNTT cơ bản', 'de-mau-kien-thuc-cntt-co-ban',
       'Đề thi demo để kiểm thử chức năng làm bài, tự lưu và chấm điểm tự động.',
       'CNTT', 15, 60.00, 1, @teacher_id
WHERE NOT EXISTS (SELECT 1 FROM exams WHERE slug='de-mau-kien-thuc-cntt-co-ban');

SET @exam_id = (SELECT id FROM exams WHERE slug='de-mau-kien-thuc-cntt-co-ban' LIMIT 1);

INSERT INTO questions (exam_id, content, points, position)
SELECT @exam_id, 'HTTP là viết tắt của cụm từ nào?', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE exam_id=@exam_id AND position=1);

SET @q1 = (SELECT id FROM questions WHERE exam_id=@exam_id AND position=1 LIMIT 1);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q1, 'HyperText Transfer Protocol', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q1);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q1, 'High Transfer Text Process', 0, 2
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q1 AND position=2);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q1, 'Hyper Terminal Transfer Program', 0, 3
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q1 AND position=3);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q1, 'Host Text Transmission Protocol', 0, 4
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q1 AND position=4);

INSERT INTO questions (exam_id, content, points, position)
SELECT @exam_id, 'Câu lệnh SQL nào dùng để lấy dữ liệu?', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE exam_id=@exam_id AND position=2);

SET @q2 = (SELECT id FROM questions WHERE exam_id=@exam_id AND position=2 LIMIT 1);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q2, 'SELECT', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q2);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q2, 'UPDATE', 0, 2
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q2 AND position=2);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q2, 'DELETE', 0, 3
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q2 AND position=3);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q2, 'DROP', 0, 4
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q2 AND position=4);

INSERT INTO questions (exam_id, content, points, position)
SELECT @exam_id, 'jQuery thường được dùng ở phía nào của ứng dụng web?', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM questions WHERE exam_id=@exam_id AND position=3);

SET @q3 = (SELECT id FROM questions WHERE exam_id=@exam_id AND position=3 LIMIT 1);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q3, 'Trình duyệt (client-side)', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q3);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q3, 'MySQL server', 0, 2
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q3 AND position=2);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q3, 'PHP-FPM nội bộ', 0, 3
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q3 AND position=3);
INSERT INTO choices (question_id, content, is_correct, position)
SELECT @q3, 'DNS resolver', 0, 4
WHERE NOT EXISTS (SELECT 1 FROM choices WHERE question_id=@q3 AND position=4);
