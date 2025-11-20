# API Rate Limiting Configuration

## Configuration
- **Location**: `bootstrap/app.php`
- **Limit**: 60 requests per minute per user
- **Scope**: Applied to all API routes

## Implementation
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(\Illuminate\Routing\Middleware\ThrottleRequests::class . ':60,1');
})
