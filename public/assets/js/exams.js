(() => {
    const grid = document.getElementById('examGrid');
    const search = document.getElementById('liveSearch');
    const dropdown = document.getElementById('searchDropdown');
    if (!grid) return;

    const card = exam => `
        <article class="exam-card">
            <div class="exam-card-top">
                <span class="badge">${Examify.escapeHtml(exam.category || 'Chung')}</span>
                <span>${Number(exam.question_count || 0)} câu</span>
            </div>
            <h3>${Examify.escapeHtml(exam.title)}</h3>
            <p>${Examify.escapeHtml(exam.description || 'Không có mô tả.')}</p>
            <div class="meta-row">
                <span>⏱ ${Number(exam.duration_minutes)} phút</span>
                <span>Đạt ≥ ${Number(exam.passing_score)}%</span>
            </div>
            <a class="btn primary" href="${Examify.url(`/exam.php?id=${Number(exam.id)}`)}">Làm bài</a>
        </article>`;

    function load() {
        Examify.api('/exams?limit=30')
            .then(data => {
                grid.innerHTML = data.items.length
                    ? data.items.map(card).join('')
                    : '<div class="empty">Chưa có đề thi nào được xuất bản.</div>';
            })
            .catch(err => grid.innerHTML = `<div class="alert error">${Examify.escapeHtml(err.message)}</div>`);
    }

    let timer;
    search?.addEventListener('input', () => {
        clearTimeout(timer);
        const q = search.value.trim();
        if (!q) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }
        timer = setTimeout(() => {
            Examify.api('/search?q=' + encodeURIComponent(q)).then(data => {
                dropdown.innerHTML = data.items.length ? data.items.map(x => `
                    <a href="${Examify.url(`/exam.php?id=${Number(x.id)}`)}">
                        <strong>${Examify.escapeHtml(x.title)}</strong>
                        <small>${Examify.escapeHtml(x.category || 'Chung')} · ${Number(x.duration_minutes)} phút</small>
                    </a>
                `).join('') : '<div class="search-empty">Không tìm thấy đề phù hợp.</div>';
                dropdown.classList.remove('hidden');
            }).catch(() => { });
        }, 250);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.search-wrap')) dropdown?.classList.add('hidden');
    });

    load();
})();
