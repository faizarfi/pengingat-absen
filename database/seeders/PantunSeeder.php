<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PantunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Avoid inserting duplicates when seeder is run multiple times
        if (DB::table('pantuns')->count() > 0) {
            return;
        }

        $masuk = [
            'Burung elang terbang ke awan,\nSinggah sebentar di pohon kelapa.\nSelamat pagi wahai kawan,\nSambut hari dengan ceria.',
            'Makan mangga manis rasanya,\nBeli lima di pasar raya.\nJam delapan tiba waktunya,\nMari bekerja penuh daya.',
            'Jalan-jalan ke Karanganyar,\nSinggah sebentar membeli jamu.\nPagi datang semangat mekar,\nKarya terbaik kini menunggumu.',
            'Bunga mawar bunga melati,\nHarum semerbak di pagi hari.\nMulai tugas mantapkan hati,\nSemoga rezeki datang menghampiri.',
            'Buah naga manis di lidah,\nDipetik satu dari dahan.\nPagi cerah terasa indah,\nMulailah kerja dengan senyuman.',
            'Naik kuda melompat pagar,\nPagar tinggi berwarna biru.\nMinum kopi badan pun segar,\nSiap menyambut tantangan baru.',
            'Membeli dawet di pinggir jalan,\nRasa manis santannya gurih.\nSambut pagi penuh kebaikan,\nKerja semangat pantang merintih.',
            'Pergi ke sawah menanam padi,\nPadi tumbuh hijau berkembang.\nSelamat bekerja di pagi ini,\nSemoga harimu makin cemerlang.',
            'Pohon jati tumbuh di hutan,\nKayunya kuat dibuat lemari.\nMari mulai setiap tugas harian,\nDengan integritas setinggi langit.',
            'Burung merpati terbang tinggi,\nHinggap sebentar di atas pagar.\nTatap laptop dengan teliti,\nFokus bekerja pikiran segar.',
            'Pergi ke pasar membeli kain,\nKain merah untuk selendang.\nTinggalkan dulu hal yang lain,\nFokus tugas yang membentang.',
            'Pohon beringin amatlah rindang,\nTempat berteduh saat hujan.\nSenyum manis ikut berkembang,\nMulai kerja demi masa depan.',
            'Ada cangkul di atas tanah,\nDipakai petani membelah batu.\nKerja pagi membawa berkah,\nTarget tercapai tepat waktu.',
            'Membeli gulai di warung kelontong,\nMakan bersama dengan teman.\nSemangat kerja tak boleh kosong,\nJadikan karya penuh kebanggaan.',
            'Ke Candi Cetho jalan menanjak,\nPemandangan indah berhias awan.\nMari melangkah secara bijak,\nSambut pagi penuh kesenangan.',
            'Pergi ke kolam memancing ikan,\nDapat gurame sama patin.\nRasa malas ayo lupakan,\nNiatkan kerja secara batin.',
            'Buah kelapa diminum segar,\nPetik sendiri di pagi hari.\nMentari pagi bersinar terang,\nPicu semangat di dalam diri.',
            'Mengayuh sepeda di pagi hari,\nAngin berhembus terasa dingin.\nSegarkan pikiran rapikan diri,\nCapai semua yang kamu ingin.',
            'Minum kopi campur gula,\nNikmat rasanya di waktu pagi.\nMulai hari tanpa cela,\nUkir prestasi hari ini.',
            'Menulis surat pakai tinta,\nTinta hitam di atas kertas.\nAyo mulai bekerja nyata,\nTunjukkan kinerja yang berkelas.',
            'Buah rambutan manis sekali,\nDijual orang di tepi jalan.\nSelamat datang di kantor lagi,\nMari melangkah penuh kepastian.',
            'Beli keripik rasa keju,\nDimakan saat duduk santai.\nSetiap target mari dipacu,\nAgar sukses dapat digapai.',
            'Terbang rendah burung gelatik,\nSinggah sebentar di dahan bambu.\nAwali pagi dengan baik,\nSenyum ramah di wajahmu.',
            'Naik bus menuju kota,\nDuduk santai dekat jendela.\nKerja giat harapan kita,\nSemoga lelah jadi pahala.',
            'Beli apel di toko buah,\nWarna merah sungguh menggoda.\nPagi ini berjalan indah,\nMari bekerja wahai kawan muda.',
            'Ke Karangpandan makan soto,\nRasanya enak bikin nagih.\nMulai tugas jangan ragu-ragu,\nHasil maksimal bakal diraih.',
            'Ada kucing tidur di meja,\nTidur sebentar lalu melompat.\nSaatnya kita mulai kerja,\nSemoga jalan selalu cerat.',
            'Menanam bunga di dalam pot,\nMenyiramnya setiap pagi.\nBekerja keras tak merasa repot,\nDemi masa depan yang tinggi.',
            'Pergi ke pantai melihat ombak,\nOmbak menggulung ke tepi pasir.\nRencana kerja mari dibedah,\nAgar rezeki terus mengalir.',
            'Burung kutilang bernyanyi merdu,\nDi atas pohon buah mangga.\nSelamat datang rekan terbaikku,\nMari berkarya bersama-sama.',
            'Beli es teh di warung bucik,\nDiminum pagi bikin segar.\nSemua ide mari diracik,\nBikin inovasi makin mekar.',
            'Membeli nanas di pasar pagi,\nManis rasanya warna kuning.\nMari menyapa jam kerja lagi,\nFokus tinggi tak bikin pusing.',
            'Langit pagi berwarna cerah,\nIndah dipandang dari taman.\nBPS Karanganyar pantang menyerah,\nSelalu kompak antar rekan.',
            'Memetik buah di kebun raya,\nDapat pisang sama pepaya.\nSetiap pegawai penuh daya,\nKantor kita makin jaya.',
            'Minum teh hangat di cangkir kaca,\nDitemani roti rasa cokelat.\nMari data dan dokumen dibaca,\nKerja teliti dan juga cepat.',
            'Pergi ke toko membeli buku,\nBuku sejarah cerita lama.\nSelamat pagi sahabatku,\nMari berjuang bersama-sama.',
            'Menangkap ikan di tepi kali,\nDapat banyak dibawa pulang.\nSemangat pagi membakar hati,\nTugas berat terasa ringan.',
            'Pohon pisang daunnya lebar,\nTumbuh subur di dekat kali.\nMasuk kantor dengan sabar,\nJalani hari penuh arti.',
            'Daun salam daun jeruk,\nDimasak bersama daging sapi.\nKantuk pagi ayo diusir,\nSambut tugas dengan berani.',
            'Berlayar jauh menuju pulau,\nMelihat pemandangan laut biru.\nMulai kerja tanpa galau,\nSambut pagi bersukaria selalu.',
            'Makan ketan ditambah ragi,\nSimpan sebentar di dalam wadah.\nAyo fokus di pagi hari,\nAgar hasil berbuah indah.',
            'Anak kecil bermain layangan,\nLayangan terbang tertiup angin.\nTata meja dan ruangan,\nSiap kerja yang diingin.',
            'Beli nasi goreng di pinggir jalan,\nPorsinya banyak kenyang terasa.\nMari mulai pengabdian,\nKinerja hebat luar biasa.',
            'Kain batik dari Solo,\nMotif indah dipandang mata.\nJangan lemas jangan galau,\nMari berkarya untuk bangsa.',
            'Burung hantu berbunyi malam,\nTidur siang di pohon jati.\nSambut pagi penuh salam,\nKerja ikhlas dari hati.',
            'Ke Tawangmangu membeli stoberi,\nRasanya manis agak sedikit asam.\nSemangat baru di pagi ini,\nKerja tenang tanpa ancam.',
            'Mengayuh sampan ke seberang,\nAir sungai tenang mengalir.\nWaktu masuk telah terang,\nSemangat kerja tak pernah berakhir.',
            'Beli jeruk dua kilo,\nDimakan bersama adik tercinta.\nSambut tugas halo-halo,\nMari capai semua cita.',
            'Menanam jagung di tanah luas,\nDisiram air setiap pagi.\nBuat pelayanan semakin memuas,\nTingkatkan mutu setiap hari.',
            'Pohon kelapa tinggi menjulang,\nTertiup angin ke kiri kanan.\nSebelum sore kita pulang,\nSelesaikan tugas dengan sigap dan aman.',
        ];

        $pulang = [
            'Burung elang terbang ke awan,\nSinggah sebentar di pohon kelapa.\nTerima kasih wahai kawan,\nKerja kerasmu sangat berharga.',
            'Makan mangga manis rasanya,\nBeli lima di pasar raya.\nJam empat sudah tiba waktunya,\nSaatnya kita pulang ceria.',
            'Jalan-jalan ke Karanganyar,\nSinggah sebentar membeli jamu.\nTetap semangat pantang menyerah,\nSelamat istirahat untuk dirimu.',
            'Bunga mawar bunga melati,\nHarum semerbak di sore hari.\nTugas selesai lega di hati,\nSaatnya pulang menyegarkan diri.',
            'Buah naga manis di lidah,\nDipetik satu dari dahan.\nHari ini terasa indah,\nTerima kasih atas dedikasi harian.',
            'Naik kuda melompat pagar,\nPagar tinggi berwarna biru.\nBila napas terasa segar,\nEsok siap menyambut tugas baru.',
            'Membeli dawet di pinggir jalan,\nRasa manis santannya gurih.\nUntuk semua hasil kerja harian,\nKami ucapkan terima kasih.',
            'Pergi ke sawah menanam padi,\nPadi tumbuh hijau berkembang.\nWaktu pulang sudah menjadi,\nHati senang berjalan pulang.',
            'Pohon jati tumbuh di hutan,\nKayunya kuat dibuat lemari.\nTerima kasih atas pengabdian,\nSemoga lelahmu jadi rezeki.',
            'Burung merpati terbang tinggi,\nHinggap sebentar di atas pagar.\nRehat sejenak tenangkan hati,\nBesok kembali lebih segar.',
            'Pergi ke pasar membeli kain,\nKain merah untuk selendang.\nMatikan komputer dan hal lain,\nKini waktunya untuk pulang.',
            'Pohon beringin amatlah rindang,\nTempat berteduh saat hujan.\nLayar monitor boleh dipejam,\nMari mengemas semua barang.',
            'Ada cangkul di atas tanah,\nDipakai petani membelah batu.\nKerja hari ini penuh berkah,\nSelamat istirahat di rumahmu.',
            'Membeli gulai di warung kelontong,\nMakan bersama dengan teman.\nSore hari makin melesat cepat,\nWaktunya kembali ke kediaman.',
            'Ke Candi Cetho jalan menanjak,\nPemandangan indah berhias awan.\nTerima kasih telah bijak,\nMenuntaskan tugas dengan cekatan.',
            'Pergi ke kolam memancing ikan,\nDapat gurame sama patin.\nRasa lelah ayo lupakan,\nNikmati sore bersama keluarga tercinta.',
            'Buah kelapa diminum segar,\nPetik sendiri di pagi hari.\nPengabdianmu sungguh mekar,\nBikin bangga kantor ini.',
            'Mengayuh sepeda di sore hari,\nAngin berhembus terasa dingin.\nTugas hari ini telah usai,\nPulanglah cepat yang kamu ingin.',
            'Minum kopi campur gula,\nNikmat rasanya di waktu petang.\nSiapkan barang tanpa cela,\nSambut esok dengan riang.',
            'Menulis surat pakai tinta,\nTinta hitam di atas kertas.\nTerima kasih atas kerja nyata,\nSemangatmu sungguh luar biasa.',
            'Buah rambutan manis sekali,\nDijual orang di tepi jalan.\nLangkah kaki terasa ringan,\nMenyambut waktu untuk pulang.',
            'Beli keripik rasa keju,\nDimakan saat duduk santai.\nTarget kerja sudah terpacu,\nKini saatnya beristirahat damai.',
            'Terbang rendah burung gelatik,\nSinggah sebentar di dahan bambu.\nSetiap tugas diselesai baik,\nSenyum terkembang di bibirmu.',
            'Naik bus menuju kota,\nDuduk santai dekat jendela.\nTerima kasih atas kerja kita,\nHasil maksimal jadi pahala.',
            'Beli apel di toko buah,\nWarna merah sungguh menggoda.\nHari ini berjalan indah,\nIstirahatlah wahai kawan muda.',
            'Ke Karangpandan makan soto,\nRasanya enak bikin nagih.\nApresiasi setinggi langit,\nUntukmu kami bilang terima kasih.',
            'Ada kucing tidur di meja,\nTidur nyenyak sangat tenang.\nSudah saatnya selesai kerja,\nWaktu santai akhirnya datang.',
            'Menanam bunga di dalam pot,\nMenyiramnya setiap pagi.\nBekerja keras tak merasa repot,\nDedikasimu tiada tandingi.',
            'Pergi ke pantai melihat ombak,\nOmbak menggulung ke tepi pasir.\nKinerja hebat tak perlu diperdebat,\nTerima kasih tanpa akhir.',
            'Burung kutilang bernyanyi merdu,\nDi atas pohon buah mangga.\nWaktu pulang sudah menunggu,\nSampai jumpa besok ya!',
            'Beli es teh di warung bucik,\nMinumnya pakai sedotan biru.\nKerja hari ini sangat baik,\nBesok kita buat prestasi baru.',
            'Membeli nanas di pasar pagi,\nManis rasanya bikin segar.\nJam empat telah tiba lagi,\nMari pulang menuju sanggar.',
            'Langit sore berwarna jingga,\nIndah dipandang dari taman.\nKerja kerasmu amat berharga,\nTerima kasih wahai rekan.',
            'Memetik buah di kebun raya,\nDapat pisang sama pepaya.\nBPS Karanganyar merasa bangga,\nPunya pegawai penuh daya.',
            'Minum teh hangat di cangkir kaca,\nDitemani roti rasa cokelat.\nBila pengingat jam pulang membaca,\nRapikan berkas dengan cepat.',
            'Pergi ke toko membeli buku,\nBuku sejarah cerita lama.\nTerima kasih sahabatku,\nKerja kerasmu tak sia-sia.',
            'Menangkap ikan di tepi kali,\nDapat banyak dibawa pulang.\nDedikasimu jempolan sekali,\nHati pimpinan terasa tenang.',
            'Pohon pisang daunnya lebar,\nTumbuh subur di dekat kali.\nPulang kerja dengan sabar,\nKeluarga menunggu di rumah nanti.',
            'Daun salam daun jeruk,\nDimasak bersama daging sapi.\nWalau badan terasa remuk,\nSenyum puas tetap di hati.',
            'Berlayar jauh menuju pulau,\nMelihat pemandangan laut biru.\nKerja hari ini tanpa galau,\nMari pulang menyambut rindu.',
            'Makan ketan ditambah ragi,\nSimpan sebentar di dalam wadah.\nSelesai sudah tugas hari ini,\nSemoga hasilnya berbuah indah.',
            'Anak kecil bermain layangan,\nLayangan terbang tertiup angin.\nRapikan meja dan ruangan,\nPulang ke rumah yang diingin.',
            'Beli nasi goreng di pinggir jalan,\nPorsinya banyak kenyang terasa.\nTerima kasih atas kebaikan,\nKinerjamu luar biasa.',
            'Kain batik dari Solo,\nMotif indah dipandang mata.\nJangan bingung jangan galau,\nJam empat pulang sudah di mata.',
            'Burung hantu berbunyi malam,\nTidur siang di pohon jati.\nSalam hormat serta salam,\nTerima kasih dari dalam hati.',
            'Ke Tawangmangu membeli stoberi,\nRasanya manis agak sedikit asam.\nTerima kasih untuk hari ini,\nSemoga malammu penuh tentram.',
            'Mengayuh sampan ke seberang,\nAir sungai tenang mengalir.\nWaktu kerja usai sekarang,\nRasa lelah pun ikut berakhir.',
            'Beli jeruk dua kilo,\nDimakan bersama adik tercinta.\nKerja bagus halo-halo,\nMari pulang ke rumah kita.',
            'Menanam jagung di tanah luas,\nDisiram air setiap sore.\nHasil kerjamu sangat memuas,\nBikin semua bilang hura-hore.',
            'Ke Karanganyar membeli jamu,\nJamu beras kencur segar rasanya.\nTerima kasih atas kerja kerasmu,\nSelamat pulang dan salam bahagia!',
        ];

        $inserts = [];

        foreach ($masuk as $p) {
            $inserts[] = [
                'type' => 'masuk',
                // convert literal \n sequences to actual newlines before inserting
                'text' => str_replace('\\n', PHP_EOL, $p),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($pulang as $p) {
            $inserts[] = [
                'type' => 'pulang',
                'text' => str_replace('\\n', PHP_EOL, $p),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('pantuns')->insert($inserts);
    }
}
