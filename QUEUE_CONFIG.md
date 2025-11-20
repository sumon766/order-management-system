
# Queue Configuration

## Driver
- **Production**: Database
- **Development**: Sync (for testing)

## Configuration
- **Connection**: `database` (configured in `.env`)
- **Table**: `jobs` and `failed_jobs`
- **Retries**: 3 attempts with exponential backoff

## Workers
```bash
# Start queue worker
php artisan queue:work

# Process failed jobs
php artisan queue:retry all

# Monitor queue
php artisan queue:monitor
