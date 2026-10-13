<?php
/**
 * Canonical laboratory-test catalogue (name-keyed).
 *
 * Single source of truth for the laboratory tests that can be ordered from the
 * Doctor Station investigation checkboxes (consultation_form.php) and priced in
 * Service Prices (services.php?type=lab_test). Entries are plain test names and
 * match service_prices.service_ref for service_type = 'lab_test'.
 *
 * Add a test here and it becomes selectable on the form and available to price
 * in one place, instead of the two lists drifting apart.
 */
function labTestCatalogue(): array {
    return [
        'Malaria Parasite / RDT',
        'Full Blood Count (FBC)',
        'Urinalysis',
        'Widal Test (Typhoid)',
        'Blood Sugar (RBS / FBS)',
    ];
}
