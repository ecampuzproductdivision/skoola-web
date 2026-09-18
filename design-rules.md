# KejarKarir — Design Rules & Guidelines

> **Project:** KejarKarir Admin Dashboard (Laravel + Blade, Bootstrap 5, Public Sans)
> **Primary color:** `#37618A` · **Secondary color:** `#EF8781`
> This document is the single source of truth for visual design decisions. Always align new UI with these rules.

---

## 1. Design Principles

1. **Clarity first** — the dashboard is a tool. Reduce visual noise; every element must serve a purpose.
2. **Consistency** — use only the tokens and component patterns defined here. Do not invent new colors, radii, or shadows.
3. **Accessibility** — honor WCAG AA contrast on all text (4.5:1 normal, 3:1 large).
4. **Responsive by default** — mobile-first with the Bootstrap 5 grid.
5. **Light & Dark** — all components must render correctly in both modes using Bootstrap color modes.

---

## 2. Color Guidelines

### 2.1 Brand Palette

| Token | Hex | Role |
|-------|-----|------|
| **Primary**  | `#37618A` | Brand blue — primary buttons, active states, key highlights, focus rings |
| **Secondary**| `#EF8781` | Brand coral — supporting accents, success-adjacent actions, badges, charts |

**Primary scale (`#37618A`)**

| Token | Hex |
|-------|-----|
| Primary-50  | `#e7ecf1` |
| Primary-100 | `#cdd8e2` |
| Primary-200 | `#a5b8ca` |
| Primary-300 | `#7d98b3` |
| Primary-400 | `#597c9e` |
| **Primary-500** | **`#37618A`** (base) |
| Primary-600 | `#2e5173` |
| Primary-700 | `#243f5a` |
| Primary-800 | `#192c3e` |
| Primary-900 | `#0e1823` |

**Secondary (`#EF8781`) — coral scale**

| Token | Hex |
|-------|-----|
| Secondary-50  | `#fdf1f0` |
| Secondary-100 | `#fbe1e0` |
| Secondary-200 | `#f8c9c6` |
| Secondary-300 | `#f5b1ad` |
| Secondary-400 | `#f29b96` |
| **Secondary-500** | **`#EF8781`** (base) |
| Secondary-600 | `#c6706b` |
| Secondary-700 | `#9b5854` |
| Secondary-800 | `#6c3d3a` |
| Secondary-900 | `#3c2220` |

### 2.2 Neutral & State Colors

| Token | Hex | Role |
|-------|-----|------|
| `-white` | `#ffffff` | Light surfaces |
| `-gray-100…gray-900` | `#f8f9fa → #212529` | Neutrals (Bootstrap scale) |
| Success | `#198754` | Positive / saved / success states |
| Danger  | `#dc3545` | Destructive / delete / errors |
| Warning | `#ffc107` | Non-critical alerts |
| Info    | `#0dcaf0` | Informational helpers |
| Dark    | `#212529` | Dark mode surfaces |

### 2.3 Color Usage Rules

1. **One primary action per view.** Use `btn-primary` (`#37618A`) for the single most important action — never two competing primary buttons in one card.
2. **Secondary is an accent.** `#EF8781` is for supporting elements (badges, icons, secondary charts, "done" chips) — not for large-filled buttons that compete with primary.
3. **Neutrals dominate.** Most surfaces, borders, and muted text use neutral scales. Brand colors are *highlights*, not the default carpet.
4. **Never use brand colors for destructive actions** — always `danger`.
5. **Contrast first.** Use Primary-600+ and Secondary-600+ as text on white. Use Primary-100/Secondary-100 as soft backgrounds with dark text.
6. **Dark mode:** raise all brand tints (use lighter scales) and lower neutrals; never use full-saturation brand as a background in dark mode.

### 2.4 Focus & Selection

- Active nav item: `primary` background tint with `primary` text.
- Input focus ring: `2–3px` inset ring in `primary` at ~`0.25` opacity.
- Selected rows / switches: `primary`.

---

## 3. Typography

- **Font family:** `Public Sans` (300–800 weights). Loaded via Google Fonts in the layout head.
- **Fallbacks:** `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`.
- Sizes (Bootstrap scale):
  - Display/Large titles: `fs-1`–`fs-2`
  - Section headings: `fs-3`–`fs-4` (bold `600–700`)
  - Body: `fs-5` / default `16px`
  - Secondary/meta text: `small` / `text-muted`
- **Weights:** Headings `600–700`; body `400`; emphasis labels `500–600`.
- **Line-height:** `1.5` for body; `1.2`–`1.3` for headings.
- Never use font weights above `800`. Avoid ALL-CAPS except tiny labels/eyebrows.

---

## 4. Spacing & Layout

- Base spacing unit: `4px` (Bootstrap `$spacer`). Use the built-in scale (`1rem` = 4 units).
- Card padding: `1.25rem–1.5rem`; section gap `1.5rem`.
- Content max width: `container` (default), constrained for card-heavy pages.
- Use the 12-column Bootstrap grid for all layout; no ad-hoc fixed widths.

---

## 5. Components

### 5.1 Buttons
| Variant | Use |
|---------|-----|
| `btn-primary`   | Main action (primary — once per view) |
| `btn-success`   | Confirmation on destructive flows |
| `btn-outline-primary` | Secondary / alternative action |
| `btn-light` / `btn-outline-secondary` | Neutral / cautious |
| `btn-danger`    | Destructive (confirm-modal) |

- Radius: `0.5rem`. Padding: default Bootstrap.
- **An add "Create / Add" button always uses `btn-primary`.** List-row actions use icon `btn-light` ghost buttons.

### 5.2 Forms
- Labels: `600` weight, `13px`. Help text: `text-muted`.
- Inputs: `0.45rem` radius, subtle border, primary focus ring.
- Errors: red border + `.invalid-feedback`; always validate server + client side.
- Required fields: red asterisk `*`.

### 5.3 Cards & Panels
- Card radius `1rem`, subtle border, optional soft shadow; internal sections separated with borders only.
- Tables inside cards: header row `600` weight, hover row highlight, `table-striped`.

### 5.4 Badges & Status Chips
- `badge bg-success` = active/complete
- `badge bg-warning-text` = pending
- `badge bg-danger-text` = inactive/blocked
- `badge bg-secondary` = neutral

### 5.5 Modals & Alerts
- Destructive confirms → `alert-danger` modal.
- Success/danger toasts bottom-right; auto-dismiss success, manual dismiss danger.

---

## 6. Navigation (Sidebar)

- Sidebar width (expanded) `260px`, collapsed `mini` state persisted in localStorage (`sidebarExpanded`).
- Nav items: icon (`20px`, outline) + label.
- Active item: `primary` background & icon; inactive uses `gray-700` on light.
- Group/section headers: uppercase eyebrows, muted.
- "Upgrade" CTA card sits at the bottom (`btn-primary`).
- Dropdown children indent under parent menu.
- Navbar (header) dan Sidebar memakai **border solid** — jangan gunakan `border-dashed` pada komponen navigasi (dropdown notifikasi, dropdown profil, modal pencarian, dll).

---

## 7. Imagery & Iconography

- **Icon set:** Tabler icons (`ti-*`) — consistent `16–20px`, `stroke-width 1.5`.
- **Avatar:** circular `rounded-circle`; brand logo `object-fit: contain`.
- Choose from Tabler only; never mix icon libraries.

---

## 8. Implementation Notes (Code Level)

- Prefer Bootstrap utility classes and theme variables in Blade; custom CSS lives in `assets/css/theme.min.css` overrides.
- **Brand palette is implemented in `public/assets/css/kejarkarir.css`** (the "SCSS/variables" layer of this theme). The admin theme is a precompiled "Dash UI" theme that reads `--ds-*` CSS custom properties (renamed Bootstrap variables). `kejarkarir.css` re-declares those variables with the brand scale and overrides the hard-coded component colors (buttons, forms, nav, pagination, progress, charts).
- `kejarkarir.css` is referenced **after** `theme.min.css` in `layouts/app.blade.php` and `login.blade.php` so its declarations win the cascade.
- Theme chart colors are centralized in `public/assets/js/vendors/chart.js` (`window.theme` object + ApexCharts `colors`); update brand hex there.
- Do not hardcode hex in Blade views — reference the `--kk-*` / Bootstrap `text-*`, `bg-*` utility classes.
- Every new page uses `layouts.app` + `@yield('content')`; login uses its own full-height layout.
- Brand accent variables (`--kk-primary-*`, `--kk-secondary-*`) are declared once at the top of `kejarkarir.css` and reused throughout.

---

## 9. Accessibility & Quality

- All interactive elements keyboard-focusable and `aria-` labelled.
- Color is never the only signal (accompany with text/icons).
- Contrast ≥ `4.5:1` (normal text) / `3:1` (large). Verify in both themes.
- Tables have header semantics; images have `alt`.
- Buttons/inputs use `disabled` + `aria-disabled` where relevant.

---

## 10. Guardrails (Do / Don't)

**Do:** reuse tokens; one primary action; neutral-first; support dark mode; keep consistency with existing users/roles/menus pages.

**Don't:** introduce new brand hues; use heavy shadows/neon; fill modals with brand colors; stray from the button hierarchy; break contrast for decoration.

---

## 11. List Data Page Standard

> **Berlaku untuk SEMUA halaman list data (index)** — users, roles, permissions, menus, dan seluruh menu list yang akan dibuat ke depannya. Halaman list data WAJIB mengikuti struktur & aturan styling di bawah ini.

### 11.1 Struktur Halaman (Urutan Wajib)

Susunan elemen dari atas ke bawah pada halaman list data:

1. **Breadcrumb** — navigasi hierarki halaman.
2. **Page Header Section**
   - **Kiri:** page title (`<h1>`) dan menu description di bawahnya.
   - **Kanan:** action button utama (create/add) — rata kanan.
3. **Card Data** — SATU card tunggal berisi 3 baris:
   - **Row 1 (card header):**
     - **Kiri:** filter terkait data (search input, dropdown filter, tombol filter & reset).
     - **Kanan:** tombol **Export** dan **Print**.
   - **Row 2:** jumlah data — format `Menampilkan ... dari ... data`.
   - **Row 3:** datatable berisi data (server-side + pagination).

### 11.2 Rules Styling

| Elemen | Class Wajib | Keterangan |
|--------|-------------|------------|
| Action button di page header | `btn btn-primary` | Tombol utama (Create/Add). Cukup 1 action primary per halaman. |
| Tombol di dalam Card Data row 1 | `btn btn-white` | Tombol filter submit/reset, tombol Export, tombol Print. |
| Menu description (di bawah page title) | `text-gray-600` | Warna `gray-600` untuk deskripsi menu. |
| | Row 2 jumlah data (di atas datatable) | `d-flex align-items-center gap-2 px-4 py-3 text-gray-600` | Tanpa `border-top` — section jumlah data tidak memakai border pemisah atas. |

Aturan turunan:

- **Action button** memakai warna primary (brand blue `#37618A`) dan wajib berlabel jelas + ikon Tabler (`ti-plus`).
- **Tombol Export/Print** berada di sisi kanan row 1 card, memakai `btn btn-white` + label + ikon Tabler (`ti-download`, `ti-printer`).
- **Tombol filter** (`Terapkan`) dan tombol reset memakai `btn btn-white`.
- **Menu description** di bawah page title memakai `text-gray-600` (bukan `text-secondary`).
- **Ikon:** Tabler icons (`ti-*`), ukuran `16–20px`, `stroke-width 1.5`.
- **JANGAN gunakan `rounded-circle` pada button.** Gunakan radius default Bootstrap. `rounded-circle` hanya untuk avatar/foto profil, bukan untuk button. Standar back button & struktur title header section form/detail ada di **Section 12**.
- **JANGAN gunakan `border-0` pada card.** Card memakai border default Bootstrap + `shadow-sm` (hapus `border-0` dari class card).

### 11.3 Aturan Tambahan

- **Form filter memakai `method="GET"`** agar state filter tersimpan di URL (bisa di-bookmark / di-share) dan tetap berlaku saat pagination.
- **Row form filter di dalam card data dibuat SATU BARIS** — jangan gunakan class `flex-wrap` pada form karena akan membuat filter turun menjadi 2 baris. Contoh: `class="d-flex align-items-center gap-2 mb-0"`.
- **Row 2 jumlah data** menampilkan hasil filter terhadap total data: `Menampilkan {count} dari {total} data`.
- **Datatable** menggunakan server-side processing (`$data->paginate()` / `$data->cursorPaginate()`) dan action per-row memakai dropdown ikon (`btn-ghost`).
- **Card data** memakai `card shadow-sm` (border default Bootstrap, tanpa `border-0`).
- **Semua kolom header tabel WAJIB sortable** (kecuali kolom `ACTIONS`). Setiap kolom bisa diklik untuk sorting naik/turun.
  - Parameter query: `?sort={column}&direction=asc|desc`.
  - Sorting dilakukan **server-side** di Repository (whitelist kolom yang diizinkan — jangan pernah orderBy langsung dari request tanpa whitelist).
  - Gunakan komponen Blade `<x-sortable-th label="..." column="..." />` untuk membuat header sortable:
    ```blade
    <thead class="table-light">
      <tr>
        <x-sortable-th label="NAME" column="name" class="py-4 ps-4" />
        <x-sortable-th label="STATUS" column="status" class="py-4" />
        <x-sortable-th label="ACTIONS" class="py-4 text-end pe-4" />
      </tr>
    </thead>
    ```
  - Contoh handling di Repository (whitelist + sanitasi direction):
    ```php
    $sort = $criteria['sort'] ?? null;
    $direction = strtolower((string) ($criteria['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
    $sortable = ['name', 'email', 'created_at']; // whitelist kolom
    if (in_array($sort, $sortable, true)) {
        $query->orderBy($sort, $direction);
    } else {
        $query->latest();
    }
    ```
  - Untuk kolom yang berasal dari **relasi/count** (misal `role`, `users_count`, `children_count`), sertakan nama alias kolom pada whitelist dan buat subquery `orderBy` di Repository agar sorting tetap dilakukan database-side.
  - Untuk **tabel statis** (data hardcoded/demo, misal dashboard), gunakan mode client-side:
    ```blade
    <x-sortable-th label="Name" column="name" client="true" />
    ```
    Komponen akan merender tombol bertanda `.sortable-client` + `data-sort-column`, lalu sorting dilakukan di browser (sertakan `<script>` client-side di halaman tersebut yang membaca `thead th` & menyusun ulang `<tbody>`).
- **Konsisten dengan halaman index yang sudah ada:** pertahankan breadcrumb, page header, summary info, flash messages, dan dropdown action per-row.

### 11.4 Contoh Kerangka Blade (Skeleton)

```blade
@extends('layouts.app')

@section('content')
<div class="custom-container">
  {{-- 1. Breadcrumb --}}
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Menu</li>
    </ol>
  </nav>

  {{-- 2. Page Header Section --}}
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-6">
    <div>
      <h1 class="h2 mb-1">Menu Management</h1>
      <p class="text-gray-600 mb-0">Deskripsi singkat menu.</p>
    </div>
    <div class="d-flex gap-2">
      @can('kejarkarir.menus.create')
      <a href="{{ route('menus.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="ti ti-plus fs-5"></i> Add New Menu
      </a>
      @endcan
    </div>
  </div>

  {{-- 3. Card Data --}}
  <div class="card shadow-sm">
    {{-- Row 1: Filter (kiri) + Export/Print (kanan) --}}
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 bg-transparent py-4">
      <form action="{{ route('menus.index') }}" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-white d-flex align-items-center gap-1">
          <i class="ti ti-filter"></i> Terapkan
        </button>
        <a href="{{ route('menus.index') }}" class="btn btn-white d-inline-flex align-items-center">
          <i class="ti ti-refresh"></i>
        </a>
      </form>
      <div class="d-flex gap-2">
        <a href="#" class="btn btn-white d-flex align-items-center gap-1"><i class="ti ti-download"></i> Export</a>
        <a href="#" class="btn btn-white d-flex align-items-center gap-1"><i class="ti ti-printer"></i> Print</a>
      </div>
    </div>

    {{-- Row 2: Jumlah data --}}
    <div class="d-flex align-items-center gap-2 px-4 py-3 text-gray-600">
      <span class="fw-semibold">Menampilkan {{ $menus->count() }} dari {{ App\Models\Menu::count() }} data</span>
    </div>

    {{-- Row 3: Datatable --}}
    <div class="table-responsive border-top">
      <table class="table table-hover table-centered text-nowrap mb-0">
        <thead class="table-light">
          <tr>
            <x-sortable-th label="COLUMN" column="name" class="py-4 ps-4" />
            <x-sortable-th label="ACTIONS" class="py-4 text-end pe-4" />
          </tr>
        </thead>
        <tbody>
          @forelse ($menus as $menu)
            <tr>
              <td class="py-3 ps-4">{{ $menu->name }}</td>
              <td class="py-3 text-end pe-4">
                {{-- dropdown action btn-ghost --}}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="text-center py-5 text-secondary fs-5">No data found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-flex justify-content-end mt-3">
    {{ $menus->links() }}
  </div>
</div>
@endsection
```

### 11.5 Checklist Penerapan

Setiap halaman list data WAJIB memenuhi checklist berikut:

- [ ] Breadcrumb ada di bagian atas halaman.
- [ ] Page header: title kiri + description `text-gray-600`, action button kanan `btn btn-primary`.
- [ ] Card Data tunggal: row 1 filter + export/print, row 2 jumlah data, row 3 datatable.
- [ ] Tombol di dalam card (filter, reset, export, print) memakai `btn btn-white`.
- [ ] Form filter memakai `method="GET"` dan **satu baris** (tanpa `flex-wrap`).
- [ ] Card memakai `card shadow-sm` (tanpa `border-0`), dan button **tanpa `rounded-circle`**.
- [ ] Datatable server-side + pagination.
- [ ] Setiap kolom header tabel sortable (`<x-sortable-th>` + whitelist di Repository).
- [ ] Ikon memakai Tabler (`ti-*`) ukuran `16–20px`.

---

## 12. Form (Create/Edit) & Detail Page Standard

> **Berlaku untuk SEMUA halaman Create/Edit (form) dan Detail** — users, roles, permissions, menus, dan seluruh halaman form/detail yang akan dibuat ke depannya. Halaman form & detail WAJIB mengikuti struktur & aturan styling di bawah ini.

### 12.1 Struktur Halaman (Urutan Wajib)

Susunan elemen dari atas ke bawah pada halaman form (create/edit) dan detail:

1. **Breadcrumb** — navigasi hierarki halaman.
2. **Title Header Section**
   - **Kiri:** button back (`btn btn-light`) + title halaman (`<h1 class="h2 mb-1">`).
   - **TIDAK ADA deskripsi halaman** di bawah title — title header section hanya berisi title saja.
   - **Kanan:** action buttons (jika ada), misal tombol Edit pada halaman Detail — rata kanan.
3. **Form Errors** (hanya untuk halaman create/edit) — alert validasi `$errors->any()`.
4. **Card Form** (create/edit) — satu card berisi form dengan grid 3 kolom per baris.
5. **Form Actions** — baris terakhir dalam card: `Cancel` (kiri/netral) + `Submit` (primary) rata kanan.

### 12.2 Title Header Section

```blade
<div class="d-flex justify-content-between align-items-center gap-3 mb-6">
  {{-- Kiri: back button + title --}}
  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('xxx.index') }}" class="btn btn-light border btn-icon d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
      <i class="ti ti-arrow-left"></i>
    </a>
    <h1 class="h2 mb-1">Create New User</h1>
  </div>

  {{-- Kanan: action buttons (hanya jika ada) --}}
  <div class="d-flex gap-2">
    {{-- contoh: <a href="{{ route('xxx.edit', $id) }}" class="btn btn-primary">Edit</a> --}}
  </div>
</div>
```

Rules styling:

| Elemen | Class Wajib | Keterangan |
|--------|-------------|------------|
| Back button | `btn btn-light border btn-icon` | Ukuran `40×40` (style width/height inline), ikon Tabler `ti-arrow-left`, **tanpa `rounded-circle`**. |
| Title | `<h1 class="h2 mb-1">` | Title halaman (create/edit/detail). |
| Deskripsi di bawah title | **DILARANG** | Hapus seluruh `<p class="text-secondary mb-0">…</p>` di bawah title. Title header section hanya berisi back button + title di kiri, action buttons di kanan. |
| Action buttons (kanan) | `d-flex gap-2` | Hanya muncul jika ada aksi (misal tombol Edit pada halaman detail). Tidak wajib pada halaman create/edit yang aksinya sudah ada di Form Actions. |

### 12.3 Card Form Layout — Grid 3 Kolom

Aturan grid di dalam card form pada halaman **Tambah (create)** dan **Edit (edit)**:

1. **Satu baris = 3 kolom input.** Setiap input field memakai `col-md-4` (bootstrap grid), sehingga dalam satu `row` ada 3 input bersebelahan.
2. **Kelipatan bukan 3** → baris terakhir tidak penuh, tetap `col-md-4` per field (tetap konsisten lebar kolomnya).
3. **Pengecualian yang TETAP dibolehkan:**
   - **Textarea / Description full-width** — `col-12` / tanpa kolom, karena bersifat deskripsi panjang.
   - **Checkbox grid** (mis. Assign To Roles, Permissions) — tetap `col-md-6 col-lg-4` per checkbox sesuai kebutuhan.
   - **Custom group items** (mis. group permissions per modul) — bebas, konsisten dengan pola yang sudah ada.

Contoh kerangka grid 3 kolom:

```blade
<div class="row g-4 mb-4">
  <!-- Field 1 -->
  <div class="col-md-4">
    <label for="field1" class="form-label fw-semibold text-dark">Field 1 <span class="text-danger">*</span></label>
    <input type="text" name="field1" id="field1" class="form-control @error('field1') is-invalid @enderror" placeholder="..." value="{{ old('field1') }}" required>
    @error('field1')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Field 2 -->
  <div class="col-md-4">
    ...
  </div>

  <!-- Field 3 -->
  <div class="col-md-4">
    ...
  </div>
</div>

<!-- Form Actions -->
<div class="d-flex justify-content-end gap-3 pt-3 border-top">
  <a href="{{ route('xxx.index') }}" class="btn btn-light border px-4 py-2 text-dark">Cancel</a>
  <button type="submit" class="btn btn-danger px-4 py-2">Create User</button>
</div>
```

### 12.4 Aturan Tambahan

- **TIDAK ada deskripsi halaman** di bawah title pada halaman Detail, Tambah, dan Edit — deskripsi hanya diperbolehkan di halaman list (index) di bawah page title (lihat Section 11).
- **Button back memakai `btn btn-light`** (bukan `btn-outline-secondary` / `btn-primary`), berukuran `40×40`, dengan ikon Tabler `ti-arrow-left`.
- **Satu primary action per view** — tombol submit/aksi utama pada form memakai primary (`btn btn-danger` mengikuti skema aksi utama halaman, konsisten dengan halaman existing) dan tombol Cancel memakai `btn btn-light border`.
- **Ikon:** Tabler icons (`ti-*`), ukuran `16–20px`.
- **JANGAN gunakan `rounded-circle` pada button** (lihat Section 12.2) dan **JANGAN gunakan `border-0` pada card** — card memakai `card shadow-sm` (border default Bootstrap).
- **JANGAN gunakan inline SVG** untuk ikon sederhana seperti panah — gunakan Tabler icon class (`ti-*`) yang sudah tersedia di layout.

### 12.5 Checklist Penerapan

Setiap halaman create/edit & detail WAJIB memenuhi checklist berikut:

- [ ] Breadcrumb ada di bagian atas halaman.
- [ ] Title header section: kiri = back button `btn btn-light border btn-icon` (40×40) + title, kanan = action buttons (jika ada).
- [ ] **TIDAK ada deskripsi `<p>` di bawah title header.**
- [ ] Grid form pada create/edit memakai `col-md-4` (3 kolom per baris).
- [ ] Textarea/Description full-width, checkbox grid tetap `col-md-6 col-lg-4`.
- [ ] Form Actions: Cancel `btn btn-light border` + Submit primary, rata kanan, dipisah `border-top`.
- [ ] Card memakai `card shadow-sm` (tanpa `border-0`), button **tanpa `rounded-circle`**.
- [ ] Ikon memakai Tabler (`ti-*`) ukuran `16–20px` (bukan inline SVG).

---

## 13. Auth Pages (Login / Register)

> **Berlaku untuk SEMUA halaman auth tanpa layout admin** — `login.blade.php` (full-height layout eiggen) dan halaman register/forgot-password yang akan dibuat ke depannya. Halaman auth WAJIB mengikuti aturan styling di bawah ini.

### 13.1 Page Background

- **Background halaman login WAJIB memakai warna primary dengan opacily 10%** — brand tint pada seluruh viewport, konsisten dengan brand palette (`#37618A`).
- Implementasi memakai utilita `bg-primary` + variabel opacily `--ds-bg-opacity` yang sudah tersedia di `kejarkarir.css` (`.bg-primary { background-color: rgba(var(--ds-primary-rgb), var(--ds-bg-opacity, 1)) }`):

  ```html
  <body class="bg-primary" style="--ds-bg-opacity: 0.10;">
  ```

- **DILARANG hardcode hex di view** — jangan menulis `background-color: rgba(55, 97, 138, 0.10)` langsung di Blade. Referensi utilita `bg-*` / token `--ds-primary-rgb` dari `kejarkarir.css` (lihat Section 8).
- Opacily `0.10` berlaku untuk **Light AND Dark mode** — `--ds-primary-rgb` tetap aktif di kedua mode, sehingga tint blue konsisten tanpa mengubah surface card.
- **Card login tetap memakai surface default** (white/dark mode background) — hanya `body`/view port yang mendapat tint primary.

### 13.2 Aturan Tambahan

- Halaman auth memakai layout full-height eiggen (login.blade.php) — **jangan** extends `layouts.app` (Section 8).
- Toggle theme (light/dark/auto) tetap tersedia di halaman login (dropdown bottom-right).
- Content card login tidak berubah: `card card-lg`, label form 600 weight, input focus ring primary (Section 5.2).

### 13.3 Checklist Penerapan

Setiap halaman auth WAJIB memenuhi checklist berikut:

- [ ] `<body>` memakai `class="bg-primary"` + `style="--ds-bg-opacity: 0.10;"` (tanpa hardcode hex).
- [ ] Layout full-height eiggen (tanpa `layouts.app`).
- [ ] Card login memakai surface default + `card shadow-sm` (tanpa `border-0`).
- [ ] Toggle theme (light/dark/auto) tetap tersedia.