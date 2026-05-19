<?php
require_once __DIR__ . "/path_helper.php";

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cardKampanye')) {
function cardKampanye($data) {

    $target_rupiah = "Rp " . number_format($data['target_dana'], 0, ',', '.');
    $terkumpul_rupiah = "Rp " . number_format($data['dana_terkumpul'], 0, ',', '.');

    $persentase = 0;
    if ($data['target_dana'] > 0) {
        $persentase = ($data['dana_terkumpul'] / $data['target_dana']) * 100;
    }

    $persentase_bulat = min(round($persentase), 100); 

    $tanggal_sekarang = new DateTime();
    $batas_waktu = new DateTime($data['batas_waktu']);
    $sisa_waktu = $tanggal_sekarang->diff($batas_waktu);
    $sisa_hari = $sisa_waktu->days;

    if ($batas_waktu < $tanggal_sekarang) {
        $sisa_hari = 0; 
    }

    $is_db_completed = (isset($data['status']) && $data['status'] === 'completed');
    $is_target_reached = ((float)$data['dana_terkumpul'] >= (float)$data['target_dana']);
    $is_expired = ($batas_waktu < $tanggal_sekarang);

    if ($is_db_completed || $is_target_reached || $is_expired) {
        $waktu_html = '<span style="color: #dc3545; font-weight: bold;">Selesai (Penggalangan Ditutup)</span>';
        $btn_class = 'btn-detail btn-completed';
        $btn_style = 'style="background-color: #6c757d; color: white;"';
        $btn_text = 'Lihat Histori Kampanye';
    } else {
        $waktu_html = "Sisa Waktu: {$sisa_hari} hari";
        $btn_class = 'btn-detail';
        $btn_style = '';
        $btn_text = 'Lihat Detail';
    }

    $id_kampanye = e($data['id_kampanye']);
    $judul_kampanye = e($data['judul_kampanye']);
    $deskripsi = e($data['deskripsi']);
    $nama_penyelenggara = e($data['nama_penyelenggara']);
    $gambar_poster = e(asset_url($data['gambar_poster']));
    $detail_url = e(url_for("pages/detail.php?id={$id_kampanye}"));

    echo <<<HTML
    <div class="card muncul-saat-scroll">
        <img src="{$gambar_poster}" alt="{$judul_kampanye}" class="card-img">
        <div class="card-content">
            <h3>{$judul_kampanye}</h3>
            <p class="deskripsi">{$deskripsi}</p>
            <p class="penyelenggara">Oleh: {$nama_penyelenggara}</p>
            <div class="info-dana">
                <p class="target">Target: <span>{$target_rupiah}</span></p>
                <p class="terkumpul">Terkumpul: <span>{$terkumpul_rupiah}</span></p>
            </div>
            <div class="progress-bar">
                <div class="progress" style="width: {$persentase_bulat}%;"></div> 
            </div>
            <p class="waktu">{$waktu_html}</p>
            <a href="{$detail_url}" class="{$btn_class}" {$btn_style}>{$btn_text}</a>
        </div>
    </div>
    HTML;
    }
}
?>
