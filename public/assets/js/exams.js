(() => {
    const grid = document.getElementById('examGrid');
    const pagination = document.getElementById('examPagination');
    const search = document.getElementById('liveSearch');
    const dropdown = document.getElementById('searchDropdown');
    if (!grid) return;

    const PAGE_SIZE = 9; 
    let currentPage = 1;

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

    function load(page = 1) {
        currentPage = Math.max(1, page);
        grid.innerHTML = '<div class="skeleton">Đang tải danh sách đề...</div>';
        if (pagination) pagination.innerHTML = '';

        Examify.api(`/exams?limit=${PAGE_SIZE}&page=${currentPage}`)
            .then(data => {
                const items = data.items || [];
                grid.innerHTML = items.length
                    ? items.map(card).join('')
                    : (currentPage > 1
                        ? '<div class="empty">Trang này không có đề thi nào.</div>'
                        : '<div class="empty">Chưa có đề thi nào được xuất bản.</div>');
                renderPagination(Number(data.page) || currentPage, items.length);
                grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(err => {
                grid.innerHTML = `<div class="alert error">${Examify.escapeHtml(err.message)}</div>`;
            });
    }
    
    function renderPagination(page, itemCount) {
        if (!pagination) return;
        const hasPrev = page > 1;
        const hasNext = itemCount === PAGE_SIZE;
        if (!hasPrev && !hasNext) return;

        pagination.innerHTML = `
            <button type="button" class="btn" id="examPagePrev" ${hasPrev ? '' : 'disabled'}>‹ Trang trước</button>
            <span class="page-indicator">Trang ${page}</span>
            <button type="button" class="btn" id="examPageNext" ${hasNext ? '' : 'disabled'}>Trang sau ›</button>
        `;
        document.getElementById('examPagePrev')?.addEventListener('click', () => load(page - 1));
        document.getElementById('examPageNext')?.addEventListener('click', () => load(page + 1));
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

    load(1);
})();
