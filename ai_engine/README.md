# AI Engine for Hospital Management System

This directory contains the AI/ML infrastructure for the HMS, designed to assist doctors and laboratories with diagnosis and prognosis.

## Architecture

The AI engine uses a microservices architecture:
- **FastAPI**: High-performance API server for real-time predictions
- **Django**: Backend for model training, data processing, and batch operations
- **Machine Learning Models**: Diagnostic and prognostic models

## Setup Instructions

### Prerequisites
- Python 3.8+
- pip
- virtualenv

### Installation

1. Create virtual environment:
```bash
cd ai_engine
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Set up environment variables:
```bash
cp .env.example .env
# Edit .env with your configuration
```

## Services

### FastAPI Service (Real-time Predictions)

The FastAPI service provides real-time AI predictions for:
- Diagnostic assistance
- Prognosis predictions
- Lab result analysis
- Radiology image analysis

**Start the service:**
```bash
cd fastapi_service
uvicorn main:app --reload --port 8000
```

**API Endpoints:**
- `POST /api/v1/diagnosis` - Get diagnostic suggestions
- `POST /api/v1/prognosis` - Get prognosis predictions
- `POST /api/v1/lab-analysis` - Analyze lab results
- `POST /api/v1/radiology-analysis` - Analyze radiology images

### Django Service (Training & Batch Processing)

The Django service handles:
- Model training and retraining
- Data preprocessing
- Batch predictions
- Model versioning

**Start the service:**
```bash
cd django_service
python manage.py runserver --port 8001
```

## Integration with HMS

The AI engine integrates with the main PHP HMS system through REST APIs:

1. **HMS sends patient data** → AI Engine
2. **AI Engine processes data** → Returns predictions
3. **HMS displays predictions** → Doctor reviews and makes final decision

### Example Integration

```php
// From PHP backend
$aiData = [
    'symptoms' => ['fever', 'cough', 'fatigue'],
    'vital_signs' => [
        'temperature' => 38.5,
        'blood_pressure' => '120/80',
        'heart_rate' => 95
    ],
    'lab_results' => [
        'wbc_count' => 12000,
        'crp' => 15
    ]
];

$response = file_get_contents('http://localhost:8000/api/v1/diagnosis', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($aiData)
    ]
]));

$predictions = json_decode($response, true);
```

## Models

### Diagnostic Models
- Symptom-based diagnosis
- Lab result interpretation
- Vital signs analysis

### Prognostic Models
- Disease progression prediction
- Recovery time estimation
- Risk assessment

### Imaging Models
- X-ray analysis
- CT scan interpretation
- MRI analysis

## Data Flow

```
HMS (PHP) → API Gateway → FastAPI Service → ML Models → Predictions → HMS
                      ↓
                 Django Service (Training/Batch)
```

## Security

- API authentication using JWT tokens
- Rate limiting
- Input validation
- HIPAA compliance considerations

## Future Enhancements

- Natural language processing for clinical notes
- Drug interaction checking
- Automated report generation
- Real-time patient monitoring alerts
- Integration with medical devices

## Monitoring

- Model performance tracking
- Prediction accuracy metrics
- API response time monitoring
- Error logging and alerting

## Contributing

When adding new models:
1. Update the model registry
2. Add API endpoints
3. Update documentation
4. Add unit tests
5. Train and validate models

## License

Part of the Hospital Management System project.
