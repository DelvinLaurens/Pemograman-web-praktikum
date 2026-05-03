<?php
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

    echo <<<HTML
    <div class="card">
        <img src="{$data['gambar_poster']}" alt="{$data['judul_kampanye']}" class="card-img">
        <div class="card-content">
            <h3>{$data['judul_kampanye']}</h3>
            <p class="deskripsi">{$data['deskripsi']}</p>
            <p class="penyelenggara">Oleh: {$data['nama_penyelenggara']}</p>
            <div class="info-dana">
                <p class="target">Target: <span>{$target_rupiah}</span></p>
                <p class="terkumpul">Terkumpul: <span>{$terkumpul_rupiah}</span></p>
            </div>
            <div class="progress-bar">
                <div class="progress" style="width: {$persentase_bulat}%;"></div> 
            </div>
            <p class="waktu">Sisa Waktu: {$sisa_hari} hari</p>
            <a href="detail.php?id={$data['id_kampanye']}" class="btn-detail">Lihat Detail</a>
        </div>
    </div>
    HTML;
    }
?>