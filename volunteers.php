<?php
/**
 * Volunteer Registration Portal
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

$active_page = 'volunteers';
$custom_page_title = 'Volunteer Registration Portal | Join Our Team';
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$districts = get_sri_lanka_districts();

$success_ref = null;
$error_msg = null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_volunteer'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Security validation failed (CSRF). Please refresh and submit again.';
    } else {
        $full_name = clean_input($_POST['full_name'] ?? '');
        $dob = !empty($_POST['dob']) ? clean_input($_POST['dob']) : null;
        $nic = clean_input($_POST['nic_passport'] ?? '');
        $gender = clean_input($_POST['gender'] ?? 'Male');
        $phone = clean_input($_POST['phone'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $address = clean_input($_POST['address'] ?? '');
        $district = clean_input($_POST['district'] ?? '');
        $occupation = clean_input($_POST['occupation'] ?? '');
        $skills = clean_input($_POST['skills'] ?? '');
        $areas = is_array($_POST['areas_of_interest'] ?? null) ? implode(', ', $_POST['areas_of_interest']) : clean_input($_POST['areas_of_interest'] ?? '');
        $availability = clean_input($_POST['availability'] ?? '');
        $experience = clean_input($_POST['previous_experience'] ?? '');
        $message = clean_input($_POST['message'] ?? '');
        $privacy = isset($_POST['privacy_accepted']) ? 1 : 0;

        // Validation
        if (empty($full_name) || empty($phone) || empty($email) || empty($address) || empty($district)) {
            $error_msg = 'Please fill all required fields (Full Name, Phone, Email, Address, District).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Please provide a valid email address.';
        } elseif (!$privacy) {
            $error_msg = 'Please acknowledge the privacy consent to complete registration.';
        } else {
            try {
                $ref_no = generate_volunteer_reference();
                
                $stmt = $db->prepare("
                    INSERT INTO volunteers 
                    (reference_no, full_name, dob, nic_passport, gender, phone, email, address, district, occupation, skills, areas_of_interest, availability, previous_experience, message, privacy_accepted, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
                ");
                $stmt->execute([
                    $ref_no, $full_name, $dob, $nic, $gender, $phone, $email, $address, $district, $occupation, $skills, $areas, $availability, $experience, $message, $privacy
                ]);

                // Create Admin notification
                create_notification(
                    'volunteer',
                    'New Volunteer Application',
                    "{$full_name} submitted an application from {$district} (Ref: {$ref_no}).",
                    'volunteers.php'
                );

                $success_ref = $ref_no;
            } catch (Exception $e) {
                $error_msg = 'An error occurred while saving your application. Please try again or contact our hotline.';
            }
        }
    }
}
?>

<!-- Header Banner -->
<div class="bg-navy py-5 text-white" style="background: linear-gradient(135deg, var(--color-navy-dark) 0%, var(--color-blue-deep) 100%);">
    <div class="container py-4">
        <span class="badge bg-gold text-dark px-3 py-2 rounded-pill fw-bold mb-2">Be The Change</span>
        <h1 class="display-5 fw-bold text-white mb-2"><?= __('vol_title') ?></h1>
        <p class="lead text-sky-light mb-0"><?= __('vol_subtitle') ?></p>
    </div>
</div>

<!-- Volunteer Content & Form -->
<section class="section-padding bg-light">
    <div class="container">
        <!-- Success Alert Modal / Box -->
        <?php if ($success_ref): ?>
            <div class="alert alert-success border-0 shadow-card rounded-4 p-4 p-md-5 mb-5 text-center">
                <div class="display-3 text-success mb-3"><i class="fas fa-check-circle"></i></div>
                <h3 class="fw-bold text-navy mb-2"><?= __('vol_success_title') ?></h3>
                <p class="text-secondary mb-3"><?= __('vol_success_msg') ?></p>
                <div class="d-inline-block bg-white text-navy px-4 py-2 rounded-3 border border-2 border-success fs-3 fw-extrabold mb-4 letter-spacing-1">
                    <?= e($success_ref) ?>
                </div>
                <p class="text-muted small max-w-600 mx-auto mb-4"><?= __('vol_success_note') ?></p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', ORG_WHATSAPP)) ?>?text=<?= urlencode("Hello SAP SADA, I submitted volunteer application reference: {$success_ref}") ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp me-1"></i> Verify On WhatsApp
                    </a>
                    <a href="volunteers.php" class="btn btn-outline-secondary btn-sm">Submit Another</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= e($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="row g-5">
            <!-- Form Column -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-card rounded-4 p-4 p-md-5 bg-white">
                    <h4 class="fw-bold text-navy mb-4"><i class="fas fa-user-edit text-teal me-2"></i>Application Form</h4>
                    
                    <form action="volunteers.php" method="POST" class="needs-validation">
                        <?= csrf_field() ?>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-navy"><?= __('vol_fullname') ?> <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. Kasun Bandara Perera" required value="<?= e($_POST['full_name'] ?? '') ?>">
                        </div>

                        <!-- DOB, NIC, Gender -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_dob') ?></label>
                                <input type="date" name="dob" class="form-control" value="<?= e($_POST['dob'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_nic') ?></label>
                                <input type="text" name="nic_passport" class="form-control" placeholder="NIC or Passport No." value="<?= e($_POST['nic_passport'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_gender') ?></label>
                                <select name="gender" class="form-select">
                                    <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>><?= __('vol_male') ?></option>
                                    <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>><?= __('vol_female') ?></option>
                                    <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>><?= __('vol_other') ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Contact: Phone & Email -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_phone') ?> <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" placeholder="07XXXXXXXX" required value="<?= e($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_email') ?> <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="your.name@example.com" required value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Address & District -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_address') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="address" class="form-control" placeholder="House No, Street, City" required value="<?= e($_POST['address'] ?? '') ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_district') ?> <span class="text-danger">*</span></label>
                                <select name="district" class="form-select" required>
                                    <option value="">-- Select District --</option>
                                    <?php foreach ($districts as $d): ?>
                                        <option value="<?= e($d) ?>" <?= (($_POST['district'] ?? 'Kalutara') === $d) ? 'selected' : '' ?>><?= e($d) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Occupation & Skills -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_occupation') ?></label>
                                <input type="text" name="occupation" class="form-control" placeholder="e.g. Teacher, Student, Diver, IT" value="<?= e($_POST['occupation'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-navy"><?= __('vol_availability') ?></label>
                                <select name="availability" class="form-select">
                                    <option value="Weekends">Weekends Only</option>
                                    <option value="Weekdays">Weekdays</option>
                                    <option value="Flexible / Anytime">Flexible / Anytime</option>
                                    <option value="Emergency On-Call Only">Emergency On-Call Only</option>
                                </select>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-navy"><?= __('vol_skills') ?></label>
                            <input type="text" name="skills" class="form-control" placeholder="e.g. Swimming, First Aid, Mechanical, Driving, Event Logistics, Photography" value="<?= e($_POST['skills'] ?? '') ?>">
                        </div>

                        <!-- Areas of Interest (Checkboxes) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-navy d-block"><?= __('vol_interests') ?></label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Life Protection & Water Rescue" id="int1" checked>
                                        <label class="form-check-label small" for="int1">Life Protection &amp; Aquatic Safety</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Environmental Canal Cleanup" id="int2" checked>
                                        <label class="form-check-label small" for="int2">Environmental &amp; Canal Cleanup</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Community & Women Welfare" id="int3">
                                        <label class="form-check-label small" for="int3">Community &amp; Women Empowerment</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="Emergency Disaster Relief" id="int4">
                                        <label class="form-check-label small" for="int4">Emergency Flood &amp; Disaster Relief</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Experience -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-navy"><?= __('vol_experience') ?></label>
                            <textarea name="previous_experience" class="form-control" rows="2" placeholder="Briefly describe any prior voluntary or community experience"><?= e($_POST['previous_experience'] ?? '') ?></textarea>
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-navy"><?= __('vol_message') ?></label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Tell us about your motivation to join SAP SADA"><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <!-- Privacy Consent -->
                        <div class="form-check mb-4 p-3 bg-light rounded-3">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="privacy_accepted" id="privacyCheck" required checked>
                            <label class="form-check-label small text-secondary" for="privacyCheck">
                                <?= __('vol_privacy') ?>
                            </label>
                        </div>

                        <button type="submit" name="submit_volunteer" class="btn btn-sapsada-teal btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i> <?= __('vol_submit_btn') ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-card rounded-4 p-4 bg-navy text-white mb-4">
                    <h5 class="fw-bold text-white mb-3"><i class="fas fa-heart text-gold me-2"></i>Why Volunteer with Us?</h5>
                    <ul class="list-unstyled small text-sky-light d-flex flex-column gap-3 mb-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-teal mt-1"></i>
                            <span>Gain real training in aquatic life protection, open-water drills, and CPR.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-teal mt-1"></i>
                            <span>Directly protect ecosystems through wetland and waterway restoration.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-teal mt-1"></i>
                            <span>Earn official organization volunteer service recognition and certificates.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle text-teal mt-1"></i>
                            <span>Connect with nationwide human rights advocates and community developers.</span>
                        </li>
                    </ul>
                </div>

                <div class="card border-0 shadow-card rounded-4 p-4 bg-white">
                    <h6 class="fw-bold text-navy mb-2"><i class="fas fa-question-circle text-teal me-2"></i>Questions?</h6>
                    <p class="small text-secondary mb-3">Our volunteer coordination desk is available directly via telephone or WhatsApp.</p>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('phone_primary', ORG_PHONE_1)) ?>" class="btn btn-outline-primary w-100 btn-sm mb-2">
                        <i class="fas fa-phone-alt me-1"></i> Call 070 7261978
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', ORG_WHATSAPP)) ?>" target="_blank" class="btn btn-outline-success w-100 btn-sm">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp Helpline
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
