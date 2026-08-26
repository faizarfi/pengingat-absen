# Setup Pesan Formal - BPS Karanganyar

## Deskripsi
Sistem telah diperbarui untuk mengirim pesan absensi dengan format yang lebih formal dan sopan, menggunakan BPS Karanganyar sebagai pengirim resmi.

## Fitur Baru
- ✅ Sapaan pembuka resmi: "Dengan hormat"
- ✅ Nama pengirim resmi: BPS Karanganyar
- ✅ Template pesan yang profesional
- ✅ Pantun yang sopan dan relevan dengan konteks kerja formal
- ✅ Pesan terstruktur dengan format surat resmi

## Konfigurasi yang Diperlukan

Tambahkan settings berikut ke database `settings` table:

### SQL Insert
```sql
INSERT INTO settings (key, value, created_at, updated_at) VALUES
('organization_name', 'BPS Karanganyar', NOW(), NOW()),
('template_pre_checkin_formal', 'Dengan hormat,

Ibu/Bapak {name},

Kami dari {organization_name} ingin mengingatkan bahwa dalam 30 menit lagi adalah waktu untuk absensi pagi. Mohon untuk segera melakukan absensi tepat waktu.

{pantun}

{kata}', NOW(), NOW()),
('template_checkin_formal', 'Dengan hormat,

Ibu/Bapak {name},

Sudah saatnya waktu absensi pagi tiba. Kami dari {organization_name} mengingatkan agar segera melakukan absensi. Terima kasih atas perhatian dan kepatuhan Anda.

{pantun}

{kata}', NOW(), NOW()),
('template_checkout_formal', 'Dengan hormat,

Ibu/Bapak {name},

Sudah saatnya waktu absensi pulang. Kami dari {organization_name} mengingatkan agar segera melakukan absensi pulang. Terima kasih atas dedikasi kerja keras Anda hari ini.

{pantun}

{kata}', NOW(), NOW());
```

### Menggunakan Laravel Tinker
```php
php artisan tinker

Setting::create(['key' => 'organization_name', 'value' => 'BPS Karanganyar']);

Setting::create(['key' => 'closing_word', 'value' => 'Hormat kami, BPS Karanganyar']);
```

## Format Pesan Hasil

### Pre-Check-in (30 menit sebelum jam absen)
```
Dengan hormat,

Ibu/Bapak [Nama Karyawan],

Kami dari BPS Karanganyar ingin mengingatkan bahwa dalam 30 menit lagi adalah waktu untuk absensi pagi. 
Mohon untuk segera melakukan absensi tepat waktu.

[Pantun Profesional]

Hormat kami, BPS Karanganyar
```

### Check-in (Jam absen pagi)
```
Dengan hormat,

Ibu/Bapak [Nama Karyawan],

Sudah saatnya waktu absensi pagi tiba. Kami dari BPS Karanganyar mengingatkan agar segera melakukan absensi. 
Terima kasih atas perhatian dan kepatuhan Anda.

[Pantun Profesional]

Hormat kami, BPS Karanganyar
```

### Check-out (Jam absen pulang)
```
Dengan hormat,

Ibu/Bapak [Nama Karyawan],

Sudah saatnya waktu absensi pulang. Kami dari BPS Karanganyar mengingatkan agar segera melakukan absensi pulang. 
Terima kasih atas dedikasi kerja keras Anda hari ini.

[Pantun Profesional]

Hormat kami, BPS Karanganyar
```

## Pantun Profesional yang Digunakan

Sistem akan memilih secara acak salah satu dari pantun-pantun berikut yang lebih sopan dan relevan:

1. Bekerja dengan sungguh-sungguh, Akan membawa hasil yang maksimal.
2. Tepat waktu adalah prioritas, Menjaga kepercayaan organisasi.
3. Absen yang teratur dan rapi, Tanda profesional dalam bekerja.
4. Kehadiran penuh setiap hari, Bentuk komitmen terhadap tugas.
5. Tanggung jawab adalah kunci, Untuk mencapai kesuksesan bersama.
6. Disiplin dalam setiap langkah, Menciptakan lingkungan kerja yang baik.
7. Kehadiran membentuk prestasi, Bersama membangun organisasi maju.
8. Kerja sama yang solid dan kuat, Mewujudkan visi institusi kami.

## Customization

Anda dapat mengubah template pesan melalui admin panel atau dengan mengupdate settings di database:

- `organization_name` - Nama organisasi (default: "BPS Karanganyar")
- `closing_word` - Kata penutup (default: "Hormat kami, BPS Karanganyar")
- `template_pre_checkin_formal` - Template pre-check-in
- `template_checkin_formal` - Template check-in
- `template_checkout_formal` - Template check-out

## Placeholder yang Tersedia

Dalam template, gunakan placeholder berikut:
- `{name}` - Nama karyawan
- `{organization_name}` - Nama organisasi
- `{kata}` - Kata penutup
- `{pantun}` - Pantun profesional

## Testing

Untuk menguji pesan formal, gunakan command:
```bash
php artisan wa:send-reminders
```

Atau test via Tinker:
```php
php artisan tinker

$emp = Employee::first();
$cmd = new \App\Console\Commands\SendWaReminder();
echo $cmd->buildMessage($emp->name, 'checkin');
```

## Catatan

- Pesan sudah diformat dengan struktur surat formal Indonesia
- Penggunaan "Ibu/Bapak" menunjukkan rasa hormat
- Pantun dipilih dari kumpulan pantun profesional yang relevan dengan konteks kerja
- Organisasi BPS Karanganyar ditampilkan sebagai pengirim resmi pesan
