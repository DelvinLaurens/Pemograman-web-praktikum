# Integrasi Backend Donasi

Fitur donasi sekarang dipisah menjadi dua bagian:

- UI: `donasi.php` dan `verif.php`
- Logic backend: `Component/donation_service.php`

Backend cukup `require` koneksi database dan service:

```php
require_once __DIR__ . "/Component/db_conn.php";
require_once __DIR__ . "/Component/donation_service.php";
```

## Database

Jalankan SQL ini setelah import database utama:

```sql
Database/donasi_verif_update.sql
```

Kolom penting di tabel `donasi`:

- `status`: `PENDING`, `VERIFIED`, `REJECTED`, `EXPIRED`
- `waktu_kadaluarsa`: batas waktu pembayaran
- `bukti_transfer`: path file bukti transfer, boleh `NULL` saat donasi baru dibuat

## Membuat Donasi Pending

```php
$result = createPendingDonation(
    $conn,
    $id_donatur,
    $id_kampanye,
    $nominal,
    $metode_pembayaran,
    $pesan_dukungan
);

if ($result['success']) {
    $id_donasi = $result['id_donasi'];
}
```

Output:

```php
[
    'success' => true,
    'errors' => [],
    'id_donasi' => 12,
]
```

## Mengambil Data Verifikasi

```php
$donasi = getDonationVerificationData($conn, $id_donasi, $id_donatur);
```

Fungsi ini sudah join ke:

- `donasi`
- `kampanye`
- `penyelenggara`
- `donatur`

## Menandai Expired

```php
$expired = syncDonationExpiry($conn, $donasi);
```

Jika sudah lewat `waktu_kadaluarsa`, belum ada bukti transfer, dan status masih `PENDING`, status akan diubah menjadi `EXPIRED`.

## Upload Bukti Transfer

```php
$upload = uploadDonationProof($conn, $donasi, $_FILES['bukti']);

if ($upload['success']) {
    $path_bukti = $upload['path'];
}
```

Validasi bawaan:

- JPG, PNG, PDF
- maksimal 2MB
- tidak bisa upload jika donasi sudah `EXPIRED`
- tidak bisa upload ulang jika bukti sudah ada

## Metode Pembayaran

Daftar metode ada di:

```php
Component/donasi_helper.php
```

Fungsi:

```php
paymentMethods();
getPaymentMethod($metode);
normalizePaymentMethod($metode);
```

Untuk integrasi payment gateway asli nanti, bagian yang paling perlu diganti adalah data di `paymentMethods()` dan flow setelah `createPendingDonation()`.
