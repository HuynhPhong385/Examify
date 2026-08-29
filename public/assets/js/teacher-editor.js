(() => {
    const shell = document.querySelector('.editor-shell');
    if (!shell) return;
    let examId = Number(shell.dataset.examId || 0);
    const metaForm = document.getElementById('examMetaForm');
    const questionList = document.getElementById('questionEditorList');
    const addBtn = document.getElementById('addQuestionBtn');
    let questions = [];

    if (examId) {
        try {
            questions = JSON.parse(document.getElementById('questionData')?.textContent || '[]');
        } catch (_) { }
    }

    function metaPayload() {
        const fd = new FormData(metaForm);
        return {
            title: String(fd.get('title') || '').trim(),
            description: String(fd.get('description') || '').trim(),
            category: String(fd.get('category') || '').trim(),
            duration_minutes: Number(fd.get('duration_minutes') || 30),
            passing_score: Number(fd.get('passing_score') || 50),
            is_published: fd.get('is_published') ? 1 : 0
        };
    }

    metaForm.addEventListener('submit', async e => {
        e.preventDefault();
        const payload = metaPayload();
        try {
            if (!examId) {
                const data = await Examify.api('/exams', { method: 'POST', body: JSON.stringify(payload) });
                sessionStorage.setItem('pendingToast', 'Đã tạo đề. Bây giờ hãy thêm câu hỏi.');
                window.location.replace(Examify.url(`/teacher/exam_editor.php?id=${Number(data.id)}`));

            } else {
                await Examify.api(`/exams/${examId}`, { method: 'PUT', body: JSON.stringify(payload) });
                Examify.toast('Đã lưu thông tin đề.');
            }
        } catch (err) {
            Examify.toast(err.message, 'error');
        }
    });

    if (!examId) return;

    function newQuestion() {
        return {
            id: 0,
            content: '',
            points: 1,
            choices: [
                { content: '', is_correct: 1 },
                { content: '', is_correct: 0 },
                { content: '', is_correct: 0 },
                { content: '', is_correct: 0 }
            ]
        };
    }

    function render() {
        questionList.innerHTML = questions.length ? questions.map((q, i) => `
            <article class="question-editor" data-index="${i}">
                <div class="question-editor-head">
                    <strong>Câu ${i + 1}</strong>
                    <button class="icon-btn remove-question" type="button">×</button>
                </div>
                <label>Nội dung câu hỏi
                    <textarea class="q-content" rows="2">${Examify.escapeHtml(q.content || '')}</textarea>
                </label>
                <label>Điểm
                    <input class="q-points" type="number" step="0.01" min="0.01" value="${Number(q.points || 1)}">
                </label>
                <div class="choice-editor-list">
                    ${(q.choices || []).map((c, ci) => `
                        <div class="choice-edit">
                            <input type="radio" name="correct_${i}" class="c-correct" ${Number(c.is_correct) ? 'checked' : ''} aria-label="Đáp án đúng">
                            <input class="c-content" value="${Examify.escapeHtml(c.content || '')}" placeholder="Lựa chọn ${ci + 1}">
                        </div>`).join('')}
                </div>
                <div class="actions">
                    <button class="btn primary save-question" type="button">${Number(q.id) ? 'Lưu câu hỏi' : 'Thêm câu hỏi'}</button>
                    <span class="muted small">${Number(q.id) ? 'ID #' + Number(q.id) : 'Chưa lưu'}</span>
                </div>
            </article>
        `).join('') : '<div class="empty">Chưa có câu hỏi. Hãy thêm câu đầu tiên.</div>';
    }

    function payloadFromCard(card) {
        return {
            content: card.querySelector('.q-content').value.trim(),
            points: Number(card.querySelector('.q-points').value || 1),
            choices: [...card.querySelectorAll('.choice-edit')].map(row => ({
                content: row.querySelector('.c-content').value.trim(),
                is_correct: row.querySelector('.c-correct').checked ? 1 : 0
            }))
        };
    }

    addBtn?.addEventListener('click', () => {
        questions.push(newQuestion());
        render();
        questionList.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    questionList.addEventListener('click', async e => {
        const card = e.target.closest('.question-editor');
        if (!card) return;
        const index = Number(card.dataset.index);
        const q = questions[index];

        if (e.target.closest('.save-question')) {
            try {
                const payload = payloadFromCard(card);
                if (Number(q.id)) {
                    await Examify.api(`/questions/${Number(q.id)}`, { method: 'PUT', body: JSON.stringify(payload) });
                    Object.assign(q, payload);
                    Examify.toast('Đã cập nhật câu hỏi.');
                } else {
                    const data = await Examify.api(`/exams/${examId}/questions`, { method: 'POST', body: JSON.stringify(payload) });
                    Object.assign(q, payload, { id: Number(data.id) });
                    Examify.toast('Đã thêm câu hỏi.');
                }
                render();
            } catch (err) {
                Examify.toast(err.message, 'error');
            }
        }

        if (e.target.closest('.remove-question')) {
            if (!confirm('Xóa câu hỏi này?')) return;
            try {
                if (Number(q.id)) {
                    await Examify.api(`/questions/${Number(q.id)}`, { method: 'DELETE', body: '{}' });
                }
                questions.splice(index, 1);
                render();
                Examify.toast('Đã xóa câu hỏi.');
            } catch (err) {
                Examify.toast(err.message, 'error');
            }
        }
    });

    render();
})();
