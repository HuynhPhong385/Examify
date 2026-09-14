(() => {
    const shell = document.querySelector('.exam-shell');
    if (!shell) return;
    const examId = Number(shell.dataset.examId);
    const durationMinutes = Number(shell.dataset.duration);
    const list = document.getElementById('questionList');
    const form = document.getElementById('examForm');
    const submitBtn = document.getElementById('submitExam');
    const status = document.getElementById('saveStatus');
    const timerEl = document.getElementById('timer');
    const progressEl = document.getElementById('examProgress');
    const navEl = document.getElementById('questionNav');
    const prevBtn = document.getElementById('prevQuestion');
    const nextBtn = document.getElementById('nextQuestion');

    let attemptId = null;
    let startedAt = null;
    let submitting = false;
    let lastSaveSeq = 0;
    const saveQueues = new Map();

    //Trạng thái phân trang câu hỏi
    let questions = [];
    let currentIndex = 0;
    const answered = new Set();

    function setStatus(text, type = '') {
        status.textContent = text;
        status.className = 'save-status ' + type;
    }

    async function init() {
        try {
            const attempt = await Examify.api('/attempts', {
                method: 'POST',
                body: JSON.stringify({ exam_id: examId })
            });
            attemptId = Number(attempt.id);
            startedAt = new Date(Number(attempt.started_at_epoch) * 1000);
            const data = await Examify.api(`/exams/${examId}/questions`);
            questions = data.items || [];
            renderQuestions(questions);
            submitBtn.disabled = questions.length === 0;
            setStatus(attempt.resumed ? 'Đã khôi phục lượt thi đang làm.' : 'Lượt thi đã bắt đầu.', 'ok');
            startTimer();
        } catch (err) {
            setStatus(err.message, 'error');
        }
    }

    function renderQuestions(items) {
        if (!items.length) {
            list.innerHTML = '<div class="alert info">Đề thi chưa có câu hỏi.</div>';
            navEl.innerHTML = '';
            progressEl.textContent = '';
            document.querySelector('.exam-pager')?.classList.add('hidden');
            return;
        }
        list.innerHTML = items.map((q, i) => `
            <article class="question-card" data-question-id="${Number(q.id)}" data-index="${i}">
                <div class="question-title">
                    <span>Câu ${i + 1}</span>
                    <small>${Number(q.points)} điểm</small>
                </div>
                <h3>${Examify.escapeHtml(q.content)}</h3>
                <div class="choices">
                    ${q.choices.map((c, j) => `
                        <label class="choice">
                            <input type="radio" name="q_${Number(q.id)}" value="${Number(c.id)}">
                            <span><b>${String.fromCharCode(65 + j)}.</b> ${Examify.escapeHtml(c.content)}</span>
                        </label>`).join('')}
                </div>
            </article>
        `).join('');

        list.addEventListener('change', e => {
            const input = e.target.closest('input[type=radio]');
            if (!input || submitting) return;
            const card = input.closest('.question-card');
            const questionId = Number(card.dataset.questionId);
            const choiceId = Number(input.value);
            const seq = ++lastSaveSeq;
            setStatus('Đang tự lưu đáp án...');

            answered.add(questionId);
            updateNavStates();

            const previous = saveQueues.get(questionId) || Promise.resolve();
            const next = previous.catch(() => { }).then(() => Examify.api(`/attempts/${attemptId}/answers`, {
                method: 'POST',
                body: JSON.stringify({ question_id: questionId, choice_id: choiceId })
            }));
            saveQueues.set(questionId, next);

            next.then(data => {
                if (seq === lastSaveSeq) setStatus(`Đã lưu lúc ${data.saved_at}`, 'ok');
            }).catch(err => {
                setStatus('Lỗi tự lưu: ' + err.message, 'error');
            });
        });

        buildNav();
        goToQuestion(0);
    }

    // Lưới số câu để nhảy nhanh tới bất kỳ câu nào, có đánh dấu đã làm/chưa làm.
    function buildNav() {
        navEl.innerHTML = questions.map((q, i) => `
            <button type="button" class="nav-dot" data-index="${i}" aria-label="Câu ${i + 1}">${i + 1}</button>
        `).join('');
        navEl.addEventListener('click', e => {
            const btn = e.target.closest('.nav-dot');
            if (!btn) return;
            goToQuestion(Number(btn.dataset.index));
        });
    }

    function updateNavStates() {
        navEl.querySelectorAll('.nav-dot').forEach((btn, i) => {
            const q = questions[i];
            btn.classList.toggle('current', i === currentIndex);
            btn.classList.toggle('answered', answered.has(Number(q.id)));
        });
    }

    function goToQuestion(index) {
        if (index < 0 || index >= questions.length) return;
        currentIndex = index;
        list.querySelectorAll('.question-card').forEach(card => {
            card.classList.toggle('active', Number(card.dataset.index) === currentIndex);
        });
        progressEl.textContent = `Câu ${currentIndex + 1} / ${questions.length}`;
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex === questions.length - 1;
        updateNavStates();
        list.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    prevBtn?.addEventListener('click', () => goToQuestion(currentIndex - 1));
    nextBtn?.addEventListener('click', () => goToQuestion(currentIndex + 1));

    function startTimer() {
        const end = startedAt.getTime() + durationMinutes * 60 * 1000;
        const tick = () => {
            const left = Math.max(0, Math.floor((end - Date.now()) / 1000));
            const mm = String(Math.floor(left / 60)).padStart(2, '0');
            const ss = String(left % 60).padStart(2, '0');
            timerEl.textContent = `${mm}:${ss}`;
            if (left <= 0) {
                notRedirect();
                clearInterval(interval);
                if (!submitting) submitExam(true);

            }
        };
        tick();
        const interval = setInterval(tick, 1000);
    }
    function notRedirect() {
        const end = startedAt.getTime() + durationMinutes * 60 * 1000;

        const tick = () => {
            const left = Math.max(0, Math.floor((end - Date.now()) / 1000));

            if (left <= 0 && window.location.href.includes(`/exam.php`)) {

                if (!submitting) submitExam(true);
                if (window.location.href.includes(`/result.php`)) clearInterval(interval);
            }
        };

        const interval = setInterval(tick, 1000);
    }

    async function submitExam(auto = false) {
        if (submitting || !attemptId) return;
        if (!auto && !confirm('Bạn chắc chắn muốn nộp bài? Sau khi nộp sẽ không thể sửa đáp án.')) return;
        submitting = true;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang chấm điểm...';
        setStatus('Server đang khóa lượt thi và chấm điểm...', 'ok');

        try {
            const result = await Examify.api(`/attempts/${attemptId}/submit`, { method: 'POST', body: '{}' });
            window.location.replace(Examify.url(`/result.php?id=${Number(result.attempt_id)}`));
        } catch (err) {
            submitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Nộp bài & chấm điểm';
            setStatus(err.message, 'error');
            window.location.replace(Examify.url(`/dashboard.php`));

        }
    }

    form.addEventListener('submit', e => {
        e.preventDefault();
        submitExam(false);
    });

    init();
})();
