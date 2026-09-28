# FastAPI Service

Real-time AI prediction service for HMS.

## Running the Service

```bash
cd fastapi_service
uvicorn main:app --reload --port 8000
```

## API Documentation

Once running, visit:
- Swagger UI: http://localhost:8000/docs
- ReDoc: http://localhost:8000/redoc

## Endpoints

- `POST /api/v1/diagnosis` - Get diagnostic suggestions
- `POST /api/v1/prognosis` - Get prognosis predictions
- `POST /api/v1/lab-analysis` - Analyze lab results
- `POST /api/v1/radiology-analysis` - Analyze radiology images
