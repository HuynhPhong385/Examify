<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Bài thi: <?= htmlspecialchars($exam['title']) ?></h2>
        <!-- Đồng hồ đếm ngược -->
        <div class="fs-4 fw-bold text-danger" 
            id="countdown" 
            data-start="<?= $attempt['start_time'] ?? date('Y-m-d H:i:s') ?>" 
            data-minutes="<?= $exam['duration'] ?? 30 ?>">
            00:00
        </div>
    </div>

    <!-- Form làm bài thi được submit ngầm bằng AJAX -->
    <form id="exam-form" data-submitUrl="/exams/<?= $exam['id'] ?>/submit">
        <?php foreach ($questions as $index => $q): ?>
            <div class="card mb-3 p-3 shadow-sm">
                <h5>Câu <?= $index + 1 ?>: <?= htmlspecialchars($q['content']) ?></h5>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="A" id="q<?= $q['id'] ?>_a">
                    <label class="form-check-label" for="q<?= $q['id'] ?>_a"><?= htmlspecialchars($q['option_a']) ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="B" id="q<?= $q['id'] ?>_b">
                    <label class="form-check-label" for="q<?= $q['id'] ?>_b"><?= htmlspecialchars($q['option_b']) ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="C" id="q<?= $q['id'] ?>_c">
                    <label class="form-check-label" for="q<?= $q['id'] ?>_c"><?= htmlspecialchars($q['option_c']) ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="D" id="q<?= $q['id'] ?>_d">
                    <label class="form-check-label" for="q<?= $q['id'] ?>_d"><?= htmlspecialchars($q['option_d']) ?></label>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary btn-lg">Nộp bài</button>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
<script>


document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('exam-form');
    const timer = document.getElementById('countdown');
    
    if (form && timer) {
        // Lấy thời gian từ data attribute hoặc mặc định 30 phút
        const minutes = parseInt(timer.dataset.minutes || 30, 10);
        let timeLeft = minutes * 60; // Tính bằng giây
        let submitting = false;

        function render() {
            if (timeLeft <= 0 && !submitting) {
                submitting = true;
                submitExam(true);
                return;
            }
            
            const mm = String(Math.floor(timeLeft / 60)).padStart(2, '0');
            const ss = String(timeLeft % 60).padStart(2, '0');
            timer.textContent = mm + ':' + ss;
            timeLeft--;
        }

        async function submitExam(auto) {
            const fd = new FormData(form);
            const answers = {};
            for (const [key, value] of fd.entries()) {
                const m = key.match(/^answers\[(\d+)\]$/);
                if (m) answers[m[1]] = value;
            }
            try {
                const res = await fetch(form.dataset.submitUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify({ answers: answers })
                });
                const data = await res.json();
                if (data.ok) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Không thể nộp bài.');
                    submitting = false;
                }
            } catch (err) {
                alert('Lỗi kết nối khi nộp bài. Hãy thử lại.');
                submitting = false;
            }
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (submitting) return;
            if (confirm('Bạn chắc chắn muốn nộp bài?')) {
                submitting = true;
                submitExam(false);
            }
        });

        render();
        setInterval(render, 1000);
    }
});
</script>