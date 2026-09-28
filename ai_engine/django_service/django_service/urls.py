"""
URL configuration for AI Engine Django service
"""

from django.contrib import admin
from django.urls import path, include

urlpatterns = [
    path('admin/', admin.site.urls),
    path('api/', include('model_training.urls')),
]
