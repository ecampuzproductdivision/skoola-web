# AI Rules — Cline Configuration untuk Ecampuz Laravel Developer
# Berlaku untuk semua developer Laravel di lingkungan Ecampuz

## Personalisasi
- Kamu adalah agen AI koding profesional yang memiliki posisi sebagai Expert Laravel Developer

## Bahasa & Komunikasi
- Gunakan **Bahasa Indonesia** untuk penjelasan dan respon, kecuali user meminta Bahasa Inggris.
- Gunakan gaya bahasa yang profesional, sopan, dan mudah dipahami.
- Jika ada istilah teknis, gunakan istilah aslinya (Inggris) lalu beri penjelasan singkat.

---

## Repository Pattern (WAJIB)
**Repository Pattern adalah mandatory / wajib untuk semua project Laravel di Ecampuz. Tidak ada pengecualian.**

### Aturan Repository Pattern
1. **WAJIB** membuat **BaseRepository** (abstract class) untuk semua akses data — baik ke database (Eloquent) maupun REST API eksternal.
2. **WAJIB** membuat Interface untuk setiap Repository.
3. **WAJIB** binding Interface ke implementation via `AppServiceProvider` atau `RepositoryServiceProvider`.
4. **DILARANG** memanggil Eloquent Model langsung dari Controller. Semua akses data dan business logic harus melalui Repository.
5. **Business logic digabung di Repository class** — tidak ada Service Layer terpisah. Repository menangani akses data DAN business logic.
6. Flow wajib: **Controller → Repository → Data Source**

### Struktur Repository yang Wajib
```
app/
└── Repositories/
    ├── BaseRepository.php                  (abstract class untuk Eloquent)
    ├── BaseApiRepository.php               (abstract class untuk REST API eksternal)
    ├── Contracts/
    │   ├── UserRepositoryInterface.php
    │   ├── KeuanganRepositoryInterface.php
    │   └── ...interface lainnya
    ├── Eloquent/
    │   ├── UserRepository.php             (extends BaseRepository, implements UserRepositoryInterface)
    │   └── ...repository eloquent lainnya
    └── Api/
        ├── KeuanganRepository.php         (extends BaseApiRepository, implements KeuanganRepositoryInterface)
        └── ...repository api lainnya
```

### BaseRepository (untuk Eloquent)
BaseRepository minimal harus menyediakan method:
- `findById($id)`
- `findAll(array $criteria = [])`
- `create(array $data)`
- `update($id, array $data)`
- `delete($id)`
- `paginate(array $criteria = [], $perPage = 15)`

### BaseApiRepository (untuk REST API Eksternal)
BaseApiRepository minimal harus menangani:
- HTTP Client (Guzzle) — setting base URL, timeout, headers
- Authentication token (bearer token, API key, dll)
- Error handling & retry mechanism
- Logging semua request/response HTTP
- Parsing response ke array atau Collection

### Contoh Implementasi (Business Logic gabung di Repository)
```php
// 1. Interface
interface UserRepositoryInterface 
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function createUser(array $data): User;
    public function updateUser(int $id, array $data): User;
    public function deleteUser(int $id): bool;
    public function getActiveUsers(): Collection;
    public function assignRoleToUser(int $userId, string $role): void;
}

// 2. Repository Implementation (gabung data access + business logic)
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): string 
    {
        return User::class;
    }
    
    // Data access
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
    
    // Business logic — langsung di Repository
    public function createUser(array $data): User
    {
        // Business logic: validasi email unik, hash password, dll
        if ($this->findByEmail($data['email'])) {
            throw new \DomainException('Email already exists');
        }
        
        $data['password'] = bcrypt($data['password']);
        return $this->create($data);
    }
    
    public function getActiveUsers(): Collection
    {
        // Business logic: filter user aktif dengan relasi tertentu
        return $this->model
            ->where('status', 'active')
            ->with('roles')
            ->orderBy('name')
            ->get();
    }
    
    public function assignRoleToUser(int $userId, string $role): void
    {
        $user = $this->findById($userId);
        // Business logic validasi role
        if (!in_array($role, ['admin', 'operator', 'viewer'])) {
            throw new \DomainException('Invalid role');
        }
        $user->syncRoles([$role]);
    }
}

// 3. Controller langsung ke Repository (tanpa Service Layer)
class UserController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}
    
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
        return response()->json(['data' => $user]);
    }
    
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userRepository->createUser($request->validated());
        return response()->json(['data' => $user], 201);
    }
}
```


## Code Style & Best Practices (Laravel)
- Ikuti **PSR-1**, **PSR-4**, dan **PSR-12** secara ketat.
- Gunakan **Laravel Coding Style** (sesuai dokumentasi resmi Laravel).
- Terapkan **SOLID Principles** dalam pengembangan.
- Hindari `dd()`, `var_dump()`, `dump()` pada production code. Gunakan `Log::info()` atau debugging tools.
- Gunakan fitur **Laravel Pint** untuk auto-formatting.

## Database & Eloquent
- Akses Eloquent **hanya melalui Repository**. Dilarang akses langsung dari Controller.
- Gunakan **migrations** untuk semua perubahan struktur database.
- Gunakan **model factories** dan **seeders** untuk testing data.
- Terapkan **Eager Loading** untuk menghindari N+1 query problem.
- Beri indeks yang tepat pada kolom yang sering di-query.

### Naming Convention Database (WAJIB)
- **Nama tabel**: `snake_case` (contoh: `master_users`, `akademik_mahasiswa`, `keuangan_transaksi`).
- **Nama kolom**: `camelCase` (contoh: `userId`, `fullName`, `createdAt`, `isActive`).
- **Primary key**: `bigInteger` auto-increment — gunakan `$table->id()` (Laravel 8+ otomatis bigIncrements) atau eksplisit `$table->bigIncrements('id')`.
- **Foreign key**: `{camelCaseReferensi}Id` (contoh: `userId`, `roleId`, `jurusanId`) dengan tipe `unsignedBigInteger`.
- **Timestamps**: `createdAt`, `updatedAt`, `deletedAt` (untuk soft delete).
- **Contoh migrasi**:
  ```php
  Schema::create('master_users', function (Blueprint $table) {
      $table->bigIncrements('id');                // primary key bigInteger
      $table->string('fullName');
      $table->string('email')->unique();
      $table->string('password');
      $table->string('phoneNumber')->nullable();
      $table->boolean('isActive')->default(true);
      $table->unsignedBigInteger('roleId');        // foreign key bigInteger
      $table->foreign('roleId')->references('id')->on('master_roles');
      $table->timestamps();                        // createdAt, updatedAt
      $table->softDeletes('deletedAt');
  });
  ```
- **Model Laravel**: tambahkan properti `$table` jika nama tabel tidak mengikuti konvensi Laravel:
  ```php
  class User extends Model
  {
      protected $table = 'master_users';
      protected $primaryKey = 'id';
      protected $keyType = 'int';
      public $incrementing = true;
      public const CREATED_AT = 'createdAt';
      public const UPDATED_AT = 'updatedAt';
      protected $casts = [
          'isActive' => 'boolean',
      ];
  }
  ```

## Pagination (WAJIB)
- **Semua endpoint yang mengembalikan list data WAJIB menggunakan pagination**. Dilarang mengembalikan semua data sekaligus tanpa pagination.
- Gunakan **LengthAwarePaginator** (`Model::paginate()`) untuk pagination standar (query parameters `?page=` dan `?per_page=`).
- Untuk dataset besar atau infinite scroll, gunakan **Cursor Pagination** (`Model::cursorPaginate()`) yang lebih performant.
- Di Repository, method `findAll()` dan method list lainnya harus mengembalikan **Paginator instance**, bukan Collection.
- Contoh implementasi:
  ```php
  // ✅ BENAR — paginated
  public function findAll(array $criteria = []): LengthAwarePaginator
  {
      return $this->model
          ->where($criteria)
          ->orderBy('created_at', 'desc')
          ->paginate(15);
  }
  
  // ❌ SALAH — return semua data tanpa pagination
  public function findAll(array $criteria = []): Collection
  {
      return $this->model->where($criteria)->get();
  }
  ```
- Default `per_page` = 15. Bisa di-override via parameter request `?per_page=`.
- Batasi maksimal `per_page` = 100 untuk mencegah abuse.
- Response API untuk list data harus menyertakan meta pagination:
  ```json
  {
    "data": [...],
    "meta": {
      "current_page": 1,
      "last_page": 10,
      "per_page": 15,
      "total": 150
    }
  }
  ```
- Gunakan **Laravel API Resource Collection** yang sudah otomatis menyertakan pagination meta.

## Configuration & Environment
- **WAJIB** akses semua konfigurasi melalui helper `config()`, **DILARANG** memanggil `env()` langsung di aplikasi (controller, repository, view, dll).
- `env()` **hanya boleh dipanggil** di file `config/*.php` saja.
- Contoh:
  ```php
  // ✅ BENAR
  $apiUrl = config('services.keuangan.api_url');
  
  // ❌ SALAH — env() dipanggil di luar file config
  $apiUrl = env('KEUANGAN_API_URL');
  ```
- Buat file konfigurasi sendiri di `config/` untuk modul-modul spesifik, misal `config/keuangan.php`, `config/akademik.php`.
- Publikasikan konfigurasi via `php artisan config:publish` jika membuat package.
- Setelah menambahkan config baru, jalankan `php artisan config:clear` agar perubahan tercache.

## Security & Authentication (WAJIB)
- **WAJIB** install **Laravel Passport** (`composer require laravel/passport`) di setiap project baru — tidak ada pengecualian.
- **WAJIB** menggunakan **OAuth 2.0 via Laravel Passport** sebagai standar keamanan REST API. **DILARANG** menggunakan autentikasi custom/token manual.
- Jalankan `php artisan passport:install` untuk generate client keys.
- Implementasi:
  ```php
  // routes/api.php — WAJIB pake auth:api
  Route::middleware('auth:api')->group(function () {
      // semua endpoint yang butuh autentikasi
  });
  ```
- Gunakan **Password Grant Tokens** untuk first-party clients (SPA, mobile app internal).
- Gunakan **Personal Access Tokens** untuk developer/testing.
- Gunakan **Client Credentials Grant** untuk machine-to-machine (integrasi service ke service).
- **WAJIB** set token expiry:
  ```php
  // config/auth.php
  'expiration' => env('PASSPORT_TOKEN_EXPIRATION', 60), // menit
  ```
- **WAJIB** implement **refresh token** untuk long-lived sessions.
- **Scopes** untuk fine-grained access control:
  ```php
  // app/Providers/AuthServiceProvider.php
  Passport::tokensCan([
      'read-data'     => 'Read data',
      'write-data'    => 'Write data',
      'manage-users'  => 'Manage users',
  ]);
  ```
- **WAJIB** install **Spatie Laravel Permission** (`composer require spatie/laravel-permission`) untuk role & permission management.
- Implementasi Spatie:
  ```php
  // php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  // php artisan migrate
  
  // Membuat role & permission — sebaiknya di Seeder atau Service Provider
  use Spatie\Permission\Models\Role;
  use Spatie\Permission\Models\Permission;
  
  // Buat permissions
  Permission::firstOrCreate(['name' => 'kejarkarir.users.read']);
  Permission::firstOrCreate(['name' => 'kejarkarir.users.write']);
  Permission::firstOrCreate(['name' => 'kejarkarir.users.delete']);
  
  // Buat roles & assign permissions
  $admin = Role::firstOrCreate(['name' => 'admin']);
  $admin->givePermissionTo(['kejarkarir.users.read', 'kejarkarir.users.write', 'kejarkarir.users.delete']);
  
  $operator = Role::firstOrCreate(['name' => 'operator']);
  $operator->givePermissionTo(['kejarkarir.users.read']);
  
  // Assign role ke user
  $user->assignRole('admin');
  
  // Cek di Controller/Repository
  if ($user->hasPermissionTo('kejarkarir.users.write')) {
      // allow
  }
  ```
- Naming convention permission: `{appname}.{resource}.{action}` (contoh: `kejarkarir.users.read`, `kejarkarir.users.create`, `kejarkarir.laporan.export`).
- Gunakan **Middleware Spatie** untuk proteksi route:
  ```php
  Route::middleware('auth:api')->group(function () {
      Route::get('/users', [UserController::class, 'index'])
          ->middleware('permission:kejarkarir.users.read');
          
      Route::post('/users', [UserController::class, 'store'])
          ->middleware('permission:kejarkarir.users.write');
          
      Route::delete('/users/{id}', [UserController::class, 'destroy'])
          ->middleware('permission:kejarkarir.users.delete');
  });
  ```
- Gunakan **Blade Directives** untuk view:
  ```blade
  @can('kejarkarir.users.write')
      <button>Create User</button>
  @endcan
  
  @role('admin')
      <div>Admin panel</div>
  @endrole
  ```
- **DILARANG** hardcode pengecekan role manual (`if ($user->role === 'admin')`) — harus melalui Spatie permission system.
- **WAJIB** buat Seeder untuk default roles & permissions (Super Admin, Admin, Operator, Viewer).
- **WAJIB** tulis unit test untuk setiap permission check.
- Gunakan **Gates/ Policies** dari Laravel bersama Spatie untuk otorisasi level resource yang lebih granular.
- Selalu gunakan **Form Request** untuk validasi input.
- Terapkan **Mass Assignment Protection** (`$fillable` atau `$guarded`).
- Gunakan **XSS Protection** (Brace `{{ }}` syntax, jangan `{!! !!}` kecuali terpaksa).
- **CSRF Protection** — pastikan semua POST/PUT/DELETE request memiliki CSRF token.
- Jangan pernah menyimpan credentials di kode. Gunakan `.env` dan `config/`.
- Gunakan **Rate Limiting** untuk API endpoints.
- Hash password menggunakan **bcrypt** atau **Argon2**.

## API Development
- Ikuti **Laravel API Resource** conventions untuk response formatting.
- Gunakan **API Versioning** (`/api/v1/`, `/api/v2/`).
- **WAJIB** dokumentasikan API menggunakan format **Hoppscotch** (JSON collection), bisa diexport dari Hoppscotch atau ditulis manual.
- Simpan file koleksi API di folder `docs/api/hoppscotch/` dengan nama per modul, contoh:
  ```
  docs/api/hoppscotch/
  ├── auth.json
  ├── users.json
  ├── keuangan.json
  └── akademik.json
  ```
- Setiap file koleksi Hoppscotch WAJIB mencakup:
  - **Environment variables** (base URL, auth token, dll)
  - **Semua endpoint** yang tersedia (lengkap dengan method, headers, body, parameter)
  - **Contoh response** untuk success case dan error case
  - **Pre-request script** jika diperlukan (misal: auto-refresh token)
- Format dasar Hoppscotch collection:
  ```json
  {
    "v": 1,
    "name": "Ecampuz API - Auth",
    "variables": [
      {
        "name": "base_url",
        "value": "https://api.ecampuz.com/api/v1"
      },
      {
        "name": "token",
        "value": ""
      }
    ],
    "requests": [
      {
        "name": "Login",
        "method": "POST",
        "url": "{{base_url}}/auth/login",
        "headers": [
          { "key": "Content-Type", "value": "application/json" }
        ],
        "body": {
          "email": "admin@ecampuz.com",
          "password": "password"
        },
        "preRequestScript": "",
        "testScript": "",
        "auth": {
          "authType": "none"
        }
      },
      {
        "name": "Get Users",
        "method": "GET",
        "url": "{{base_url}}/users?page=1&per_page=15",
        "headers": [
          { "key": "Authorization", "value": "Bearer {{token}}" }
        ],
        "auth": {
          "authType": "inherit"
        }
      }
    ]
  }
  ```
- **WAJIB** update koleksi Hoppscotch setiap kali ada perubahan endpoint atau response.
- Gunakan **Hoppscotch CLI** atau **Hoppscotch Desktop** untuk testing dan export collection.
- Alternatif tool: **Postman** juga boleh, tapi WAJIB diexport ke format Hoppscotch-compatible.
- Dokumentasi API juga bisa dilengkapi dengan **Scribe** atau **Swagger/OpenAPI** sebagai pelengkap.
- Gunakan **Form Request** untuk validasi API.
- Implementasikan **API Authentication** wajib via **Laravel Passport (OAuth 2.0)**.
- Gunakan **Resource Collection** untuk daftar data.
- Beri response yang konsisten (format: `data`, `message`, `errors`, `meta`).

## Health Check Endpoint (WAJIB)
- **WAJIB** menyediakan endpoint `/api/health` (tanpa autentikasi) untuk kebutuhan monitoring dan checking up service.
- Tujuan: Load balancer, Docker health check, monitoring tools (uptime robot, pingdom, dll), dan CI/CD pipeline.

### Spesifikasi Endpoint
```
GET /api/health
```

### Response Sukses (HTTP 200)
```json
{
    "status": "ok",
    "timestamp": "2026-06-21T17:00:00.000000Z",
    "services": {
        "app": {
            "status": "ok",
            "name": "ecampuz-laravel",
            "version": "1.0.0",
            "environment": "production"
        },
        "database": {
            "status": "ok",
            "driver": "mysql",
            "latency_ms": 2.5
        },
        "cache": {
            "status": "ok",
            "driver": "redis",
            "latency_ms": 1.2
        }
    }
}
```

### Response Gagal (HTTP 503)
Jika salah satu service turun:
```json
{
    "status": "degraded",
    "timestamp": "2026-06-21T17:00:00.000000Z",
    "services": {
        "app": {
            "status": "ok",
            "name": "ecampuz-laravel",
            "version": "1.0.0",
            "environment": "production"
        },
        "database": {
            "status": "error",
            "driver": "mysql",
            "message": "Connection refused"
        },
        "cache": {
            "status": "ok",
            "driver": "redis",
            "latency_ms": 1.2
        }
    }
}
```

### Aturan Implementasi
1. **WAJIB** mengecek koneksi ke:
   - **Database** (Eloquent: jalankan query `SELECT 1` atau `DB::connection()->getPdo()`)
   - **Cache driver** (Redis/Memcached: ping test)
   - Opsional: Storage disk, queue connection, external API yang critical
2. **WAJIB** mengukur **latency** masing-masing service dalam milidetik.
3. **Tidak perlu middleware auth** — endpoint ini harus bisa diakses tanpa token untuk monitoring.
4. **WAJIB** dilindungi **Rate Limiting** agar tidak di-hit terlalu sering.
5. Simpan di Controller khusus: `app/Http/Controllers/HealthController.php`.
6. Route didefinisikan di `routes/api.php`:
   ```php
   Route::get('/health', [HealthController::class, 'index'])
       ->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class]);
   ```
   Atau beri throttle khusus:
   ```php
   Route::get('/health', [HealthController::class, 'index'])
       ->middleware('throttle:10,1'); // max 10 request per menit
   ```
7. **WAJIB** menulis unit test untuk health endpoint:
   ```php
   public function test_health_endpoint_returns_ok_when_all_services_up()
   public function test_health_endpoint_returns_503_when_database_down()
   public function test_health_endpoint_does_not_require_authentication()
   ```

### Struktur File yang Disarankan
```
app/
├── Http/
│   └── Controllers/
│       └── HealthController.php
└── Services/
    └── HealthService.php           (opsional, jika logic cek health complex)
```

## Testing (WAJIB — Setiap Flow WAJIB Punya Unit Test)
- **WAJIB** membuat **Unit Test untuk SETIAP flow yang dibuat** — tidak ada pengecualian. Setiap method/service/repository yang selesai dikerjakan harus memiliki test-nya sendiri.
- **WAJIB** menulis **Unit Tests** dan **Feature Tests** menggunakan PHPUnit.

### Aturan Wajib Testing
1. **Setiap method di Repository WAJIB** memiliki minimal 1 unit test:
   - `test_it_can_find_by_id()`
   - `test_it_can_create_data()`
   - `test_it_can_update_data()`
   - `test_it_can_delete_data()`
   - `test_it_throws_exception_when_data_not_found()`
   - `test_it_throws_exception_when_validation_fails()`
   
2. **Setiap endpoint di Controller WAJIB** memiliki feature test:
   - Test sukses (200/201 response)
   - Test validasi gagal (422 response)
   - Test unauthorized (401/403 response)
   - Test data tidak ditemukan (404 response)

3. **Contoh daftar test untuk 1 flow (misal CRUD User):**
   ```php
   // Repository Unit Tests
   public function test_it_can_get_paginated_users()
   public function test_it_can_find_user_by_id()
   public function test_it_can_create_user_with_valid_data()
   public function test_it_throws_exception_when_creating_duplicate_email()
   public function test_it_can_update_user()
   public function test_it_can_delete_user()
   public function test_it_returns_null_when_user_not_found()
   public function test_it_can_get_active_users_only()
   
   // Controller Feature Tests
   public function test_guest_cannot_access_user_list()
   public function test_admin_can_view_paginated_users()
   public function test_admin_can_create_user()
   public function test_admin_gets_validation_error_on_invalid_input()
   public function test_admin_can_update_user()
   public function test_admin_can_delete_user()
   public function test_returns_404_when_user_not_found()
   ```

4. **Tidak boleh ada kode production yang dikerjakan tanpa test-nya.** Urutan pengerjaan:
   ```
   1. Tulis test (Red) → test pasti gagal karena belum ada implementasi
   2. Tulis implementasi (Green) → test jadi passing
   3. Refactor jika perlu
   ```

### Tools & Mocking
- Repository interface memudahkan mocking — manfaatkan untuk unit test.
- Gunakan **Mockery** untuk mock HTTP client di repository tests.
- Gunakan **HTTP Fake** (`Http::fake()`) untuk testing integrasi API eksternal.
- Test Repository secara langsung — tidak perlu Service Layer.
- Gunakan **RefreshDatabase** trait untuk feature test agar database bersih setiap test.
- Gunakan **DatabaseTransactions** trait jika tidak ingin data test benar-benar tersimpan.

### Coverage Target
- Coverage minimal **70%** untuk kode baru.
- Coverage **100%** untuk method-method critical (autentikasi, transaksi finansial, integrasi API eksternal).
- Jalankan test suite sebelum commit: `php artisan test` — **pastikan semua test PASSING**.
- Jika ada test yang fail, **jangan commit** sebelum diperbaiki.

## Dokumentasi & README (WAJIB)
- **WAJIB** membuat file `README.md` di root project yang berisi dokumentasi lengkap.

### Isi Wajib README.md
1. **Judul & Deskripsi Proyek**
   - Nama aplikasi, deskripsi singkat, dan tujuan proyek.

2. **Persyaratan Sistem (Prerequisites)**
   ```markdown
   ## Prerequisites
   - PHP >= 8.1
   - Composer
   - MySQL / MariaDB / PostgreSQL
   - Redis (untuk cache & queue)
   - Node.js & NPM (untuk asset compilation)
   - Laravel Passport (OAuth 2.0)
   - Spatie Laravel Permission
   ```

3. **Panduan Instalasi (Step-by-Step)**
   ```markdown
   ## Installation
   
   1. Clone repository
      ```bash
      git clone <repo-url>
      cd <project-folder>
      ```
   
   2. Install dependencies
      ```bash
      composer install
      npm install
      ```
   
   3. Copy environment
      ```bash
      cp .env.example .env
      php artisan key:generate
      ```
   
   4. Konfigurasi database di `.env`
      ```env
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=ecampuz_db
      DB_USERNAME=root
      DB_PASSWORD=
      ```
   
   5. Migrasi & seeder
      ```bash
      php artisan migrate --seed
      ```
   
   6. Install Passport
      ```bash
      php artisan passport:install
      ```
   
   7. Link storage
      ```bash
      php artisan storage:link
      ```
   
   8. Compile assets
      ```bash
      npm run build
      ```
   
   9. Jalankan server
      ```bash
      php artisan serve
      ```
   ```

4. **Konfigurasi Environment**
   - Daftar semua variable `.env` beserta penjelasan dan contoh nilai.
   - Contoh:
     ```markdown
     | Variable | Deskripsi | Contoh |
     |----------|-----------|--------|
     | `APP_NAME` | Nama aplikasi | Ecampuz App |
     | `DB_*` | Konfigurasi database | - |
     | `PASSPORT_TOKEN_EXPIRATION` | Expiry token OAuth (menit) | 60 |
     | `CACHE_DRIVER` | Driver cache | redis |
     ```

5. **Struktur Folder**
   - Penjelasan singkat struktur folder utama project.

6. **API Documentation**
   - Link ke folder koleksi Hoppscotch: `docs/api/hoppscotch/`
   - Atau link ke dokumentasi online jika ada.

7. **Testing**
   ```markdown
   ## Testing
   ```bash
   # Jalankan semua test
   php artisan test
   
   # Jalankan dengan coverage
   php artisan test --coverage
   ```
   ```

8. **Deployment**
   - Langkah-langkah deployment ke production/staging.
   - Commands yang perlu dijalankan (migrate, cache, queue, dll).

9. **Contributing**
   - Panduan kontribusi: branching strategy, code style, PR process.
   - Link ke `.clinerules` untuk standar coding.

10. **Kontak / Maintainer**
    - Nama tim atau orang yang bertanggung jawab.

### Aturan Tambahan
- **WAJIB** update `README.md` setiap ada perubahan signifikan (dependency baru, environment variable baru, perubahan struktur).
- **WAJIB** buat file `docs/` untuk dokumentasi tambahan:
  ```markdown
  docs/
  ├── api/
  │   └── hoppscotch/         (koleksi API Hoppscotch)
  ├── architecture.md         (arsitektur aplikasi)
  ├── database/
  │   └── erd.md              (Entity Relationship Diagram)
  └── development/
      ├── setup.md            (panduan setup development)
      └── conventions.md      (coding conventions)
  ```
- Gunakan **Bahasa Indonesia** untuk README, kecuali project bersifat internasional.
- Sertakan badge (build status, PHP version, license) di bagian atas README.

## Git & Collaboration
- **WAJIB** menggunakan **conventional commits** format: `type(scope): message`
  - Contoh: `feat(auth): add two-factor authentication`
  - Tipe yang digunakan:
    | Type | Keterangan |
    |------|-----------|
    | `feat` | Fitur baru |
    | `fix` | Perbaikan bug |
    | `chore` | Tugas rutin, maintenance |
    | `refactor` | Refaktor kode (tanpa perubahan fitur/bug) |
    | `docs` | Perubahan dokumentasi |
    | `test` | Penambahan atau perbaikan test |
    | `style` | Perubahan style/format (linting, prettier) |
    | `perf` | Perbaikan performa |
    | `db` | Perubahan migration, seeder, atau struktur database |
  - Scope diisi dengan nama modul atau konteks (contoh: `auth`, `users`, `keuangan`, `db`).
- Buat **feature branch** untuk setiap task: `feature/ECO-xxx-description`
- Pull request wajib di-review minimal 1 orang sebelum merge.
- Jangan commit file `.env`, `node_modules/`, `vendor/`, atau file cache.

## Template UI — Dasher Bootstrap 5 Admin Template (WAJIB)
- **WAJIB** menggunakan **Dasher Bootstrap 5 Admin Template** untuk semua project Ecampuz yang membutuhkan admin panel.
- Dokumentasi: [https://dasher-ui.netlify.app/docs/](https://dasher-ui.netlify.app/docs/)
- Source template: [https://git.solusikampus.id/uiux/template-berbayar/dasher-minimal-and-clean-bootstrap-5-admin-template](https://git.solusikampus.id/uiux/template-berbayar/dasher-minimal-and-clean-bootstrap-5-admin-template)

### Aturan Penggunaan Template
<!-- - **WAJIB** menggunakan **Dasher `<router-view>` layout** untuk semua halaman admin (bukan Blade layout manual).

### Konfigurasi Warna Template (WAJIB)
Gunakan konfigurasi warna berikut untuk semua project. Atur di file SCSS/CSS atau variable theme Dasher:
```scss
// Dasher Theme Variables
$primary:     #EA6267;   // Primary color (merah Ecampuz)
$success:     #22C55E;   // Success color (hijau)
$warning:     #FFAB00;   // Warning color (kuning)
$secondary:   #919EAB;   // Secondary color (abu-abu)

// Shadow
$box-shadow:  0 2px 4px rgba(0, 0, 0, 0.08);  // Small shadow
```

- **Primary color** `#EA6267` — untuk button utama, link, active state, header highlights.
- **Success color** `#22C55E` — untuk status sukses, confirmation button, badge aktif.
- **Warning color** `#FFAB00` — untuk alert warning, status pending, peringatan.
- **Secondary color** `#919EAB` — untuk text secondary, border, disabled state, icon non-aktif.
- **Shadow** — gunakan small shadow (`0 2px 4px rgba(0,0,0,0.08)`) untuk card, dropdown, modal.
- **Mode** — **WAJIB** mendukung **Light mode** dan **Dark mode**:
  - Light mode: default, background putih/terang.
  - Dark mode: background gelap (#1a1a2e atau #121212), text putih/terang.
  - Gunakan CSS custom properties (CSS variables) agar mudah toggle:
    ```css
    :root {
      --bg-primary: #ffffff;
      --bg-secondary: #f8f9fa;
      --text-primary: #212529;
      --text-secondary: #6c757d;
    }
    
    [data-theme="dark"] {
      --bg-primary: #1a1a2e;
      --bg-secondary: #16213e;
      --text-primary: #e4e6eb;
      --text-secondary: #b0b3b8;
    }
    ```
  - Simpan preferensi theme user di localStorage.
  - Sediakan toggle button di navbar untuk switch light/dark mode. -->
- Gunakan komponen-komponen yang sudah tersedia di Dasher (sidebar, navbar, cards, tables, forms, modals, dll) — jangan membuat ulang dari nol.
- Ikuti struktur folder Blade sesuai template Dasher:
  ```
  resources/views/
  ├── layouts/
  │   └── dasher.blade.php         (main layout)
  ├── pages/
  │   ├── auth/                    (login, register, forgot password)
  │   ├── dashboard/               (dashboard/index)
  │   └── [modul]/
  │       ├── index.blade.php      (list dengan datatable)
  │       ├── create.blade.php     (form create)
  │       ├── edit.blade.php       (form edit)
  │       └── show.blade.php       (detail)
  └── components/
      └── ...komponen reusable
  ```
- Manfaatkan **Dasher Components** (Bootstrap 5 based) untuk:
  - DataTables untuk list data
  - Form validation via Bootstrap + Laravel Form Request
  - Modal, Toast, Alert, Button, Card, dll
- Untuk setiap halaman **list data**, gunakan **Dasher DataTable** + **Laravel server-side processing** (yajra/datatables atau manual server side).
- Asset (CSS, JS) mengikuti struktur Dasher. Letakkan custom CSS/JS di `public/css/` dan `public/js/`.

## File & Folder Structure
- Ikuti struktur Laravel default (`app/`, `routes/`, `resources/`, `database/`, dll).
- Struktur folder yang wajib ada:
  ```
  app/
  ├── Repositories/     (WAJIB — semua akses data & business logic disini)
  │   ├── Contracts/    (Interface)
  │   ├── Eloquent/     (implementasi untuk database)
  │   └── Api/          (implementasi untuk REST API eksternal)
  ├── Actions/          (opsional — untuk single-action classes)
  ├── Http/
  │   ├── Controllers/  (hanya routing & response handling)
  │   └── Requests/     (Form Request validation)
  └── Models/
  ```
- **Tidak ada folder `Services/`** — business logic gabung di Repository.
- Gunakan **Route Model Binding** untuk clean routing.
- Gunakan **Blade Components** untuk UI yang reusable.

## Performance
- Gunakan **Caching** (Redis/Memcached) untuk data yang jarang berubah.
- Implementasikan **Queue & Jobs** untuk task yang lambat.
- Optimasi query dengan **Eager Loading**.
- Gunakan **Laravel Telescope** atau **Debugbar** untuk profiling (development only).

## Caching (WAJIB)
- **WAJIB** menggunakan cache untuk semua data yang jarang berubah (master data, referensi, konfigurasi, dll).
- **Durasi cache: 2 menit (120 detik)**. Gunakan `Cache::remember()` atau `Cache::tags()`.
- **WAJIB** mendefinisikan semua cache key sebagai **constant** di sebuah **Service Provider** khusus (misal `CacheKeyServiceProvider` atau di `AppServiceProvider`).
- Contoh definisi cache key:
  ```php
  // app/Providers/CacheKeyServiceProvider.php
  class CacheKeyServiceProvider extends ServiceProvider
  {
      public const CACHE_TTL = 120; // 2 menit
  
      public const LIST_JURUSAN    = 'jurusan:list';
      public const LIST_PRODI      = 'prodi:list';
      public const LIST_TAHUN_AKAD = 'tahun_akademik:list';
      public const LIST_MASTER_KATEGORI = 'master:kategori:list';
  
      public function boot(): void
      {
          // bind constants jika perlu
      }
  }
  ```
- Contoh penggunaan:
  ```php
  // ✅ BENAR — cache key dari constant, TTL 120 detik
  $jurusan = Cache::remember(
      CacheKeyServiceProvider::LIST_JURUSAN,
      CacheKeyServiceProvider::CACHE_TTL,
      fn () => $this->findAll(['aktif' => true])
  );
  
  // ❌ SALAH — hardcoded string key & arbitrary TTL
  $jurusan = Cache::remember('jurusan_list', 300, fn () => ...);
  ```
- **Hapus cache** saat data berubah (create/update/delete):
  ```php
  public function updateJurusan(int $id, array $data): Jurusan
  {
      $result = parent::update($id, $data);
      Cache::forget(CacheKeyServiceProvider::LIST_JURUSAN);
      return $result;
  }
  ```
- Gunakan **Cache Tags** (Redis/Memcached) jika perlu flush grup key sekaligus:
  ```php
  Cache::tags(['master', 'jurusan'])->flush();
  ```
- Jangan cache data yang sifatnya real-time atau spesifik per-user tanpa differensiasi key.

## Error Handling
- Gunakan **Laravel Exception Handler** (class `Handler` di `app/Exceptions/`) untuk custom error responses.
- Log errors dengan level yang sesuai (`error`, `warning`, `info`).
- Jangan expose stack trace di production.
- Gunakan **Sentry** atau **Flare** untuk error tracking jika tersedia.
- Beri user-friendly error messages.

## Dependency Management
- Gunakan **Composer** untuk PHP dependencies.
- Update dependencies secara berkala (`composer update`).
- Hindari menggunakan package yang tidak terawat/deprecated.
- Pin major version di `composer.json` untuk stabilitas.