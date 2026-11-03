<?php
/**
 * Contact Us Page
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'contact';
$custom_page_title = 'Contact Us | Headquarters, Hotline & Location';
require_once __DIR__ . '/includes/header.php';

$db = get_db();

$success_msg = null;
$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security token validation failed. Please refresh and try again.';
    } else {
        $name = clean_input($_POST['name'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $subject = clean_input($_POST['subject'] ?? 'General Inquiry');
        $message = clean_input($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $error_msg = 'Please fill all required fields (Name, Email, Message).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Please provide a valid email address.';
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO contact_messages (name, email, phone, subject, message)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $email, $phone, $subject, $message]);

                // Create Admin notification
                create_notification(
                    'contact',
                    'New Public Message',
                    "{$name} sent an inquiry: {$subject}",
                    'messages.php'
                );

                $success_msg = __('contact_success');
            } catch (Exception $e) {
                $error_msg = 'Failed to send message. Please reach out via our direct telephone hotlines.';
            }
        }
    }
}

$whatsapp_clean = preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', ORG_WHATSAPP));
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">We Are Here For You</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('contact_title') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('contact_subtitle') ?></p>
    </div>
</div>

<section class="section-padding bg-light">
    <div class="container">
        <!-- Success Alert -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-5 text-center">
                <i class="fas fa-check-circle fs-1 text-success mb-3"></i>
                <h4 class="fw-bold text-navy">Message Sent Successfully!</h4>
                <p class="text-secondary mb-0"><?= e($success_msg) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= e($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white">
                    <h3 class="fw-bold text-navy mb-2"><?= __('contact_form_title') ?></h3>
                    <p class="text-muted small mb-4">Have an inquiry or wish to invite us for a community life protection or cleanup drill? Send us a message.</p>

                    <form action="contact.php" method="POST">
                        <?= csrf_field() ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('contact_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Your Full Name" value="<?= e($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('contact_email') ?> <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('contact_phone') ?></label>
                                <input type="tel" name="phone" class="form-control" placeholder="07XXXXXXXX" value="<?= e($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('contact_subject') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required placeholder="Inquiry Subject" value="<?= e($_POST['subject'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-navy"><?= __('contact_message') ?> <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="How can we assist you?"><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" name="submit_contact" class="btn btn-sapsada-primary btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i> <?= __('contact_submit_btn') ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Official Contact Cards -->
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-3">
                    <!-- Headquarters Address Card -->
                    <div class="card border-0 shadow-card rounded-4 p-4 bg-white border-start border-5 border-primary">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-sky-light text-primary p-3 rounded-circle fs-4">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-navy mb-1">Administrative Headquarters</h6>
                                <p class="text-secondary small mb-0"><?= e(get_setting('address_physical', ORG_ADDRESS)) ?></p>
                                <div class="mt-2">
                                    <span class="badge bg-light text-navy border">
                                        <?= __('reg_number_label') ?>: <?= e(get_setting('reg_number', ORG_REG_NO)) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Hotline Card -->
                    <div class="card border-0 shadow-card rounded-4 p-4 bg-white border-start border-5 border-teal">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-sky-light text-teal p-3 rounded-circle fs-4">
                                <i class="fas fa-phone-volume"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-navy mb-1">Telephone Hotlines</h6>
                                <div class="small text-secondary mb-2">Available for public inquiries and emergencies:</div>
                                <div class="d-flex flex-column gap-1">
                                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_primary', ORG_PHONE_1)) ?>" class="fw-bold text-navy text-decoration-none">
                                        <i class="fas fa-phone me-1 text-teal"></i> <?= e(get_setting('phone_primary', ORG_PHONE_1)) ?>
                                    </a>
                                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_secondary', ORG_PHONE_2)) ?>" class="fw-bold text-navy text-decoration-none">
                                        <i class="fas fa-phone me-1 text-teal"></i> <?= e(get_setting('phone_secondary', ORG_PHONE_2)) ?>
                                    </a>
                                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_hotline', ORG_HOTLINE)) ?>" class="fw-bold text-navy text-decoration-none">
                                        <i class="fas fa-mobile-alt me-1 text-teal"></i> <?= e(get_setting('phone_hotline', ORG_HOTLINE)) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email & WhatsApp Card -->
                    <div class="card border-0 shadow-card rounded-4 p-4 bg-white border-start border-5 border-gold">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-sky-light text-warning p-3 rounded-circle fs-4">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-navy mb-1">Digital Channels</h6>
                                <div class="small mb-3">
                                    <strong>Email:</strong> 
                                    <a href="mailto:<?= e(get_setting('email_official', ORG_EMAIL)) ?>" class="text-teal text-decoration-none">
                                        <?= e(get_setting('email_official', ORG_EMAIL)) ?>
                                    </a>
                                </div>
                                <a href="https://wa.me/<?= $whatsapp_clean ?>?text=<?= urlencode('Hello SAP SADA Unit, I have an inquiry.') ?>" target="_blank" class="btn btn-success btn-sm w-100">
                                    <i class="fab fa-whatsapp me-1"></i> <?= __('whatsapp_chat_btn') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps Embed -->
        <div class="mt-5">
            <div class="card border-0 shadow-card rounded-4 overflow-hidden">
                <iframe 
                    src="<?= e(get_setting('google_map_embed', 'https://maps.google.com/maps?q=Nalluruwa,Panadura,Sri+Lanka&t=&z=14&ie=UTF8&iwloc=&output=embed')) ?>" 
                    width="100%" 
                    height="380" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
