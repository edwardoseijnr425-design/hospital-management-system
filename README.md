# Hospital Management System (HMS)

A comprehensive Hospital Management System inspired by LHIMS (Lightwave Health Information Management System), designed for multi-department healthcare facilities with role-based access control and workflow management.

## Features

### Core Features
- **Multi-Department Architecture**: Separate workflows for Records, Nursing, Doctors, Pharmacy, Laboratory, Radiology, Accounts, and Revenue departments
- **Role-Based Access Control**: Super Admin, Admin, IT, Records, Nurse, Doctor, Pharmacy, Lab, Radiology, Account, and Revenue roles
- **User Management**: Hierarchical user creation (Super Admin → Admin → Department accounts)
- **Patient Management**: Registration, search, and comprehensive patient records
- **Visit Management**: OPD and IPD visit tracking with workflow pipeline
- **Audit Trail**: Complete audit logging for all system actions

### Department-Specific Features
- **Records**: Patient registration and record management
- **Nursing Station**: Vital signs recording and patient monitoring
- **Doctors Station**: Consultations, prescriptions, lab/radiology requests
- **Pharmacy**: Inventory management and medication dispensing
- **Laboratory**: Test requests and result management
- **Radiology**: Imaging requests and result management
- **IPD Management**: Ward and bed management, patient admissions
- **Finance**: Billing, invoicing, sponsor management (NHIA)
- **Reporting**: MIS reports, registers, operation reports

### Technical Features
- **Responsive Design**: Works on desktop and mobile devices
- **Laragon Compatible**: Runs entirely on Laragon (Apache, MySQL, PHP)
- **Security**: Password hashing, session management, CSRF protection
- **Audit Logging**: Complete trail of all system actions

## System Requirements

- Laragon (or equivalent LAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher (tested on MySQL 8.4)
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### 1. Setup Laragon
1. Download and install Laragon from https://laragon.org/
2. Start **Apache** and **MySQL** from the Laragon control panel
3. Place the HMS folder in `C:\laragon\www\hms\` (on Windows), or link the
   existing checkout with a junction so one copy serves the app:
   ```powershell
   New-Item -ItemType Junction -Path 'C:\laragon\www\hms' -Target 'C:\path\to\hms'
   ```

### 2. Run Setup Script
1. Open your browser and navigate to: `http://localhost/hms/setup.php`
2. The setup script will:
   - Create the database `hms_db`
   - Import the database schema
   - Create the default super admin account
   - Create necessary directories

### 3. Access the System
1. Navigate to: `http://localhost/hms/frontend/index.php`
2. Login with default credentials:
   - Username: `admin`
   - Password: `Admin@123`
3. **IMPORTANT**: Change the default password immediately after first login

## Default Credentials

**Super Admin:**
- Username: `admin`
- Password: `Admin@123`

## Directory Structure

```
hms/
├── backend/
│   ├── api/              # API endpoints
│   ├── config/           # Configuration files
│   ├── includes/        # Common functions
│   └── models/          # Data models
├── database/
│   └── schema.sql       # Database schema
├── frontend/
│   ├── assets/          # CSS, JS, images
│   ├── pages/           # Page templates
│   ├── uploads/         # User uploads
│   ├── dashboard.php    # Main dashboard
│   └── index.php        # Login page
├── setup.php            # Setup script
└── README.md            # This file
```

## User Roles and Permissions

### Super Admin
- Create and manage admin accounts
- Full system access
- Manage all departments
- View audit trails

### Admin
- Create and manage department user accounts
- Manage departments and services
- View reports
- Manage sponsors and pricing

### Department Roles
- **Records**: Patient registration, record management
- **Nurse**: Vital signs, patient monitoring
- **Doctor**: Consultations, prescriptions, test requests
- **Pharmacy**: Inventory, dispensing
- **Lab**: Lab tests, results
- **Radiology**: Imaging, results
- **Account**: Billing, invoices
- **Revenue**: Payment collection
- **IT**: System maintenance

## Workflow Pipeline

### Patient Flow
1. **Registration** (Records Department)
   - Patient registration
   - Assign hospital number
   - Capture demographics and sponsor info

2. **Visit Creation** (Records/Reception)
   - Create OPD or IPD visit
   - Assign to department
   - Capture chief complaint

3. **Vital Signs** (Nursing Station)
   - Record temperature, BP, heart rate, etc.
   - Calculate BMI
   - Document observations

4. **Consultation** (Doctor)
   - Medical examination
   - Diagnosis
   - Treatment plan
   - Prescriptions
   - Lab/Radiology requests

5. **Laboratory/Radiology** (Lab/Radiology Departments)
   - Process test requests
   - Record results
   - Verify and finalize

6. **Pharmacy** (Pharmacy Department)
   - Process prescriptions
   - Dispense medications
   - Update inventory

7. **Billing** (Accounts/Revenue)
   - Generate invoices
   - Process payments
   - Sponsor claims

## Database Schema

The system uses a comprehensive MySQL database with the following main tables:
- `users` - User accounts and authentication
- `user_profiles` - Extended user information
- `departments` - Hospital departments
- `patient_registrations` - Patient records
- `patient_visits` - Patient visits (OPD/IPD)
- `consultations` - Doctor consultations
- `vital_signs` - Patient vital signs
- `prescriptions` - Medication prescriptions
- `lab_requests` / `lab_results` - Laboratory tests
- `radiology_requests` / `radiology_results` - Imaging tests
- `pharmacy_inventory` - Drug inventory
- `wards` / `beds` - IPD ward and bed management
- `invoices` / `billing_items` - Financial transactions
- `sponsors` - Insurance/sponsor management
- `audit_trails` - System audit logs

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Role-based access control
- CSRF protection
- SQL injection prevention (prepared statements)
- Input sanitization
- Audit logging for all critical actions

## Future Enhancements

### AI Engine Integration
- Django/FastAPI backend for AI-powered features
- Diagnostic assistance
- Prognosis predictions
- Image analysis for radiology
- Natural language processing for clinical notes

### Additional Features
- Mobile app (React Native)
- Offline-first architecture
- Integration with medical devices
- Telemedicine module
- Appointment scheduling
- Blood bank management
- Ambulance services
- Cafeteria management

## Troubleshooting

### Database Connection Issues
- Ensure Laragon MySQL is running (Laragon control panel → Start All)
- Check database credentials in `backend/config/database.php`
- Verify database `hms_db` exists

### Permission Issues
- Ensure `frontend/uploads` directory is writable
- Check file permissions on Linux/Mac

### Session Issues
- Clear browser cookies and cache
- Check session configuration in `backend/config/config.php`

## Support

For issues and questions:
- Check the audit logs for error details
- Review browser console for JavaScript errors
- Check the Laragon error logs (`C:\laragon\log\`, or Laragon → PHP/MySQL → error log)

## License

This project is for educational and demonstration purposes.

## Credits

Inspired by LHIMS (Lightwave Health Information Management System) used in Ghana's healthcare facilities.

## Version

Current Version: 1.0.0
