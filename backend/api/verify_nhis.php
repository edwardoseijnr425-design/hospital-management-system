<?php
/**
 * NHIS eligibility + Claim Check Code (CCC) verification.
 *
 * Called by the "Verify NHIS & Get CCC" button on Patient Registration.
 * Accepts POST form-data or JSON: { nhis_number: "202683952" }
 *
 * Response contract (consumed by frontend/pages/register-patient.php):
 *   success -> { status: "success", claim_check_code, expiry_date, source, mock }
 *   failure -> { status: "error", message }
 *
 * Eligibility is resolved in two stages, in this order:
 *
 *   1. LOCAL DATABASE (authoritative). Matches patient_registrations.nhia_number
 *      and reads the linked sponsors.nhia_status / sponsors.expiry_date (falling
 *      back to the default NHIA sponsor row when the patient has none linked).
 *      A patient row that is found is never overridden by stage 2.
 *
 *   2. SIMULATED FALLBACK ($MOCK_FALLBACK). There is no live NHIA/DHIMS
 *      eligibility service wired up yet, so an unrecognised-but-well-formed card
 *      number gets a deterministic simulated response so the registration UI can
 *      be exercised end to end. Every simulated response carries source="mock"
 *      and mock=true, and the UI labels it as simulated. Once the real NHIA
 *      service is available, set $MOCK_FALLBACK to false (or replace that block)
 *      so unknown numbers are reported as ineligible instead of "active".
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

/**
 * Simulated-eligibility switch. Keep this paired with a visible "simulated"
 * marker in the UI; never let it silently certify a patient for NHIA billing.
 */
$MOCK_FALLBACK = true;

// NHIS card numbers are printed as digits (8-13 covers old and current cards).
const NHIS_NUMBER_PATTERN = '/^[0-9]{8,13}$/';

$data = getPostData();
$nhisNumber = trim((string)($data['nhis_number'] ?? ''));

if ($nhisNumber === '') {
    jsonResponse(['status' => 'error', 'message' => 'NHIS card number is required']);
}

$db = Database::getInstance();

// ---------------------------------------------------------------- stage 1: DB
$patient = $db->fetchOne(
    "SELECT p.id,
            p.hospital_number,
            p.first_name,
            p.middle_name,
            p.last_name,
            p.nhia_number,
            s.name        AS sponsor_name,
            s.code        AS sponsor_code,
            s.nhia_status AS sponsor_nhia_status,
            s.expiry_date AS sponsor_expiry
       FROM patient_registrations p
       LEFT JOIN sponsors s ON s.id = p.sponsor_id
      WHERE p.nhia_number = ?
      ORDER BY p.id DESC
      LIMIT 1",
    [$nhisNumber]
);

if ($patient) {
    // Patient may have no sponsor_id at all; fall back to the seeded NHIA sponsor
    // so a linked-card lookup still reflects the scheme's current status.
    $nhiaStatus = $patient['sponsor_nhia_status'];
    $expiryDate = $patient['sponsor_expiry'];

    if ($nhiaStatus === null) {
        $nhia = $db->fetchOne(
            "SELECT nhia_status, expiry_date FROM sponsors WHERE type = 'nhia' AND is_active = 1 ORDER BY id LIMIT 1"
        );
        if ($nhia) {
            $nhiaStatus = $nhia['nhia_status'];
            $expiryDate = $expiryDate ?: $nhia['expiry_date'];
        }
    }

    $expired = $expiryDate !== null && $expiryDate !== '' && strtotime($expiryDate) < strtotime(date('Y-m-d'));

    if ($nhiaStatus === 'active' && !$expired) {
        nhisVerifyRespond([
            'patient_id'   => (int)$patient['id'],
            'hospital_number' => $patient['hospital_number'],
            'patient_name' => trim(($patient['first_name'] ?? '') . ' ' . ($patient['middle_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')),
            'source'       => 'database',
            'nhia_status'  => 'active',
            'expiry_date'  => $expiryDate ?: null,
        ], $nhisNumber);
    }

    $reason = $expired
        ? 'NHIS card expired on ' . date('d M Y', strtotime($expiryDate))
        : 'NHIS card is ' . ($nhiaStatus !== null ? $nhiaStatus : 'not active') . ' on record';

    logAudit('VERIFY', 'nhis', (int)$patient['id'], null, [
        'nhis_number' => $nhisNumber,
        'result'      => 'inactive',
        'reason'      => $reason,
    ]);

    jsonResponse([
        'status'      => 'error',
        'source'      => 'database',
        'mock'        => false,
        'nhia_status' => $expired ? 'expired' : ($nhiaStatus ?: 'inactive'),
        'message'     => $reason . '. Bill this visit on the cash tariff.',
    ]);
}

// ------------------------------------------- stage 2: simulated (see header)
if ($MOCK_FALLBACK) {
    if (preg_match(NHIS_NUMBER_PATTERN, $nhisNumber)) {
        // Deterministic so repeated checks during a demo return the same values.
        $expiryDate = date('Y-m-d', strtotime('+1 year'));

        logAudit('VERIFY', 'nhis', null, null, [
            'nhis_number' => $nhisNumber,
            'result'      => 'simulated_active',
        ]);

        nhisVerifyRespond([
            'patient_id'   => null,
            'source'       => 'mock',
            'nhia_status'  => 'active',
            'expiry_date'  => $expiryDate,
            'message'      => 'Simulated eligibility: no patient record matches this card number.',
        ], $nhisNumber);
    }

    jsonResponse([
        'status'  => 'error',
        'source'  => 'mock',
        'mock'    => false,
        'message' => 'Card number ' . $nhisNumber . ' is not a valid NHIS number (expected 8-13 digits).',
    ]);
}

jsonResponse([
    'status'  => 'error',
    'source'  => 'database',
    'mock'    => false,
    'message' => 'No NHIS record found for card number ' . $nhisNumber . '. Bill this visit on the cash tariff.',
]);

/**
 * Emits an ACTIVE/success payload, deriving the Claim Check Code.
 *
 * The HMS schema stores no NHIA-issued CCC (the NHIA portal issues one at the
 * point of claim), so the code is derived deterministically from the card
 * number, the claim month and the validated expiry. That keeps it stable for
 * every check inside one month while remaining unique per card.
 */
function nhisVerifyRespond(array $info, $nhisNumber) {
    $claimCheckCode = nhisDeriveClaimCheckCode($nhisNumber, $info['expiry_date'] ?? null);

    jsonResponse([
        'status'          => 'success',
        'claim_check_code' => $claimCheckCode,
        'expiry_date'     => $info['expiry_date'] ?? null,
        'nhia_status'     => $info['nhia_status'] ?? 'active',
        'source'          => $info['source'] ?? 'database',
        'mock'            => ($info['source'] ?? '') === 'mock',
        'patient_id'      => $info['patient_id'] ?? null,
        'hospital_number' => $info['hospital_number'] ?? null,
        'patient_name'    => $info['patient_name'] ?? null,
        'message'         => $info['message'] ?? 'NHIS eligibility confirmed.',
    ]);
}

function nhisDeriveClaimCheckCode($nhisNumber, $expiryDate) {
    $claimMonth = date('Y-m'); // NHIA issues a fresh code per claim month.
    $digest = hash('sha256', strtoupper($nhisNumber) . '|' . $claimMonth . '|' . (string)$expiryDate);
    $digits = preg_replace('/[^0-9]/', '', $digest);

    return str_pad(substr($digits, 0, 8), 8, '0', STR_PAD_LEFT);
}
