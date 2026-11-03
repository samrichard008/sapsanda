<?php
/**
 * Volunteer CSV Exporter
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 */

require_once __DIR__ . '/includes/auth.php';

$db = get_db();
$district = clean_input($_GET['district'] ?? '');
$status = clean_input($_GET['status'] ?? '');

$sql = "SELECT reference_no, full_name, dob, nic_passport, gender, phone, email, address, district, occupation, skills, areas_of_interest, availability, previous_experience, status, created_at FROM volunteers WHERE 1=1";
$params = [];

if (!empty($district)) {
    $sql .= " AND district = ?";
    $params[] = $district;
}
if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Set CSV download headers
$filename = 'sapsada_volunteers_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel Unicode display
fputs($output, "\xEF\xBB\xBF");

// CSV Header row
fputcsv($output, [
    'Reference No',
    'Full Name',
    'Date of Birth',
    'NIC/Passport',
    'Gender',
    'Phone',
    'Email',
    'Address',
    'District',
    'Occupation',
    'Skills',
    'Areas of Interest',
    'Availability',
    'Previous Experience',
    'Status',
    'Registered Date'
]);

// Write records
foreach ($rows as $row) {
    fputcsv($output, [
        $row['reference_no'],
        $row['full_name'],
        $row['dob'] ?: 'N/A',
        $row['nic_passport'] ?: 'N/A',
        $row['gender'],
        $row['phone'],
        $row['email'],
        $row['address'],
        $row['district'],
        $row['occupation'] ?: 'N/A',
        $row['skills'] ?: 'N/A',
        $row['areas_of_interest'] ?: 'N/A',
        $row['availability'] ?: 'N/A',
        $row['previous_experience'] ?: 'N/A',
        $row['status'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
