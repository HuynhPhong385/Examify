</main>
<div id="toast" class="toast" aria-live="polite"></div>
<script src="<?= e(base_url('/assets/js/jquery.min.js')) ?>"></script>
<script src="<?= e(base_url('/assets/js/app.js')) ?>"></script>
<?php if (!empty($extraScripts)): ?>
    <?php foreach ($extraScripts as $src): ?>
        <script src="<?= e(base_url($src)) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
