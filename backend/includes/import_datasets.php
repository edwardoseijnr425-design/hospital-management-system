<?php
// Import dataset whitelist for backend/api/import.php.
//
// Bulk import is a privileged, write-anywhere operation, so the tables and
// columns it may touch are fixed here rather than taken from the request. Each
// dataset declares:
//   label      human name shown in the importer
//   table      physical table
//   key_field  unique column used for upsert mode (null = insert only)
//   required   columns that must be present when inserting
//   columns    map of column => type
//
// Type grammar: string | int | decimal | bool | date | datetime | enum:a,b,c
//
// Deliberately NOT listed (financial / append-only / business-rules tables):
// invoices, billing_items, admission_payments, admissions, patient_visits,
// prescriptions/medication_dispensing, lab/radiology result tables,
// clinical_notes, bed_transfer_history, audit_trails, dhims_indicator_overrides.
// Those are written only through their own endpoints so occupancy, stock,
// posting and audit rules are not bypassed by a spreadsheet. Add a dataset here
// if that changes.

function hmsImportDatasetCatalog() {
    return [
        'patients' => [
            'label' => 'Patients',
            'table' => 'patient_registrations',
            'key_field' => 'hospital_number',
            'required' => ['hospital_number', 'first_name', 'last_name', 'date_of_birth', 'gender'],
            'defaults' => ['registered_by' => '@user', 'registration_date' => '@today'],
            'columns' => [
                'hospital_number' => 'string',
                'first_name' => 'string',
                'middle_name' => 'string',
                'last_name' => 'string',
                'date_of_birth' => 'date',
                'gender' => 'enum:male,female,other',
                'title' => 'string',
                'occupation' => 'string',
                'marital_status' => 'enum:Single,Married,Divorced,Widowed',
                'phone' => 'string',
                'secondary_phone' => 'string',
                'email' => 'string',
                'address' => 'string',
                'emergency_contact_name' => 'string',
                'emergency_contact_phone' => 'string',
                'blood_group' => 'enum:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'allergies' => 'string',
                'sponsor_id' => 'int',
                'nhia_number' => 'string',
                'claim_check_code' => 'string',
                'nhis_expiry_date' => 'date',
                'registration_date' => 'date',
                'is_active' => 'bool',
            ],
        ],
        'departments' => [
            'label' => 'Departments',
            'table' => 'departments',
            'key_field' => 'code',
            'required' => ['name', 'code'],
            'columns' => [
                'name' => 'string',
                'code' => 'string',
                'description' => 'string',
                'head_of_department' => 'int',
                'status' => 'enum:Active,Frozen,Deactivated',
            ],
        ],
        'medical_teams' => [
            'label' => 'Medical Teams',
            'table' => 'medical_teams',
            'key_field' => 'name',
            'required' => ['name'],
            'columns' => [
                'name' => 'string',
                'department_id' => 'int',
                'is_active' => 'bool',
            ],
        ],
        'services' => [
            'label' => 'Consultation Services',
            'table' => 'consultation_services',
            'key_field' => 'code',
            'required' => ['name', 'code'],
            'columns' => [
                'name' => 'string',
                'code' => 'string',
                'department_id' => 'int',
                'description' => 'string',
                'is_active' => 'bool',
            ],
        ],
        'procedures' => [
            'label' => 'Procedures',
            'table' => 'procedures',
            'key_field' => 'code',
            'required' => ['name', 'code', 'category'],
            'columns' => [
                'name' => 'string',
                'code' => 'string',
                'category' => 'enum:surgery,diagnostic,therapeutic,other',
                'department_id' => 'int',
                'description' => 'string',
                'duration_minutes' => 'int',
                'is_active' => 'bool',
            ],
        ],
        'prices' => [
            'label' => 'Service Prices / Tariffs',
            'table' => 'service_prices',
            'key_field' => null,
            'required' => ['service_type', 'service_id', 'price', 'effective_date'],
            'columns' => [
                'service_type' => 'enum:consultation,procedure,drug,lab_test,radiology,other',
                'service_id' => 'int',
                'price' => 'decimal',
                'copay_amount' => 'decimal',
                'currency' => 'string',
                'effective_date' => 'date',
                'is_active' => 'bool',
            ],
        ],
        'sponsors' => [
            'label' => 'Sponsors / Insurance',
            'table' => 'sponsors',
            'key_field' => 'code',
            'required' => ['name', 'code', 'type'],
            'columns' => [
                'name' => 'string',
                'code' => 'string',
                'type' => 'enum:nhia,private_insurance,corporate,individual,government',
                'contact_person' => 'string',
                'phone' => 'string',
                'email' => 'string',
                'address' => 'string',
                'is_active' => 'bool',
                'nhia_status' => 'enum:active,inactive,suspended',
                'expiry_date' => 'date',
            ],
        ],
        'wards' => [
            'label' => 'Wards',
            'table' => 'wards',
            'key_field' => 'ward_code',
            'required' => ['ward_code', 'ward_name', 'ward_type'],
            'columns' => [
                'ward_code' => 'string',
                'ward_name' => 'string',
                'ward_type' => 'enum:Male,Female,Pediatric,ICU,Maternity,General',
                'floor_level' => 'string',
                'capacity' => 'int',
                'status' => 'enum:Active,Maintenance,Full',
                'is_frozen' => 'bool',
            ],
        ],
        'rooms' => [
            'label' => 'Rooms',
            'table' => 'rooms',
            'key_field' => null,
            'required' => ['ward_id', 'room_number'],
            'columns' => [
                'ward_id' => 'int',
                'room_number' => 'string',
                'room_type' => 'enum:Standard,VIP,Isolation,Semi-Private',
                'status' => 'enum:Available,Occupied,Cleaning,Maintenance',
            ],
        ],
        'beds' => [
            'label' => 'Beds',
            'table' => 'beds',
            'key_field' => null,
            'required' => ['ward_id', 'bed_number'],
            'columns' => [
                'ward_id' => 'int',
                'room_id' => 'int',
                'bed_number' => 'string',
                'status' => 'enum:Available,Occupied,Reserved,Maintenance',
            ],
        ],
        'inventory' => [
            'label' => 'Pharmacy / Store Items',
            'table' => 'pharmacy_inventory',
            'key_field' => 'drug_code',
            'required' => ['drug_name', 'drug_code'],
            'columns' => [
                'drug_name' => 'string',
                'generic_name' => 'string',
                'drug_code' => 'string',
                'store_type' => 'enum:MEDICAL,GENERAL',
                'category' => 'string',
                'manufacturer' => 'string',
                'batch_number' => 'string',
                'expiry_date' => 'date',
                'quantity_in_stock' => 'int',
                'reorder_level' => 'int',
                'unit' => 'string',
                'storage_location' => 'string',
                'is_active' => 'bool',
            ],
        ],
        'appointments' => [
            'label' => 'Appointments',
            'table' => 'appointments',
            'key_field' => null,
            'required' => ['patient_id', 'appointment_date'],
            'defaults' => ['created_by' => '@user'],
            'columns' => [
                'patient_id' => 'int',
                'appointment_date' => 'datetime',
                'department_id' => 'int',
                'doctor_id' => 'int',
                'reason' => 'string',
                'consultation_type' => 'enum:OPD,ENT,EYE,EMERGENCY,GENERAL,SPECIALIST,FOLLOWUP,PEDIATRIC,DENTAL',
                'visit_type' => 'enum:New,Review',
                'status' => 'enum:scheduled,confirmed,completed,cancelled,no_show',
            ],
        ],
        'users' => [
            'label' => 'Users / Staff',
            'table' => 'users',
            'key_field' => 'username',
            'required' => ['username', 'full_name', 'role'],
            'password_field' => 'password',
            'defaults' => ['created_by' => '@user'],
            'columns' => [
                'username' => 'string',
                'password' => 'string',
                'email' => 'string',
                'full_name' => 'string',
                'role' => 'enum:' . implode(',', USER_ROLES),
                'department_id' => 'int',
                'is_active' => 'bool',
            ],
        ],
    ];
}

// Shape the catalog for the browser: ordered column descriptors, no internals.
function hmsImportDatasetList() {
    $out = [];
    foreach (hmsImportDatasetCatalog() as $key => $ds) {
        $cols = [];
        foreach ($ds['columns'] as $name => $type) {
            $cols[] = [
                'name' => $name,
                'type' => $type,
                'required' => in_array($name, $ds['required'], true),
            ];
        }
        $out[] = [
            'key' => $key,
            'label' => $ds['label'],
            'key_field' => $ds['key_field'],
            'required' => $ds['required'],
            'columns' => $cols,
        ];
    }
    return $out;
}
