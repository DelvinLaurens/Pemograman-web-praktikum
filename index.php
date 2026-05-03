<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DemiSesama</title>
    <!-- fav icon -->
    <link rel="icon" type="image/png" href="Asset/tangan2 tnpa bg.png">
    <link rel="stylesheet" href="CSS/home.css">
    <link rel="stylesheet" href="CSS/global.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container nav-wrapper">
            <div class="logo">
                <img src="Asset/tangan2 tnpa bg.png" alt="logo website" class="logo-website">
                <span>DemiSesama.</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Beranda</a></li>
                    <li><a href="index.php#kampanye">Donasi</a></li>
                    <li><a href="galang-dana.php">Galang Dana</a></li>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="tampilan-utama">
            <div class="hero-overlay"></div>
            <div class="container text-center hero-content">
                <h1>Ayo wujudkan Harapan, Demi sesama.</h1>
                <p class="hero-desc">Demi Sesama hadir sebagai jembatan kebaikan. Di sini, setiap donasi menjadi harapan bagi mereka yang membutuhkan. Mari bersama-sama membantu, berbagi, dan menciptakan dunia yang lebih peduli.</p>

                <div class="search-bar">
                    <form>
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Cari judul kampanye...">

                        <select name="Kategori" id="Kategori">
                            <option value="">Semua Kategori</option>
                            <option value="kesehatan">Kesehatan</option>
                            <option value="pendidikan">Pendidikan</option>
                            <option value="bencana_alam">Bencana Alam</option>
                            <option value="sosial">Kehidupan Sosial</option>
                            <option value="pembangunan">Pembangunan</option>
                            <option value="lingkungan">Lingkungan</option>
                        </select>

                        <select name="lokasi" id="lokasi">
                            <option value="">Semua Lokasi</option>
                            <option value="sumatera">Sumatera</option>
                            <option value="jawa">Jawa</option>
                            <option value="kalimantan">Kalimantan</option>
                            <option value="sulawesi">Sulawesi</option>
                            <option value="bali">Bali</option>
                            <option value="maluku">Maluku</option>
                            <option value="papua">Papua</option>
                            <option value="ntt">NTT</option>
                        </select>

                        <select name="range" id="range">
                            <option value="0-1000000">&lt; 1 Juta</option>
                            <option value="1000000-5000000">1 - 5 Juta</option>
                            <option value="5000000-10000000">5 - 10 Juta</option>
                            <option value="10000000+">&gt; 10 Juta</option>
                        </select>

                        <button type="button" class="btn-search">Cari</button>
                    </form>
                </div>
            </div>    
        </section>

        <section class="kampanye" id="kampanye">
            <div class="container">
                <h2 class="section-title">Kampanye Mendesak</h2>
                <div class="kampanye-grid">
                    
                    <!-- Penggalangan dana 1 -->
                    <div class="card">
                        <img src="Asset/banjir melawi.jpg" alt="Kampanye 1" class="card-img">
                        <div class="card-content">
                            <h3>Banjir Melawi</h3>
                            <p class="deskripsi">Telah Terjadi banjir di Melawi tahun 2024</p>
                            <p class="penyelenggara">Oleh: Sdr. Roihan Jaidid</p>
                            <div class="info-dana">
                                <p class="target">Target: <span>Rp 100.000.000</span></p>
                                <p class="terkumpul">Terkumpul: <span>Rp 12.674.000</span></p>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: 12%;"></div> 
                            </div>
                            <p class="waktu">Sisa Waktu: 13 hari</p>
                            <a href="detail.php" class="btn-detail">Lihat Detail</a>
                        </div>
                    </div>

                    <!-- Penggalangan dana 2 -->
                    <div class="card">
                        <img src="Asset/gempa manado.jpg" alt="Kampanye 2" class="card-img">
                        <div class="card-content">
                            <h3>Gempa Manado</h3>
                            <p class="deskripsi">Gempa di Manado yang mengakibatkan banyaknya rumah</p>
                            <p class="penyelenggara">Oleh: Sdri. Mirel</p>
                            <div class="info-dana">
                                <p class="target">Target: <span>Rp 180.000.000</span></p>
                                <p class="terkumpul">Terkumpul: <span>Rp 24.534.000</span></p>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: 13%;"></div> 
                            </div>
                            <p class="waktu">Sisa Waktu: 50 hari</p>
                            <a href="detail.php" class="btn-detail">Lihat Detail</a>
                        </div>
                    </div>

                    <!-- Penggalangan dana 3 -->
                    <div class="card">
                        <img src="Asset/puting beliung kupang.jpeg" alt="Kampanye 3" class="card-img">
                        <div class="card-content">
                            <h3>Puting Beliung Kupang</h3>
                            <p class="deskripsi">Bencana puting beliung di Kupang pada Januari 2024</p>
                            <p class="penyelenggara">Oleh: Sdr. Jeremy Waraney</p>
                            <div class="info-dana">
                                <p class="target">Target: <span>Rp 45.000.000</span></p>
                                <p class="terkumpul">Terkumpul: <span>Rp 690.000</span></p>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: 2%;"></div> 
                            </div>
                            <p class="waktu">Sisa Waktu: 29 hari</p>
                            <a href="detail.php" class="btn-detail">Lihat Detail</a>
                        </div>
                    </div>
                    <!-- Penggalangan dana 4 -->
                    <div class="card">
                        <img src="Asset/banjir bali.jpg" alt="Kampanye 4" class="card-img">
                        <div class="card-content">
                            <h3>Banjir Bali</h3>
                            <p class="deskripsi">Banjir di bali pada 10 September 2025</p>
                            <p class="penyelenggara">Oleh: Sdr. Richard Van Nistelroy</p>
                            <div class="info-dana">
                                <p class="target">Target: <span>Rp 20.000.000</span></p>
                                <p class="terkumpul">Terkumpul: <span>Rp 700.000</span></p>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: 5%;"></div> 
                            </div>
                            <p class="waktu">Sisa Waktu: 45 hari</p>
                            <a href="detail.php" class="btn-detail">Lihat Detail</a>
                        </div>
                    </div>
                    <!-- Penggalangan dana 5 -->
                    <div class="card">
                        <img src="Asset/bantuan penddikan desa.jpg" alt="Kampanye 5" class="card-img">
                        <div class="card-content">
                            <h3>Bantuan Pendidikan Anak Desa</h3>
                            <p class="deskripsi">Bantu Anak Desa Mendapatkan Pendidikan Layak</p>
                            <p class="penyelenggara">Oleh: PT. Kenangan Bersama</p>
                            <div class="info-dana">
                                <p class="target">Target: <span>Rp 150.000.000</span></p>
                                <p class="terkumpul">Terkumpul: <span>Rp 122.050.000</span></p>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: 80%;"></div> 
                            </div>
                            <p class="waktu">Sisa Waktu: 29 hari</p>
                            <a href="detail.php" class="btn-detail">Lihat Detail</a>
                        </div>
                    </div>
                    <!-- Penggalangan dana 6 -->
                    <div class="card">
                        <img src="Asset/reboisasi hutan.jpg" alt="Kampanye 6" class="card-img">
                        <div class="card-content">
                            <h3>Reboisasi Hutan Gundul</h3>
                            <p class="deskripsi">Tanam 10.000 pohon untuk menghidupi hutan kembali</p>
                            <p class="penyelenggara">Oleh: Sdr. Kevin Puskas Viera</p>
                            <div class="info-dana">
                                <p class="target">Target: <span>Rp 50.000.000</span></p>
                                <p class="terkumpul">Terkumpul: <span>Rp 35.000.000</span></p>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: 75%;"></div> 
                            </div>
                            <p class="waktu">Sisa Waktu: 10 hari</p>
                            <a href="detail.php" class="btn-detail">Lihat Detail</a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>
    
    <footer>
        <div class="container text-center">
            <p>&copy; 2026 DemiSesama</p>
        </div>
    </footer>

</body>
</html>