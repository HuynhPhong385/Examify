(() => {
    const grid = document.getElementById('adminExamGrid');
    if (!grid) return;

    function render(exam) {
        return `
            <article class="exam-card">
                <div class="exam-card-top">
                    <span class="badge">${Examify.escapeHtml(exam.category || 'Chung')}</span>
                    <span class="${Number(exam.is_published) ? 'published' : 'draft'}">${Number(exam.is_published) ? 'Đã xuất bản' : 'Bản nháp'}</span>
                </div>
                <h3>${Examify.escapeHtml(exam.title)}</h3>
                <p>${Number(exam.question_count)} câu · ${Number(exam.duration_minutes)} phút · Đạt ${Number(exam.passing_score)}%</p>
                <div class="actions">
                    <a class="btn" href="${Examify.url(`/teacher/exam_editor.php?id=${Number(exam.id)}`)}">Chỉnh sửa</a>
                    <button class="btn danger delete-exam" data-id="${Number(exam.id)}">Xóa</button>
                </div>
            </article>`;
    }

    function load() {
        Examify.api('/exams?limit=50').then(data => {
            grid.innerHTML = data.items.length ? data.items.map(render).join('') : '<div class="empty">Chưa có đề thi.</div>';
        }).catch(err => grid.innerHTML = `<div class="alert error">${Examify.escapeHtml(err.message)}</div>`);
    }

    grid.addEventListener('click', async e => {
        const btn = e.target.closest('.delete-exam');
        if (!btn) return;
        if (!confirm('Xóa đề này? Toàn bộ câu hỏi và lượt thi liên quan cũng sẽ bị xóa.')) return;
        try {
            await Examify.api(`/exams/${Number(btn.dataset.id)}`, { method: 'DELETE', body: '{}' });
            Examify.toast('Đã xóa đề.');
            load();
        } catch (err) {
            Examify.toast(err.message, 'error');
        }
    });

    load();
})();
