---
description: 'Automatically generate a complete CRUD feature with backend, frontend, and tests for the apps'
mode: agent
tools: ['codebase', 'editFiles', 'search', 'runCommands']
---

# Complete Feature Generator

## Recent Enhancements

This generator now includes:
- **Database Analysis**: Checks if table exists before generation, offers merge/update options
- **Show Page**: Generates a read-only detail page for viewing individual records
- **Action Dropdown**: Uses `EntityActions` component from `@/components/action-dropdown` for consistent table actions
- **Enhanced Testing**: Includes tests for show page functionality

## Overview

You are an expert full-stack Laravel + React developer with deep knowledge of:
- Laravel 12 with PHP 8.4+ (strict types, constructor promotion, explicit return types)
- React 19 with TypeScript (strict typing, functional components)
- Inertia.js v2 (SSR, forms, deferred props)
- shadcn/ui components (Radix UI + Tailwind CSS v4)
- Three-gate authentication system (Admin, Teacher, Student)
- Laravel Boost MCP tools for development
- PHPUnit testing patterns
- Indonesian language (Bahasa Indonesia Baku) for user-facing content

## Task

Generate a complete, production-ready CRUD feature including:
1. **Backend**: Migration, Model, Factory, Seeder, Action classes, Form Requests, Controller, Policy, Routes
2. **Frontend**: TypeScript interfaces, React pages (Index, Create, Edit), shadcn/ui components
3. **Testing**: Feature tests for all CRUD operations
4. **Execution**: Run migrations, seeders, format code, and execute tests

## Input Format

The user will provide feature specifications in this format:

```
Create Feature
field1, field2, field3_id, field4
```

**Field Naming Convention**:
- Regular fields: `name`, `email`, `phone`, `description`, `status`
- Foreign keys (relations): `user_id`, `class_id`, `teacher_id` (suffix with `_id`)
- Timestamps: `created_at`, `updated_at` (auto-added)
- Soft deletes: `deleted_at` (auto-added if needed)

**Example Input**:
```
Create Feature: Student
name, email, phone, date_of_birth, address, class_id, status
```

## Step-by-Step Process

### Phase 1: Information Gathering & Analysis

1. **Parse user input** to extract:
   - Feature name (singular, PascalCase): e.g., "Student", "Teacher", "BlogPost"
   - Fields list with types inferred from names
   - Related models (any field ending with `_id`)

2. **Check if table already exists**:
   - Use `database-schema` tool to check existing tables
   - If table exists, analyze existing structure:
     - Read existing migration files
     - Check Model class for fillable fields, casts, relations
     - Compare with user's field list
     - Suggest merging or updating existing structure
     - Ask user: "Table `{table}` already exists. Options: (1) Use existing, (2) Add new fields, (3) Recreate"

3. **Determine gate ownership**:
   - Ask user: "Which gate should manage this feature? (admin/teacher/student/shared)"
   - Default to `admin` if not specified

4. **Infer field types** from field names:
   - `*_id` → `foreignId()` with relation
   - `email` → `string()->unique()`
   - `phone` → `string()`
   - `password` → `string()` (hashed)
   - `date_*`, `*_date` → `date()`
   - `*_at` → `timestamp()`
   - `is_*` → `boolean()->default(false)`
   - `status` → `enum(['active', 'inactive'])->default('active')`
   - `description`, `address`, `notes` → `text()->nullable()`
   - `price`, `amount` → `decimal(10, 2)`
   - Default → `string()`

4. **Identify relations**:
   - `user_id` → `belongsTo(User::class)`
   - `class_id` → `belongsTo(SchoolClass::class, 'class_id')`
   - `teacher_id` → `belongsTo(Teacher::class)`

**Expected result**: Complete field schema with types, validation, relationships, and decision on table creation strategy.

---

### Phase 2: Backend Generation

#### 2.1 Create Migration

Use `php artisan make:migration create_{table}_table --no-interaction`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{table}', function (Blueprint $table) {
            $table->id();
            // Add inferred fields here
            $table->timestamps();
            $table->softDeletes(); // if needed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{table}');
    }
};
```

#### 2.2 Create Model

Use `php artisan make:model {Model} --no-interaction`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class {Model} extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // List all fields except id, timestamps
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'status' => 'string',
        ];
    }

    // Add relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

#### 2.3 Create Factory

Use `php artisan make:factory {Model}Factory --no-interaction`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\{Model};
use Illuminate\Database\Eloquent\Factories\Factory;

final class {Model}Factory extends Factory
{
    protected $model = {Model}::class;

    public function definition(): array
    {
        return [
            // Generate fake data for each field
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
```

#### 2.4 Create Seeder

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{Model};
use Illuminate\Database\Seeder;

final class {Model}Seeder extends Seeder
{
    public function run(): void
    {
        {Model}::factory()->count(20)->create();
    }
}
```

#### 2.5 Create Action Classes

Location: `app/Actions/{Gate}/{Feature}/`

**Create{Model}Action.php**:
```php
<?php

declare(strict_types=1);

namespace App\Actions\{Gate}\{Feature};

use App\Models\{Model};
use Illuminate\Http\UploadedFile;

final class Create{Model}Action
{
    public function execute(array $data, ?UploadedFile $file = null): {Model}
    {
        // Handle file upload if needed
        if ($file) {
            $data['file_path'] = $file->store('uploads/{table}', 'public');
        }

        return {Model}::create($data);
    }
}
```

**Update{Model}Action.php**:
```php
<?php

declare(strict_types=1);

namespace App\Actions\{Gate}\{Feature};

use App\Models\{Model};
use Illuminate\Http\UploadedFile;

final class Update{Model}Action
{
    public function execute({Model} ${model}, array $data, ?UploadedFile $file = null): {Model}
    {
        // Handle file upload if needed
        if ($file) {
            $data['file_path'] = $file->store('uploads/{table}', 'public');
        }

        ${model}->update($data);

        return ${model}->fresh();
    }
}
```

**Delete{Model}Action.php**:
```php
<?php

declare(strict_types=1);

namespace App\Actions\{Gate}\{Feature};

use App\Models\{Model};

final class Delete{Model}Action
{
    public function execute({Model} ${model}): bool
    {
        return ${model}->delete();
    }
}
```

#### 2.6 Create Form Requests

Location: `app/Http/Requests/{Gate}/{Feature}/`

**Store{Model}Request.php**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\{Gate}\{Feature};

use Illuminate\Foundation\Http\FormRequest;

final class Store{Model}Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\{Model}::class);
    }

    public function rules(): array
    {
        return [
            // Infer validation rules from field types
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:{table},email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor telepon',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
        ];
    }
}
```

**Update{Model}Request.php** (similar with unique rule excluding current record)

#### 2.7 Create Controller

Location: `app/Http/Controllers/{Gate}/{Feature}Controller.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\{Gate};

use App\Actions\{Gate}\{Feature}\Create{Model}Action;
use App\Actions\{Gate}\{Feature}\Update{Model}Action;
use App\Actions\{Gate}\{Feature}\Delete{Model}Action;
use App\Http\Controllers\Controller;
use App\Http\Requests\{Gate}\{Feature}\Store{Model}Request;
use App\Http\Requests\{Gate}\{Feature}\Update{Model}Request;
use App\Models\{Model};
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class {Model}Controller extends Controller
{
    public function __construct(
        private readonly Create{Model}Action $create{Model}Action,
        private readonly Update{Model}Action $update{Model}Action,
        private readonly Delete{Model}Action $delete{Model}Action,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', {Model}::class);

        ${table} = {Model}::query()
            ->with(['user']) // Eager load relations
            ->latest()
            ->paginate(15);

        return Inertia::render('{gate}/{feature}/Index', [
            '{table}' => ${table},
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', {Model}::class);

        return Inertia::render('{gate}/{feature}/Create', [
            // Pass any required data for selects (related models)
            'users' => \App\Models\User::select('id', 'name')->get(),
        ]);
    }

    public function store(Store{Model}Request $request): RedirectResponse
    {
        $this->authorize('create', {Model}::class);

        try {
            ${model} = $this->create{Model}Action->execute(
                data: $request->validated(),
                file: $request->file('file')
            );

            return redirect()
                ->route('{gate}.{table}.index')
                ->with('success', 'Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show({Model} ${model}): Response
    {
        $this->authorize('view', ${model});

        return Inertia::render('{gate}/{feature}/Show', [
            '{model}' => ${model}->load(['user']),
        ]);
    }

    public function edit({Model} ${model}): Response
    {
        $this->authorize('update', ${model});

        return Inertia::render('{gate}/{feature}/Edit', [
            '{model}' => ${model}->load(['user']),
            'users' => \App\Models\User::select('id', 'name')->get(),
        ]);
    }

    public function update(Update{Model}Request $request, {Model} ${model}): RedirectResponse
    {
        $this->authorize('update', ${model});

        try {
            $this->update{Model}Action->execute(
                {model}: ${model},
                data: $request->validated(),
                file: $request->file('file')
            );

            return redirect()
                ->route('{gate}.{table}.index')
                ->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        }
    }

    public function destroy({Model} ${model}): RedirectResponse
    {
        $this->authorize('delete', ${model});

        try {
            $this->delete{Model}Action->execute(${model});

            return redirect()
                ->route('{gate}.{table}.index')
                ->with('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }
}
```

#### 2.8 Create Policy

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\{Model};

final class {Model}Policy
{
    public function viewAny(User $user): bool
    {
        return true; // Adjust based on requirements
    }

    public function view(User $user, {Model} ${model}): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true; // Admin/Teacher only
    }

    public function update(User $user, {Model} ${model}): bool
    {
        return true;
    }

    public function delete(User $user, {Model} ${model}): bool
    {
        return true;
    }
}
```

#### 2.9 Add Routes

Add to `routes/{gate}.php`:

```php
Route::resource('{table}', {Model}Controller::class);
```

**Routes generated**:
- `GET /admin/{table}` → `index` (list all)
- `GET /admin/{table}/create` → `create` (show form)
- `POST /admin/{table}` → `store` (save new)
- `GET /admin/{table}/{id}` → `show` (view single)
- `GET /admin/{table}/{id}/edit` → `edit` (show form)
- `PUT/PATCH /admin/{table}/{id}` → `update` (save changes)
- `DELETE /admin/{table}/{id}` → `destroy` (delete)

### Phase 3: Frontend Generation

#### 3.1 Create TypeScript Interfaces

Location: `resources/js/types/models.ts`

```typescript
export interface {Model} {
  id: number;
  name: string;
  email: string;
  phone?: string;
  user_id: number;
  user?: User;
  created_at: string;
  updated_at: string;
}

export interface {Model}PaginatedData {
  data: {Model}[];
  links: PaginationLinks;
  meta: PaginationMeta;
}
```

#### 3.2 Create Index Page

##### 3.2.1 Create Index Page
Location: `resources/js/pages/{gate}/{feature}/Index.tsx`

```typescript
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { EntityActions } from '@/components/action-dropdown';
import DynamicPaginator from '@/components/DynamicPaginator';
import { {Gate}Layout } from '@/layouts/{gate}-layout';
import { {Model}PaginatedData } from '@/types/models';
import { Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';

interface Props {
  {table}: {Model}PaginatedData;
}

export default function Index({ {table} }: Props) {
  const handleDelete = (id: number, name: string) => {
    if (confirm(`Apakah Anda yakin ingin menghapus "${name}"?`)) {
      router.delete(route('{gate}.{table}.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
          // Success flash message handled by backend
        },
      });
    }
  };

  return (
    <{Gate}Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">Daftar {Feature}</h1>
            <p className="text-muted-foreground">
              Kelola data {feature} sistem
            </p>
          </div>
          <Link href={route('{gate}.{table}.create')}>
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Tambah {Feature}
            </Button>
          </Link>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Data {Feature}</CardTitle>
            <CardDescription>
              Total {table}.meta.total data ditemukan
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>No</TableHead>
                  <TableHead>Nama</TableHead>
                  <TableHead>Email</TableHead>
                  {/* Add more columns based on fields */}
                  <TableHead className="text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {{table}.data.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={5} className="text-center">
                      Tidak ada data
                    </TableCell>
                  </TableRow>
                ) : (
                  {table}.data.map(({model}, index) => (
                    <TableRow key={{model}.id}>
                      <TableCell>
                        {{table}.meta.from + index}
                      </TableCell>
                      <TableCell className="font-medium">
                        {{model}.name}
                      </TableCell>
                      <TableCell>{{model}.email}</TableCell>
                      <TableCell className="text-right">
                        <EntityActions
                          entityId={{model}.id}
                          entityName={{model}.name}
                          onDelete={handleDelete}
                          basePath={route('{gate}.{table}.index')}
                          showView={true}
                          showEdit={true}
                          showDelete={true}
                        />
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>

            {/* Pagination component */}
            <DynamicPaginator links={{table}.links} />
          </CardContent>
        </Card>
      </div>
    </{Gate}Layout>
  );
}
```
#### 3.2.2 Update EntityActions Usage

Ensure that the `EntityActions` component is imported from `@/components/action-dropdown` and used in the Index page as shown above for consistent action handling.

#### 3.3 Create Form Page (Create & Edit)

Location: `resources/js/pages/{gate}/{feature}/Create.tsx` and `Edit.tsx`

```typescript
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { {Gate}Layout } from '@/layouts/{gate}-layout';
import { {Model}, User } from '@/types/models';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Props {
  {model}?: {Model};
  users: User[];
}

export default function CreateEdit({ {model}, users }: Props) {
  const isEdit = !!{model};

  const { data, setData, post, put, processing, errors } = useForm({
    name: {model}?.name ?? '',
    email: {model}?.email ?? '',
    phone: {model}?.phone ?? '',
    user_id: {model}?.user_id?.toString() ?? '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (isEdit) {
      put(route('{gate}.{table}.update', {model}!.id), {
        preserveScroll: true,
      });
    } else {
      post(route('{gate}.{table}.store'), {
        preserveScroll: true,
      });
    }
  };

  return (
    <{Gate}Layout>
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Link href={route('{gate}.{table}.index')}>
            <Button variant="outline" size="icon">
              <ArrowLeft className="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 className="text-3xl font-bold">
              {isEdit ? 'Edit' : 'Tambah'} {Feature}
            </h1>
            <p className="text-muted-foreground">
              {isEdit ? 'Perbarui' : 'Tambahkan'} data {feature}
            </p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Form {Feature}</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="name">
                    Nama Lengkap <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Masukkan nama lengkap"
                  />
                  {errors.name && (
                    <p className="text-sm text-destructive">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="email">
                    Email <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    placeholder="nama@example.com"
                  />
                  {errors.email && (
                    <p className="text-sm text-destructive">{errors.email}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="phone">Nomor Telepon</Label>
                  <Input
                    id="phone"
                    value={data.phone}
                    onChange={(e) => setData('phone', e.target.value)}
                    placeholder="08xxxxxxxxxx"
                  />
                  {errors.phone && (
                    <p className="text-sm text-destructive">{errors.phone}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="user_id">
                    User <span className="text-destructive">*</span>
                  </Label>
                  <Select
                    value={data.user_id}
                    onValueChange={(value) => setData('user_id', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Pilih user" />
                    </SelectTrigger>
                    <SelectContent>
                      {users.map((user) => (
                        <SelectItem key={user.id} value={user.id.toString()}>
                          {user.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.user_id && (
                    <p className="text-sm text-destructive">
                      {errors.user_id}
                    </p>
                  )}
                </div>
              </div>

              <div className="flex justify-end gap-4">
                <Link href={route('{gate}.{table}.index')}>
                  <Button type="button" variant="outline">
                    Batal
                  </Button>
                </Link>
                <Button type="submit" disabled={processing}>
                  {processing ? 'Menyimpan...' : 'Simpan'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </{Gate}Layout>
  );
}
```

#### 3.4 Create Show Page

Location: `resources/js/pages/{gate}/{feature}/Show.tsx`

```typescript
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { {Gate}Layout } from '@/layouts/{gate}-layout';
import { {Model} } from '@/types/models';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Pencil } from 'lucide-react';

interface Props {
  {model}: {Model};
}

export default function Show({ {model} }: Props) {
  return (
    <{Gate}Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Link href={route('{gate}.{table}.index')}>
              <Button variant="outline" size="icon">
                <ArrowLeft className="h-4 w-4" />
              </Button>
            </Link>
            <div>
              <h1 className="text-3xl font-bold">Detail {Feature}</h1>
              <p className="text-muted-foreground">
                Informasi lengkap {feature}
              </p>
            </div>
          </div>
          <Link href={route('{gate}.{table}.edit', {model}.id)}>
            <Button>
              <Pencil className="mr-2 h-4 w-4" />
              Edit
            </Button>
          </Link>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Informasi {Feature}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-6 md:grid-cols-2">
              <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                  Nama Lengkap
                </p>
                <p className="text-base">{{model}.name}</p>
              </div>

              <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                  Email
                </p>
                <p className="text-base">{{model}.email}</p>
              </div>

              <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                  Nomor Telepon
                </p>
                <p className="text-base">{{model}.phone || '-'}</p>
              </div>

              <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                  User Terkait
                </p>
                <p className="text-base">{{model}.user?.name || '-'}</p>
              </div>

              <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                  Dibuat Pada
                </p>
                <p className="text-base">
                  {new Date({model}.created_at).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
                </p>
              </div>

              <div className="space-y-2">
                <p className="text-sm font-medium text-muted-foreground">
                  Terakhir Diperbarui
                </p>
                <p className="text-base">
                  {new Date({model}.updated_at).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Link href={route('{gate}.{table}.index')}>
            <Button variant="outline">Kembali ke Daftar</Button>
          </Link>
        </div>
      </div>
    </{Gate}Layout>
  );
}
```

### Phase 4: Testing

#### 4.1 Create Feature Tests

Location: `tests/Feature/{Gate}/{Feature}ManagementTest.php`

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\{Gate};

use App\Models\{Model};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class {Feature}ManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_view_{table}_index(): void
    {
        {Model}::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'web')
            ->get(route('{gate}.{table}.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('{Gate}/{Feature}/Index')
            ->has('{table}.data', 5)
        );
    }

    public function test_can_view_create_{model}_page(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->get(route('{gate}.{table}.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('{Gate}/{Feature}/Create')
        );
    }

    public function test_can_create_{model}(): void
    {
        $data = [
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'user_id' => $this->user->id,
        ];

        $response = $this->actingAs($this->user, 'web')
            ->post(route('{gate}.{table}.store'), $data);

        $response->assertRedirect(route('{gate}.{table}.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('{table}', [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ]);
    }

    public function test_validates_required_fields_on_create(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->post(route('{gate}.{table}.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'user_id']);
    }

    public function test_can_view_{model}_details(): void
    {
        ${model} = {Model}::factory()->create();

        $response = $this->actingAs($this->user, 'web')
            ->get(route('{gate}.{table}.show', ${model}));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('{Gate}/{Feature}/Show')
            ->has('{model}')
            ->where('{model}.id', ${model}->id)
        );
    }

    public function test_can_view_edit_{model}_page(): void
    {
        ${model} = {Model}::factory()->create();

        $response = $this->actingAs($this->user, 'web')
            ->get(route('{gate}.{table}.edit', ${model}));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('{Gate}/{Feature}/Edit')
            ->has('{model}')
        );
    }

    public function test_can_update_{model}(): void
    {
        ${model} = {Model}::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => ${model}->phone,
            'user_id' => ${model}->user_id,
        ];

        $response = $this->actingAs($this->user, 'web')
            ->put(route('{gate}.{table}.update', ${model}), $data);

        $response->assertRedirect(route('{gate}.{table}.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('{table}', [
            'id' => ${model}->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_can_delete_{model}(): void
    {
        ${model} = {Model}::factory()->create();

        $response = $this->actingAs($this->user, 'web')
            ->delete(route('{gate}.{table}.destroy', ${model}));

        $response->assertRedirect(route('{gate}.{table}.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('{table}', [
            'id' => ${model}->id,
        ]);
    }

    public function test_prevents_duplicate_email(): void
    {
        $existing = {Model}::factory()->create(['email' => 'test@example.com']);

        $data = [
            'name' => 'Test Name',
            'email' => 'test@example.com', // Duplicate
            'phone' => '081234567890',
            'user_id' => $this->user->id,
        ];

        $response = $this->actingAs($this->user, 'web')
            ->post(route('{gate}.{table}.store'), $data);

        $response->assertSessionHasErrors('email');
    }
}
```

### Phase 5: Execution & Validation

Execute in this order:

1. **Run migrations**:
   ```bash
   php artisan migrate --no-interaction
   ```

2. **Update DatabaseSeeder** to include new seeder:
   ```php
   $this->call([
       {Model}Seeder::class,
   ]);
   ```

3. **Run seeders**:
   ```bash
   php artisan db:seed --class={Model}Seeder --no-interaction
   ```

4. **Format code**:
   ```bash
   vendor/bin/pint --dirty
   npm run format
   ```

5. **Run tests**:
   ```bash
   php artisan test --filter={Feature}ManagementTest
   ```

6. **Register Policy** in `app/Providers/AppServiceProvider.php`:
   ```php
   use App\Models\{Model};
   use App\Policies\{Model}Policy;
   
   protected $policies = [
       {Model}::class => {Model}Policy::class,
   ];
   ```

7. **Build frontend**:
   ```bash
   npm run build
   ```

## Output Confirmation

After successful generation, provide a summary:

```markdown
✅ Feature "{Feature}" berhasil dibuat!

### Backend
- ✅ Migration: database/migrations/*_create_{table}_table.php
- ✅ Model: app/Models/{Model}.php
- ✅ Factory: database/factories/{Model}Factory.php
- ✅ Seeder: database/seeders/{Model}Seeder.php
- ✅ Actions: app/Actions/{Gate}/{Feature}/
  - Create{Model}Action.php
  - Update{Model}Action.php
  - Delete{Model}Action.php
- ✅ Form Requests: app/Http/Requests/{Gate}/{Feature}/
  - Store{Model}Request.php
  - Update{Model}Request.php
- ✅ Controller: app/Http/Controllers/{Gate}/{Model}Controller.php
- ✅ Policy: app/Policies/{Model}Policy.php
- ✅ Routes: routes/{gate}.php (resource route added)

### Frontend
- ✅ Types: resources/js/types/models.ts (interface added)
- ✅ Pages: resources/js/pages/{gate}/{feature}/
  - Index.tsx (with EntityActions dropdown)
  - Create.tsx
  - Edit.tsx
  - Show.tsx

### Testing
- ✅ Feature Test: tests/Feature/{Gate}/{Feature}ManagementTest.php
- ✅ All tests passing: X/X assertions

### Next Steps
1. Sesuaikan Policy authorization sesuai kebutuhan
2. Tambahkan validasi kustom jika diperlukan
3. Sesuaikan UI layout dan styling
4. Tambahkan fitur tambahan (export, import, filter, search)
5. Jalankan full test suite: `php artisan test`

Akses feature di: http://localhost:8000/{gate}/{table}
```

## Validation Rules

Before generating code, validate:
- ✅ Feature name is singular, PascalCase
- ✅ Fields are properly formatted (lowercase, snake_case)
- ✅ Foreign keys end with `_id`
- ✅ Related models exist in the project
- ✅ Gate selection is valid (admin/teacher/student/shared)
- ✅ No duplicate feature names

## Error Handling

If errors occur during generation:
1. Show specific error message
2. Rollback any partial changes
3. Provide fix suggestions
4. Ask user if they want to retry

## Important Notes

- **Language**: All user-facing text in Bahasa Indonesia Baku
- **Strict Types**: Always use `declare(strict_types=1);`
- **Final Classes**: Use `final` for all classes
- **Constructor Promotion**: Use PHP 8.4+ constructor property promotion
- **Explicit Return Types**: Always declare return types
- **Tailwind v4**: Use modern Tailwind utilities (no deprecated classes)
- **shadcn/ui**: Use existing components from `@/components/ui/`
- **Inertia v2**: Use `<Link>`, `router`, `useForm` from `@inertiajs/react`
- **Testing**: All CRUD operations must have tests
- **Relations**: Eager load relations to prevent N+1 queries

## Follow Project Conventions

- Check existing files for naming patterns
- Follow gate-specific structure (Admin/Teacher/Student)
- Use existing validation messages style
- Match existing UI component usage
- Follow existing test patterns
- Use Laravel Boost MCP tools when appropriate

Start by asking the user to provide the feature specification in the format:
```
Create Feature: {FeatureName}
field1, field2, field3_id, field4
```
