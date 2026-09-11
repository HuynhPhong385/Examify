<?php
require __DIR__ . '/../app/bootstrap.php';
require_auth_page();
$pageTitle = 'Danh sách đề thi - Examify';
require __DIR__ . '/_header.php';
?>
<section class="page-head">
    <div>
        <h1><?= $user['role']==='student' ? 'Đề thi đang mở' : 'Đề thi đang quản lý' ?></h1>
        <p class="muted">Tìm kiếm tức thời theo tên, mô tả hoặc chuyên mục.</p>
    </div>
</section>

<div class="search-wrap">
    <input id="liveSearch" type="search" placeholder="Gõ để tìm đề thi..." autocomplete="off">
    <div id="searchDropdown" class="search-dropdown hidden"></div>
</div>

<div id="examGrid" class="cards-grid">
    <div class="skeleton">Đang tải danh sách đề...</div>
</div>
<nav id="examPagination" class="pagination-wrap" aria-label="Phân trang đề thi"></nav>
<script>
window.EXAMIFY_PAGE = 'exams';
</script>
<?php
$extraScripts = ['/assets/js/exams.js'];
require __DIR__ . '/_footer.php';
?>
