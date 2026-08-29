<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

$pdo = Database::pdo();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
// Hoạt động cả khi app chạy ở domain root lẫn thư mục con như /examify/public.
$apiPos = strpos($path, '/api');
if ($apiPos !== false) {
    $path = substr($path, $apiPos + 4);
}
$path = preg_replace('#^/index\.php#', '', $path);
$path = '/' . trim((string)$path, '/');
if ($path === '/') {
    $path = '';
}

if (!in_array($method, ['GET', 'HEAD'], true) && $path !== '/auth/login') {
    verify_csrf_json();
}

function exam_access(PDO $pdo, int $examId, array $user, bool $write = false): array {
    $stmt = $pdo->prepare('SELECT * FROM exams WHERE id=? LIMIT 1');
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();
    if (!$exam) {
        json_response(['error' => 'Không tìm thấy đề thi.'], 404);
    }
    if ($write) {
        if (!in_array($user['role'], ['admin','teacher'], true)) {
            json_response(['error' => 'Không có quyền sửa đề.'], 403);
        }
        if ($user['role'] !== 'admin' && (int)$exam['created_by'] !== (int)$user['id']) {
            json_response(['error' => 'Bạn chỉ được sửa đề do mình tạo.'], 403);
        }
    } else {
        if (!$exam['is_published'] && $user['role'] === 'student') {
            json_response(['error' => 'Đề thi chưa được xuất bản.'], 404);
        }
    }
    return $exam;
}

function slugify(string $text): string {
    $map = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d',
    ];
    $text = mb_strtolower(strtr($text, $map));
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    return trim((string)$text, '-') ?: 'de-thi';
}

if ($path === '/auth/login' && $method === 'POST') {
    $data = json_input();
    if (!Auth::attempt((string)($data['email'] ?? ''), (string)($data['password'] ?? ''))) {
        json_response(['error' => 'Sai email hoặc mật khẩu.'], 422);
    }
    json_response(['message' => 'Đăng nhập thành công.', 'user' => Auth::user(), 'csrf_token' => csrf_token()]);
}

if ($path === '/me' && $method === 'GET') {
    json_response(['user' => require_auth_json(), 'csrf_token' => csrf_token()]);
}

if ($path === '/search' && $method === 'GET') {
    $user1 = require_auth_json();
    $q = trim((string)($_GET['q'] ?? ''));
    if (mb_strlen($q) < 1) {
        json_response(['items' => []]);
    }
    // Prefix search tận dụng index tốt hơn so với LIKE '%keyword%'.
    $prefix = '%'.$q . '%';
    $stmt = $pdo->prepare(
        "SELECT id, title, category, duration_minutes, passing_score
        FROM exams
        WHERE (title LIKE ? OR category LIKE ?)
         ORDER BY updated_at DESC
         LIMIT 10"
    );
    if ($user1['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT id, title, category, duration_minutes, passing_score
            FROM exams
            WHERE is_published = 1 
              AND (title LIKE ? OR category LIKE ?) 
              ORDER BY updated_at DESC
            LIMIT 10");
}
    $stmt->execute([$prefix, $prefix]);
    json_response(['items' => $stmt->fetchAll()]);
}

if ($path === '/exams' && $method === 'GET') {
    $user = require_auth_json();
    $page = max(1, positive_int($_GET['page'] ?? 1, 1));
    $limit = min(50, max(1, positive_int($_GET['limit'] ?? 12, 12)));
    $offset = ($page - 1) * $limit;
    $q = trim((string)($_GET['q'] ?? ''));

    $where = [];
    $params = [];
    if ($user['role'] === 'student') {
        $where[] = 'e.is_published=1';
    } elseif ($user['role'] === 'teacher') {
        $where[] = 'e.created_by=?';
        $params[] = $user['id'];
    }
    if ($q !== '') {
        $where[] = '(e.title LIKE ? OR e.description LIKE ? OR e.category LIKE ?)';
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like);
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT e.id,e.title,e.description,e.category,e.duration_minutes,e.passing_score,e.is_published,
                   e.created_by,e.updated_at,u.name AS creator_name,
                   (SELECT COUNT(*) FROM questions q WHERE q.exam_id=e.id) AS question_count
            FROM exams e JOIN users u ON u.id=e.created_by
            $sqlWhere
            ORDER BY e.updated_at DESC
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(['items' => $stmt->fetchAll(), 'page' => $page, 'limit' => $limit]);
}

if ($path === '/exams' && $method === 'POST') {
    $user = require_role_json(['admin','teacher']);
    $data = json_input();
    $title = trim((string)($data['title'] ?? ''));
    $titlestmt = $pdo->prepare("SELECT * FROM exams WHERE title = ?");
    $titlestmt->execute([$title]);
    $titlename = $titlestmt->fetchColumn(1);
    if ($title === '') {
        json_response(['error' => 'Tên đề là bắt buộc.'], 422);
    }else if($titlename===$title){
        json_response(['error' => 'Tên đề đã tồn tại.'], 422);
    }
    $duration = min(600, max(1, positive_int($data['duration_minutes'] ?? 30, 30)));
    $passing = min(100, max(0, (float)($data['passing_score'] ?? 50)));
    $slugBase = slugify($title);
    $slug = $slugBase;
    for ($i=2; ; $i++) {
        $check = $pdo->prepare('SELECT 1 FROM exams WHERE slug=? LIMIT 1');
        $check->execute([$slug]);
        if (!$check->fetchColumn()) break;
        $slug = $slugBase . '-' . $i;
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO exams (title,slug,description,category,duration_minutes,passing_score,is_published,created_by)
         VALUES (?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        $title, $slug, trim((string)($data['description'] ?? '')),
        trim((string)($data['category'] ?? '')) ?: null,
        $duration, $passing, !empty($data['is_published']) ? 1 : 0, $user['id']
    ]);
    json_response(['message' => 'Đã tạo đề.', 'id' => (int)$pdo->lastInsertId()], 201);
}

if (preg_match('#^/exams/(\d+)$#', $path, $m)) {
    $examId = (int)$m[1];
    $user = require_auth_json();

    if ($method === 'GET') {
        $exam = exam_access($pdo, $examId, $user, false);
        unset($exam['slug']);
        json_response(['exam' => $exam]);
    }

    if ($method === 'PUT') {
        $exam = exam_access($pdo, $examId, $user, true);
        $data = json_input();
        $title = trim((string)($data['title'] ?? $exam['title']));
        if ($title === '') {
            json_response(['error' => 'Tên đề là bắt buộc.'], 422);
        }
        $stmt = $pdo->prepare(
            'UPDATE exams SET title=?,description=?,category=?,duration_minutes=?,passing_score=?,is_published=? WHERE id=?'
        );
        $stmt->execute([
            $title,
            trim((string)($data['description'] ?? '')),
            trim((string)($data['category'] ?? '')) ?: null,
            min(600, max(1, positive_int($data['duration_minutes'] ?? 30, 30))),
            min(100, max(0, (float)($data['passing_score'] ?? 50))),
            !empty($data['is_published']) ? 1 : 0,
            $examId
        ]);
        json_response(['message' => 'Đã cập nhật đề.']);
    }

    if ($method === 'DELETE') {
        exam_access($pdo, $examId, $user, true);
        $stmt = $pdo->prepare('DELETE FROM exams WHERE id=?');
        $stmt->execute([$examId]);
        json_response(['message' => 'Đã xóa đề.']);
    }
}

if (preg_match('#^/exams/(\d+)/questions$#', $path, $m)) {
    $examId = (int)$m[1];
    $user = require_auth_json();

    if ($method === 'GET') {
        exam_access($pdo, $examId, $user, false);
        $stmt = $pdo->prepare('SELECT id,content,points,position FROM questions WHERE exam_id=? ORDER BY position,id');
        $stmt->execute([$examId]);
        $questions = $stmt->fetchAll();
        $choiceStmt = $pdo->prepare('SELECT id,content,position FROM choices WHERE question_id=? ORDER BY position,id');
        foreach ($questions as &$q) {
            $choiceStmt->execute([$q['id']]);
            $q['choices'] = $choiceStmt->fetchAll();
        }
        unset($q);
        json_response(['items' => $questions]);
    }

    if ($method === 'POST') {
        exam_access($pdo, $examId, $user, true);
        $data = json_input();
        $content = trim((string)($data['content'] ?? ''));
        $choices = is_array($data['choices'] ?? null) ? $data['choices'] : [];
        if ($content === '' || count($choices) < 2) {
            json_response(['error' => 'Câu hỏi cần nội dung và ít nhất 2 lựa chọn.'], 422);
        }
        $correctCount = 0;
        foreach ($choices as $c) $correctCount += !empty($c['is_correct']) ? 1 : 0;
        if ($correctCount !== 1) {
            json_response(['error' => 'Mỗi câu phải có đúng 1 đáp án đúng.'], 422);
        }
        $pdo->beginTransaction();
        try {
            $posStmt = $pdo->prepare('SELECT COALESCE(MAX(position),0)+1 FROM questions WHERE exam_id=?');
            $posStmt->execute([$examId]);
            $position = (int)$posStmt->fetchColumn();
            $idstmt = $pdo->query('SELECT id FROM questions ORDER BY id');
            
            $ids = $idstmt->fetchAll(PDO::FETCH_COLUMN);
            $qid = count($ids)+1;
            for ($quid = 0; $quid+1 < count($ids); $quid++) {
                if ($ids[$quid+1]-$ids[$quid]!=1){
                    $qid = $ids[$quid]+1;
                    break;
                }
            }
            $stmt = $pdo->prepare('INSERT INTO questions (id,exam_id,content,points,position) VALUES (?,?,?,?,?)');
            $stmt->execute([$qid,$examId, $content, max(0.01, (float)($data['points'] ?? 1)), $position]);
           
            
            //json_response(['error' => $qid], 422);
            
           
            
            //$qid = (int)$pdo->lastInsertId();
            $cstmt = $pdo->prepare('INSERT INTO choices (question_id,content,is_correct,position) VALUES (?,?,?,?)');
            foreach ($choices as $i => $c) {
                $cc = trim((string)($c['content'] ?? ''));
                if ($cc === '') throw new RuntimeException('Lựa chọn không được để trống.');
                $cstmt->execute([$qid, $cc, !empty($c['is_correct']) ? 1 : 0, $i + 1]);
            }
            $pdo->commit();
            json_response(['message' => 'Đã thêm câu hỏi.', 'id' => $qid], 201);
        } catch (Throwable $e) {
            $pdo->rollBack();
            json_response(['error' => $e->getMessage()], 422);
        }
    }
}

if (preg_match('#^/questions/(\d+)$#', $path, $m)) {
    $questionId = (int)$m[1];
    $user = require_role_json(['admin','teacher']);
    $stmt = $pdo->prepare('SELECT q.*, e.created_by FROM questions q JOIN exams e ON e.id=q.exam_id WHERE q.id=? LIMIT 1');
    $stmt->execute([$questionId]);
    $q = $stmt->fetch();
    if (!$q) json_response(['error' => 'Không tìm thấy câu hỏi.'], 404);
    if ($user['role'] !== 'admin' && (int)$q['created_by'] !== (int)$user['id']) {
        json_response(['error' => 'Không có quyền sửa câu hỏi.'], 403);
    }

    if ($method === 'PUT') {
        $data = json_input();
        $content = trim((string)($data['content'] ?? ''));
        $choices = is_array($data['choices'] ?? null) ? $data['choices'] : [];
        if ($content === '' || count($choices) < 2) {
            json_response(['error' => 'Câu hỏi cần nội dung và ít nhất 2 lựa chọn.'], 422);
        }
        $correctCount = 0;
        foreach ($choices as $c) $correctCount += !empty($c['is_correct']) ? 1 : 0;
        if ($correctCount !== 1) {
            json_response(['error' => 'Mỗi câu phải có đúng 1 đáp án đúng.'], 422);
        }
        $pdo->beginTransaction();
        try {
            $u = $pdo->prepare('UPDATE questions SET content=?,points=? WHERE id=?');
            $u->execute([$content, max(0.01, (float)($data['points'] ?? 1)), $questionId]);
            $pdo->prepare('DELETE FROM choices WHERE question_id=?')->execute([$questionId]);
            $cstmt = $pdo->prepare('INSERT INTO choices (question_id,content,is_correct,position) VALUES (?,?,?,?)');
            foreach ($choices as $i => $c) {
                $cc = trim((string)($c['content'] ?? ''));
                if ($cc === '') throw new RuntimeException('Lựa chọn không được để trống.');
                $cstmt->execute([$questionId, $cc, !empty($c['is_correct']) ? 1 : 0, $i+1]);
            }
            $pdo->commit();
            json_response(['message' => 'Đã cập nhật câu hỏi.']);
        } catch (Throwable $e) {
            $pdo->rollBack();
            json_response(['error' => $e->getMessage()], 422);
        }
    }

    if ($method === 'DELETE') {
        $pdo->prepare('DELETE FROM questions WHERE id=?')->execute([$questionId]);
        json_response(['message' => 'Đã xóa câu hỏi.']);
    }
}

if ($path === '/attempts' && $method === 'POST') {
    $user = require_auth_json();
    $data = json_input();
    $examId = positive_int($data['exam_id'] ?? 0);
    if (!$examId) json_response(['error' => 'Thiếu exam_id.'], 422);
    $exam = exam_access($pdo, $examId, $user, false);
    if (!$exam['is_published']) json_response(['error' => 'Đề chưa được xuất bản.'], 422);

    $stmt = $pdo->prepare("SELECT id,started_at,UNIX_TIMESTAMP(started_at) AS started_at_epoch FROM attempts WHERE exam_id=? AND user_id=? AND status='in_progress' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$examId, $user['id']]);
    if ($existing = $stmt->fetch()) {
        json_response(['id' => (int)$existing['id'], 'started_at' => $existing['started_at'], 'started_at_epoch' => (int)$existing['started_at_epoch'], 'resumed' => true]);
    }
    $stmt = $pdo->prepare("INSERT INTO attempts (exam_id,user_id,status) VALUES (?,?,'in_progress')");
    $stmt->execute([$examId, $user['id']]);
    $id = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT started_at, UNIX_TIMESTAMP(started_at) AS started_at_epoch FROM attempts WHERE id=?');
    $stmt->execute([$id]);
    $started = $stmt->fetch();
    json_response(['id' => $id, 'started_at' => $started['started_at'], 'started_at_epoch' => (int)$started['started_at_epoch'], 'resumed' => false], 201);
}

if (preg_match('#^/attempts/(\d+)/answers$#', $path, $m) && $method === 'POST') {
    $attemptId = (int)$m[1];
    $user = require_auth_json();
    $data = json_input();
    $questionId = positive_int($data['question_id'] ?? 0);
    $choiceId = positive_int($data['choice_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT a.*, e.duration_minutes FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.id=? AND a.user_id=? LIMIT 1");
    $stmt->execute([$attemptId, $user['id']]);
    $attempt = $stmt->fetch();
    if (!$attempt) json_response(['error' => 'Không tìm thấy lượt thi.'], 404);
    if ($attempt['status'] !== 'in_progress') json_response(['error' => 'Lượt thi đã nộp.'], 409);
    $deadline = strtotime($attempt['started_at']) + ((int)$attempt['duration_minutes'] * 60);
    if (time() > $deadline) json_response(['error' => 'Đã hết thời gian làm bài. Hệ thống sẽ nộp các đáp án đã lưu.'], 409);

    $stmt = $pdo->prepare(
        'SELECT c.id FROM choices c JOIN questions q ON q.id=c.question_id
         WHERE c.id=? AND q.id=? AND q.exam_id=? LIMIT 1'
    );
    $stmt->execute([$choiceId, $questionId, $attempt['exam_id']]);
    if (!$stmt->fetchColumn()) json_response(['error' => 'Đáp án không thuộc câu hỏi/đề thi này.'], 422);

    $stmt = $pdo->prepare(
        'INSERT INTO attempt_answers (attempt_id,question_id,choice_id)
         VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE choice_id=VALUES(choice_id), is_correct=NULL, points_awarded=0'
    );
    $stmt->execute([$attemptId, $questionId, $choiceId]);
    json_response(['message' => 'Đã tự lưu.', 'saved_at' => date('H:i:s')]);
}

if (preg_match('#^/attempts/(\d+)/submit$#', $path, $m) && $method === 'POST') {
    $attemptId = (int)$m[1];
    $user = require_auth_json();

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "SELECT a.*, e.passing_score
             FROM attempts a JOIN exams e ON e.id=a.exam_id
             WHERE a.id=? AND a.user_id=? FOR UPDATE"
        );
        $stmt->execute([$attemptId, $user['id']]);
        $attempt = $stmt->fetch();
        if (!$attempt) throw new RuntimeException('Không tìm thấy lượt thi.');
        if ($attempt['status'] === 'submitted') {
            $pdo->commit();
            json_response([
                'message' => 'Lượt thi đã được chấm.',
                'attempt_id' => $attemptId,
                'percentage' => (float)$attempt['percentage'],
                'passed' => (bool)$attempt['passed']
            ]);
        }

        $stmt = $pdo->prepare('SELECT id,points FROM questions WHERE exam_id=?');
        $stmt->execute([$attempt['exam_id']]);
        $questions = $stmt->fetchAll();
        $total = 0.0;
        $score = 0.0;

        $answerStmt = $pdo->prepare(
            'SELECT aa.choice_id, c.is_correct
             FROM attempt_answers aa
             JOIN choices c ON c.id=aa.choice_id
             WHERE aa.attempt_id=? AND aa.question_id=? LIMIT 1'
        );
        $markStmt = $pdo->prepare(
            'UPDATE attempt_answers SET is_correct=?, points_awarded=? WHERE attempt_id=? AND question_id=?'
        );

        foreach ($questions as $q) {
            $points = (float)$q['points'];
            $total += $points;
            $answerStmt->execute([$attemptId, $q['id']]);
            $ans = $answerStmt->fetch();
            if ($ans) {
                $correct = (int)$ans['is_correct'] === 1;
                $awarded = $correct ? $points : 0.0;
                $score += $awarded;
                $markStmt->execute([$correct ? 1 : 0, $awarded, $attemptId, $q['id']]);
            }
        }

        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0.0;
        $passed = $percentage >= (float)$attempt['passing_score'] ? 1 : 0;
        $stmt = $pdo->prepare(
            "UPDATE attempts
             SET status='submitted', submitted_at=NOW(), score=?, total_points=?, percentage=?, passed=?
             WHERE id=?"
        );
        $stmt->execute([$score, $total, $percentage, $passed, $attemptId]);
        $pdo->commit();

        json_response([
            'message' => 'Chấm điểm hoàn tất.',
            'attempt_id' => $attemptId,
            'score' => $score,
            'total_points' => $total,
            'percentage' => $percentage,
            'passed' => (bool)$passed
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        json_response(['error' => $e->getMessage()], 422);
    }
}

if (preg_match('#^/attempts/(\d+)$#', $path, $m) && $method === 'GET') {
    $attemptId = (int)$m[1];
    $user = require_auth_json();
    $stmt = $pdo->prepare(
        'SELECT a.*, e.title,e.passing_score,e.created_by
         FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.id=? LIMIT 1'
    );
    $stmt->execute([$attemptId]);
    $a = $stmt->fetch();
    if (!$a) json_response(['error' => 'Không tìm thấy lượt thi.'], 404);
    $allowed = (int)$a['user_id'] === (int)$user['id']
        || $user['role'] === 'admin'
        || ($user['role'] === 'teacher' && (int)$a['created_by'] === (int)$user['id']);
    if (!$allowed) json_response(['error' => 'Không có quyền xem lượt thi.'], 403);
    unset($a['created_by']);
    json_response(['attempt' => $a]);
}

json_response(['error' => 'Endpoint không tồn tại.', 'path' => $path, 'method' => $method], 404);
