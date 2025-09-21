# Web API with Authentication

Build a REST API with modular authentication using automatic route discovery, JWT tokens, and PSR-15 middleware - all with proper module encapsulation.

## Quick Start

```bash
composer require power-modules/framework power-modules/router
composer require laminas/laminas-diactoros laminas/laminas-httphandlerrunner
```

**Architecture**: `AuthModule` exports JWT services → `ApiModule` imports auth and defines routes → `RouterModule` wires everything together.

## Implementation

### 1. Authentication Module

```php
<?php

declare(strict_types=1);

namespace MyApi\Auth;

use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Laminas\Diactoros\Response\JsonResponse;

final readonly class JwtManager
{
    public function __construct(private string $secretKey) {}

    public function createToken(array $payload): string
    {
        // Simple JWT implementation (use firebase/jwt in production)
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $headerPayload = base64_encode($header) . '.' . base64_encode($payload);
        $signature = hash_hmac('sha256', $headerPayload, $this->secretKey, true);
        
        return $headerPayload . '.' . base64_encode($signature);
    }

    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;
        $headerPayload = $header . '.' . $payload;
        
        $expectedSignature = base64_encode(
            hash_hmac('sha256', $headerPayload, $this->secretKey, true)
        );
        
        if ($signature !== $expectedSignature) return null;
        
        return json_decode(base64_decode($payload), true);
    }
}

final class UserRepository
{
    private array $users = [
        'admin' => ['id' => 1, 'username' => 'admin', 'password_hash' => '$2y$10$example_hash'],
        'user' => ['id' => 2, 'username' => 'user', 'password_hash' => '$2y$10$another_hash'],
    ];

    public function findByUsername(string $username): ?array
    {
        return $this->users[$username] ?? null;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->users as $user) {
            if ($user['id'] === $id) return $user;
        }
        return null;
    }
}

final readonly class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private JwtManager $jwtManager
    ) {}

    public function authenticate(string $username, string $password): ?string
    {
        $user = $this->userRepository->findByUsername($username);
        if (!$user) return null;

        // In production, use password_verify()
        if ($password === 'password') {
            return $this->jwtManager->createToken([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'exp' => time() + 3600 // 1 hour
            ]);
        }

        return null;
    }

    public function getUserFromToken(string $token): ?array
    {
        $payload = $this->jwtManager->validateToken($token);
        if (!$payload || $payload['exp'] < time()) return null;

        return $this->userRepository->findById($payload['user_id']);
    }
}

// Authentication Middleware (PSR-15)
final readonly class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthService $authService) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Missing or invalid authorization header'], 401);
        }

        $token = substr($authHeader, 7);
        $user = $this->authService->getUserFromToken($token);
        
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid or expired token'], 401);
        }

        // Add user to request attributes for controllers
        $request = $request->withAttribute('user', $user);
        
        return $handler->handle($request);
    }
}

final readonly class AuthModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            AuthService::class,
            AuthMiddleware::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepository::class, UserRepository::class);
        
        $container->set(JwtManager::class, JwtManager::class)
            ->addArguments(['my-secret-key-change-in-production']);
        
        $container->set(AuthService::class, AuthService::class)
            ->addArguments([UserRepository::class, JwtManager::class]);
        
        $container->set(AuthMiddleware::class, AuthMiddleware::class)
            ->addArguments([AuthService::class]);
    }
}
```

### 2. API Module with Router Integration

```php
<?php

declare(strict_types=1);

namespace MyApi\Http;

use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\ImportItem;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Router\Contract\{HasRoutes, HasMiddleware, HasCustomRouteSlug};
use Modular\Router\Route;
use MyApi\Auth\{AuthModule, AuthService, AuthMiddleware};
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

// User Controller (PSR-15 RequestHandler)
final readonly class UserController implements RequestHandlerInterface
{
    public function __construct(private AuthService $authService) {}

    // Default handle method - for catch-all routing
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['error' => 'Method not found'], 404);
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $input = json_decode($body, true);
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            return new JsonResponse(['error' => 'Username and password required'], 400);
        }

        $token = $this->authService->authenticate($username, $password);
        if (!$token) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        return new JsonResponse(['token' => $token]);
    }

    public function profile(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user'); // Set by AuthMiddleware
        
        return new JsonResponse([
            'id' => $user['id'],
            'username' => $user['username']
        ]);
    }
}

// Product Controller
final readonly class ProductController implements RequestHandlerInterface
{
    // Default handle method - for catch-all routing
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['error' => 'Method not found'], 404);
    }

    public function list(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'products' => [
                ['id' => 1, 'name' => 'Widget A', 'price' => 29.99],
                ['id' => 2, 'name' => 'Widget B', 'price' => 39.99],
            ]
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getBody()->getContents();
        $input = json_decode($body, true);
        
        $name = $input['name'] ?? '';
        $price = $input['price'] ?? 0;

        if (!$name || $price <= 0) {
            return new JsonResponse(['error' => 'Name and valid price required'], 400);
        }

        // In real app, save to database
        return new JsonResponse([
            'id' => rand(1000, 9999),
            'name' => $name,
            'price' => $price
        ], 201);
    }
}

// API Module with Router Contracts
final readonly class ApiModule implements PowerModule, ImportsComponents, HasRoutes, HasCustomRouteSlug
{
    public static function imports(): array
    {
        return [
            ImportItem::create(AuthModule::class, AuthService::class, AuthMiddleware::class),
        ];
    }

    public function getRouteSlug(): string
    {
        return '/api/v1'; // Custom prefix instead of auto-generated '/api'
    }

    public function getRoutes(): array
    {
        return [
            // Public login route (no middleware)
            Route::post('/auth/login', UserController::class, 'login'),
            
            // Protected routes (with AuthMiddleware)
            Route::get('/user/profile', UserController::class, 'profile')
                ->addMiddleware(AuthMiddleware::class),
            Route::get('/products', ProductController::class, 'list')
                ->addMiddleware(AuthMiddleware::class),
            Route::post('/products', ProductController::class, 'create')
                ->addMiddleware(AuthMiddleware::class),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserController::class, UserController::class)
            ->addArguments([AuthService::class]);

        $container->set(ProductController::class, ProductController::class);
    }
}
```

### 3. Application Bootstrap

```php
<?php

declare(strict_types=1);

// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Modular\Framework\App\ModularAppBuilder;
use Modular\Router\Contract\ModularRouterInterface;
use Modular\Router\PowerModule\Setup\RoutingSetup;
use Modular\Router\RouterModule;
use MyApi\Auth\AuthModule;
use MyApi\Http\ApiModule;

// Build the modular application with router
$app = new ModularAppBuilder(__DIR__ . '/..')->build();

// Add router setup - this wires modules with HasRoutes automatically
$app->addPowerModuleSetup(new RoutingSetup());

$app->registerModules([
    RouterModule::class,  // Provides ModularRouterInterface
    AuthModule::class,    // Provides authentication services
    ApiModule::class,     // Implements HasRoutes with our API endpoints
]);

// Get the router (configured with all module routes)
$router = $app->get(ModularRouterInterface::class);

// Handle incoming HTTP request
$request = ServerRequestFactory::fromGlobals();
$response = $router->handle($request);

// Emit response
new SapiEmitter()->emit($response);
```

### 4. Optional: Router Configuration

```php
<?php

declare(strict_types=1);

// config/modular_router.php

use Laminas\Diactoros\ResponseFactory;
use League\Route\Strategy\JsonStrategy;
use Modular\Router\Config\Config;
use Modular\Router\Config\Setting;

return Config::create()
    ->set(Setting::Strategy, new JsonStrategy(new ResponseFactory()));
```

## Key Features

- **Module Encapsulation**: Controllers resolve from their own DI containers, not global
- **Automatic Route Discovery**: `HasRoutes` interface enables zero-config routing
- **PSR-15 Middleware**: Per-route authentication and request/response flow
- **Custom Route Prefixes**: `HasCustomRouteSlug` for API versioning (`/api/v1`)

```php
// Routes discovered automatically from modules
class ApiModule implements HasRoutes {
    public function getRoutes(): array {
        return [
            Route::get('/products', ProductController::class)
                ->addMiddleware(AuthMiddleware::class),
        ];
    }
}
```

## Usage Examples

### 1. Login to get a token:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "password"}'

# Response: {"token": "eyJ0eXAiOiJKV1QiLCJhbGci..."}
```

### 2. Access protected endpoints:
```bash
# Get user profile
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/user/profile

# List products  
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/products

# Create product
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "New Widget", "price": 49.99}'
```

## Next Steps

**Extend the API**: Add controllers, swap JWT for OAuth2, integrate databases through repositories.

**Production**: Replace demo JWT with `firebase/jwt`, add proper password hashing, implement logging middleware.

The modular design lets you evolve each concern independently while maintaining clean boundaries.