<?php
/**
 * Transparent Donation Portal
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'donate';
$custom_page_title = 'Transparent Donation Portal | Direct Bank Details';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// 1. Fetch Active Donation Campaigns
$campaigns = [];
try {
    $stmt = $db->query("SELECT * FROM donation_campaigns WHERE status = 'active' ORDER BY id ASC");
    $campaigns = $stmt->fetchAll();
} catch (Exception $e) {}

$success_msg = null;
$error_msg = null;

// Handle Donation Notification Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation_enquiry'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security validation failed. Please refresh the page and try again.';
    } else {
        $donor_name = clean_input($_POST['donor_name'] ?? '');
        $email = clean_input($_POST['donor_email'] ?? '');
        $phone = clean_input($_POST['donor_phone'] ?? '');
        $amount = !empty($_POST['donor_amount']) ? (float)$_POST['donor_amount'] : null;
        $campaign_id = !empty($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : null;
        $transfer_ref = clean_input($_POST['donor_ref'] ?? '');
        $message = clean_input($_POST['donor_message'] ?? '');

        if (empty($donor_name) || empty($phone) || empty($email)) {
            $error_msg = 'Please provide your Name, Email, and Telephone/WhatsApp number.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO donation_enquiries 
                    (donor_name, email, phone, campaign_id, amount_declared, transfer_reference, payment_method, message, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'Bank Transfer', ?, 'Pending')
                ");
                $stmt->execute([$donor_name, $email, $phone, $campaign_id, $amount, $transfer_ref, $message]);

                // Notify Admin
                create_notification(
                    'donation',
                    'New Donation Bank Slip Notification',
                    "{$donor_name} notified bank transfer of LKR " . number_format($amount ?? 0) . " (Ref: {$transfer_ref}).",
                    'donations.php'
                );

                $success_msg = 'Thank you! Your bank transfer notification has been submitted. Our finance committee will verify the transaction and issue your official acknowledgement receipt.';
            } catch (Exception $e) {
                $error_msg = 'Failed to submit notification. Please contact the office directly.';
            }
        }
    }
}
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">100% Financial Integrity</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('donate_title') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('donate_subtitle') ?></p>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container">
        <?php if ($success_msg): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-5 text-center">
                <i class="fas fa-check-circle fs-1 text-success mb-3"></i>
                <h4 class="fw-bold text-navy">Notification Successfully Received!</h4>
                <p class="text-secondary mb-0"><?= e($success_msg) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= e($error_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Active Campaigns -->
        <div class="mb-5">
            <div class="text-center mb-5">
                <span class="section-badge">Direct Impact</span>
                <h2 class="section-title">Active Community Welfare Funds</h2>
                <p class="text-muted">Directly allocate your support to an active life-saving or environmental initiative.</p>
            </div>

            <div class="row g-4">
                <?php foreach ($campaigns as $camp): ?>
                    <?php 
                        $pct = $camp['target_amount'] > 0 ? min(100, round(($camp['raised_amount'] / $camp['target_amount']) * 100)) : 0;
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="sapsada-card">
                            <div class="card-img-wrap">
                                <span class="card-badge bg-gold text-dark fw-bold">Active Fund</span>
                                <img src="<?= ASSETS_URL ?>/../<?= e($camp['image_path']) ?>" alt="<?= e($camp['title']) ?>">
                            </div>
                            <div class="card-body-sapsada">
                                <h5 class="fw-bold text-navy mb-2"><?= e($camp['title']) ?></h5>
                                <p class="text-secondary small mb-3 flex-grow-1"><?= e($camp['description']) ?></p>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small fw-bold text-navy mb-1">
                                        <span>Raised: LKR <?= number_format($camp['raised_amount']) ?></span>
                                        <span>Target: LKR <?= number_format($camp['target_amount']) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-teal" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <a href="#donationFormSection" class="btn btn-sapsada-gold btn-sm w-100" onclick="document.getElementById('campaignSelect').value='<?= $camp['id'] ?>'">
                                    <i class="fas fa-heart me-1"></i> Support This Fund
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bank Details and Transfer Form -->
        <div class="row g-5 align-items-start" id="donationFormSection">
            <!-- Official Bank Account Card -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-navy text-white">
                    <span class="badge bg-gold text-dark px-3 py-1 rounded-pill fw-bold align-self-start mb-3">
                        <i class="fas fa-university me-1"></i> Official Banking
                    </span>
                    <h4 class="fw-bold text-white mb-3"><?= __('donate_bank_heading') ?></h4>
                    <p class="small text-sky-light mb-4"><?= __('donate_bank_notice') ?></p>

                    <div class="p-3 bg-white bg-opacity-10 rounded-3 mb-4">
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-0 small">
                            <li><strong><?= __('bank_name_label') ?>:</strong> <span class="text-cyan"><?= e(get_setting('bank_name', 'Commercial Bank of Ceylon / People\'s Bank')) ?></span></li>
                            <li><strong><?= __('bank_account_name_label') ?>:</strong> <span class="text-white"><?= e(get_setting('bank_account_name', ORG_NAME)) ?></span></li>
                            <li><strong><?= __('bank_account_no_label') ?>:</strong> <span class="text-gold fw-bold"><?= e(get_setting('bank_account_no', 'Pending verification - Please contact office prior to direct deposit')) ?></span></li>
                            <li><strong><?= __('bank_branch_label') ?>:</strong> <span class="text-cyan"><?= e(get_setting('bank_branch', 'Panadura Branch')) ?></span></li>
                            <li><strong><?= __('bank_swift_label') ?>:</strong> <span class="text-cyan"><?= e(get_setting('bank_swift', 'CCEYLKJA')) ?></span></li>
                        </ul>
                    </div>

                    <div class="alert alert-warning border-0 rounded-3 small p-3 text-dark mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Transparency Guarantee:</strong> SAP SADA never utilizes unauthorized personal agents. All contributions must be remitted strictly to the recognized organization account.
                    </div>

                    <div class="text-sky-light small">
                        For institutional partnerships or corporate CSR donations, please contact our administrative desk directly at <a href="mailto:<?= e(get_setting('email_official', ORG_EMAIL)) ?>" class="text-gold"><?= e(get_setting('email_official', ORG_EMAIL)) ?></a>.
                    </div>
                </div>
            </div>

            <!-- Transfer Slip Notification Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white">
                    <h4 class="fw-bold text-navy mb-2"><?= __('donate_enquiry_title') ?></h4>
                    <p class="text-muted small mb-4"><?= __('donate_enquiry_sub') ?></p>

                    <form action="donate.php" method="POST">
                        <?= csrf_field() ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('donor_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="donor_name" class="form-control" required placeholder="Your Full Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('donor_phone') ?> <span class="text-danger">*</span></label>
                                <input type="tel" name="donor_phone" class="form-control" required placeholder="07XXXXXXXX">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('donor_email') ?> <span class="text-danger">*</span></label>
                                <input type="email" name="donor_email" class="form-control" required placeholder="donor@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('donor_amount') ?></label>
                                <input type="number" step="0.01" name="donor_amount" class="form-control" placeholder="e.g. 5000">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('donor_ref') ?></label>
                                <input type="text" name="donor_ref" class="form-control" placeholder="Slip Ref / Cheque No">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('donor_campaign') ?></label>
                                <select name="campaign_id" id="campaignSelect" class="form-select">
                                    <option value="">General Social Welfare Fund</option>
                                    <?php foreach ($campaigns as $camp): ?>
                                        <option value="<?= $camp['id'] ?>"><?= e($camp['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-navy"><?= __('donor_message') ?></label>
                            <textarea name="donor_message" class="form-control" rows="3" placeholder="Optional notes, dedication, or physical receipt delivery address"></textarea>
                        </div>

                        <button type="submit" name="submit_donation_enquiry" class="btn btn-sapsada-primary btn-lg w-100">
                            <i class="fas fa-check-circle me-2"></i> <?= __('donor_submit_btn') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
