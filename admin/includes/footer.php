<?php
/**
 * Admin Layout Footer
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */
?>
        </main>
        
        <!-- Admin Footer -->
        <footer class="bg-white border-top py-3 px-4 text-center text-sm-start d-flex flex-column flex-sm-row justify-content-between align-items-center text-muted small">
            <div>
                &copy; <?= date('Y') ?> <strong><?= e(get_setting('site_name', ORG_NAME)) ?></strong>. Admin System.
            </div>
            <div>
                Reg: <strong><?= e(get_setting('reg_number', ORG_REG_NO)) ?></strong> (Sri Lanka)
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Admin JS -->
    <script src="<?= ASSETS_URL ?>/js/admin.js?v=1.0.1"></script>
</body>
</html>
