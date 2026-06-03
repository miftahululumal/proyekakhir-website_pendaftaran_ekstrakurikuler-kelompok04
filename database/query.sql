CREATE DATABASE ekstrakurikuler;
USE ekstrakurikuler;

CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE siswa (
    nisn CHAR(10) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    alamat TEXT,
    jenis_kelamin ENUM('Laki-laki','Perempuan'),
    kelas ENUM('10','11','12'),
    CONSTRAINT chk_panjang_nisn CHECK (LENGTH(nisn) = 10)
);

CREATE TABLE guru (
    nip CHAR(18) PRIMARY KEY,
    nama_guru VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE ekskul (
    id_ekskul INT AUTO_INCREMENT PRIMARY KEY,
    nama_ekskul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    foto VARCHAR(225),
    visi TEXT,
    misi TEXT,
    program_kerja TEXT,
    prestasi TEXT,
    nip_pembimbing CHAR(18), 
    id_jadwal INT,
    CONSTRAINT fk_ekskul_pembimbing
    FOREIGN KEY (nip_pembimbing) REFERENCES guru(nip)
    ON UPDATE CASCADE
    ON DELETE SET NULL 
);

CREATE TABLE jadwal_ekskul (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_ekskul INT NOT NULL,
    hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    CONSTRAINT fk_jadwal_ekskul
    FOREIGN KEY (id_ekskul) REFERENCES ekskul(id_ekskul)
    ON DELETE CASCADE
);

CREATE TABLE pendaftaran (
    id_pendaftaran INT AUTO_INCREMENT PRIMARY KEY,
    nisn CHAR(10),
    id_ekskul INT,
    id_jadwal INT NOT NULL,
    tanggal_daftar DATE,
    status ENUM('Menunggu','Diterima','Ditolak','Dikeluarkan','Sudah Max'),
    no_hp VARCHAR(20),
    foto_diri VARCHAR(255),
    alasan_ditolak TEXT,
    alasan_dikeluarkan TEXT,

    CONSTRAINT fk_pendaftaran_siswa
    FOREIGN KEY (nisn)
    REFERENCES siswa(nisn)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    CONSTRAINT fk_pendaftaran_ekskul
    FOREIGN KEY (id_ekskul)
    REFERENCES ekskul(id_ekskul)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

INSERT INTO admin(username,password)
VALUES
(
'admin',
'admin123'
);


INSERT INTO siswa (nisn, nama, email, password, alamat, jenis_kelamin, kelas) VALUES
('2600010001','Ahmad Rizki','ahmad10@gmail.com','12345678','Surabaya','Laki-laki', '10'),
('2600010002','Budi Santoso','budi10@gmail.com','12345678','Sidoarjo','Laki-laki', '10'),
('2600010003','Cahyo Nugroho','cahyo10@gmail.com','12345678','Gresik','Laki-laki', '10'),
('2600010004','Dimas Saputra','dimas10@gmail.com','12345678','Mojokerto','Laki-laki', '10'),
('2600010005','Eko Prasetyo','eko10@gmail.com','12345678','Lamongan','Laki-laki', '10'),
('2600010006','Farhan Akbar','farhan10@gmail.com','12345678','Surabaya','Laki-laki', '10'),
('2600010007','Galih Ramadhan','galih10@gmail.com','12345678','Sidoarjo','Laki-laki', '10'),
('2600010008','Hendra Wijaya','hendra10@gmail.com','12345678','Gresik','Laki-laki', '10'),
('2600010009','Indra Kurniawan','indra10@gmail.com','12345678','Mojokerto','Laki-laki', '10'),
('2600010010','Joko Susilo','joko10@gmail.com','12345678','Lamongan','Laki-laki', '10'),
('2600010011','Ayu Lestari','ayu10@gmail.com','12345678','Surabaya','Perempuan', '10'),
('2600010012','Bella Putri','bella10@gmail.com','12345678','Sidoarjo','Perempuan', '10'),
('2600010013','Citra Dewi','citra10@gmail.com','12345678','Gresik','Perempuan', '10'),
('2600010014','Dewi Anggraini','dewi10@gmail.com','12345678','Mojokerto','Perempuan', '10'),
('2600010015','Erika Safitri','erika10@gmail.com','12345678','Lamongan','Perempuan', '10'),
('2500011001','Agus Salim','agus11@gmail.com','12345678','Surabaya','Laki-laki', '11'),
('2500011002','Bayu Pratama','bayu11@gmail.com','12345678','Sidoarjo','Laki-laki', '11'),
('2500011003','Candra Saputra','candra11@gmail.com','12345678','Gresik','Laki-laki', '11'),
('2500011004','Deni Firmansyah','deni11@gmail.com','12345678','Mojokerto','Laki-laki', '11'),
('2500011005','Erwin Setiawan','erwin11@gmail.com','12345678','Lamongan','Laki-laki', '11'),
('2500011006','Fajar Maulana','fajar11@gmail.com','12345678','Surabaya','Laki-laki', '11'),
('2500011007','Guntur Prakoso','guntur11@gmail.com','12345678','Sidoarjo','Laki-laki', '11'),
('2500011008','Hari Nugraha','hari11@gmail.com','12345678','Gresik','Laki-laki', '11'),
('2500011009','Iqbal Ramadhan','iqbal11@gmail.com','12345678','Mojokerto','Laki-laki', '11'),
('2500011010','Jefri Kurnia','jefri11@gmail.com','12345678','Lamongan','Laki-laki', '11'),
('2500011011','Anisa Putri','anisa11@gmail.com','12345678','Surabaya','Perempuan', '11'),
('2500011012','Bunga Maharani','bunga11@gmail.com','12345678','Sidoarjo','Perempuan', '11'),
('2500011013','Clara Oktavia','clara11@gmail.com','12345678','Gresik','Perempuan', '11'),
('2500011014','Desi Natalia','desi11@gmail.com','12345678','Mojokerto','Perempuan', '11'),
('2500011015','Elsa Permata','elsa11@gmail.com','12345678','Lamongan','Perempuan', '11'),
('2400012001','Andi Saputra','andi12@gmail.com','12345678','Surabaya','Laki-laki', '12'),
('2400012002','Bagas Nugroho','bagas12@gmail.com','12345678','Sidoarjo','Laki-laki', '12'),
('2400012003','Ciko Ramadhan','ciko12@gmail.com','12345678','Gresik','Laki-laki', '12'),
('2400012004','Daffa Maulana','daffa12@gmail.com','12345678','Mojokerto','Laki-laki', '12'),
('2400012005','Erlangga Putra','erlangga12@gmail.com','12345678','Lamongan','Laki-laki', '12'),
('2400012006','Fikri Hidayat','fikri12@gmail.com','12345678','Surabaya','Laki-laki', '12'),
('2400012007','Gilang Prasetyo','gilang12@gmail.com','12345678','Sidoarjo','Laki-laki', '12'),
('2400012008','Haikal Akbar','haikal12@gmail.com','12345678','Gresik','Laki-laki', '12'),
('2400012009','Ilham Saputra','ilham12@gmail.com','12345678','Mojokerto','Laki-laki', '12'),
('2400012010','Jovan Kurnia','jovan12@gmail.com','12345678','Lamongan','Laki-laki', '12'),
('2400012011','Aisyah Zahra','aisyah12@gmail.com','12345678','Surabaya','Perempuan', '12'),
('2400012012','Bella Safira','bella12@gmail.com','12345678','Sidoarjo','Perempuan', '12'),
('2400012013','Cindy Maharani','cindy12@gmail.com','12345678','Gresik','Perempuan', '12'),
('2400012014','Dinda Permata','dinda12@gmail.com','12345678','Mojokerto','Perempuan', '12'),
('2400012015','Evi Natalia','evi12@gmail.com','12345678','Lamongan','Perempuan', '12');

INSERT INTO guru (nip, nama_guru, no_hp, email) VALUES
('197504122003121002', 'Budi Utomo, S.Pd.', '081234567890', 'budi.utomo@school.sch.id'),
('198008232006042001', 'Siti Aminah, M.Pd.', '081345678901', 'siti.aminah@school.sch.id'),
('198511052010011003', 'Hendra Wijaya, S.Kom.', '081456789012', 'hendra.wijaya@school.sch.id'),
('197802282005012002', 'Rina Kartika, S.Si.', '081567890123', 'rina.kartika@school.sch.id'),
('199006152015031001', 'Ahmad Fauzi, S.P d.', '081678901234', 'ahmad.fauzi@school.sch.id'),
('198207192008012004', 'Dewi Lestari, M.Si.', '081789012345', 'dewi.lestari@school.sch.id'),
('197309011999031002', 'Bambang Suprianto, M.T.', '081890123456', 'bambang.s@school.sch.id'),
('198812252014022001', 'Mega Utami, S.Pd.', '081901234567', 'mega.utami@school.sch.id'),
('198403142009041002', 'Eko Prasetyo, S.E.', '082123456789', 'eko.prasetyo@school.sch.id'),
('199301102019032003', 'Fitriani, S.Hum.', '082234567890', 'fitriani@school.sch.id'),
('197705302003121001', 'Agus Setiawan, S.Pd.', '082345678901', 'agus.setiawan@school.sch.id'),
('198609122010012005', 'Sri Wahyuni, M.Pd.', '082456789012', 'sri.wahyuni@school.sch.id'),
('199104052018011002', 'Rizky Ramadhan, S.Sn.', '082567890123', 'rizky.r@school.sch.id'),
('197910222006042003', 'Nani Wijaya, S.Pd.', '082678901234', 'nani.wijaya@school.sch.id'),
('198308182009021001', 'Dedi Kurniawan, M.Kom.', '082789012345', 'dedi.kurniawan@school.sch.id');

INSERT INTO ekskul 
(id_ekskul, nama_ekskul, deskripsi, foto, visi, misi, program_kerja, prestasi, nip_pembimbing)
VALUES
(1, 'Futsal', 'Ekstrakurikuler Futsal merupakan wadah bagi siswa yang memiliki minat dan bakat di bidang olahraga futsal. Kegiatan ini bertujuan untuk melatih kemampuan teknik bermain, strategi pertandingan, kerja sama tim, serta meningkatkan kebugaran jasmani siswa.', 'futsal.jpeg', 'Menjadi ekstrakurikuler futsal yang unggul, sportif, disiplin, dan mampu mencetak pemain berbakat di tingkat sekolah maupun daerah.', '- Melatih kemampuan teknik dan strategi bermain futsal.\n- Menanamkan nilai sportivitas dan kerja sama tim.\n- Mengembangkan mental juara dan disiplin siswa.\n- Mengikuti berbagai kompetisi futsal antar sekolah.', '- Latihan rutin mingguan.\n- Sparing dengan sekolah lain.\n- Seleksi tim inti futsal.\n- Mengadakan turnamen internal sekolah.\n- Mengikuti kompetisi tingkat kota dan provinsi.', '- Juara 1 Turnamen Futsal Antar SMA Kota.\n- Best Player Competition 2025.\n- Juara 2 Liga Pelajar Daerah.', '199006152015031001'),
(2, 'Basket', 'Ekstrakurikuler Basket adalah kegiatan yang diperuntukkan bagi siswa yang menyukai olahraga bola basket dan ingin mengembangkan kemampuan bermain secara profesional maupun rekreasional.', 'basket.jpg', 'Mewujudkan tim basket yang aktif, solid, dan berprestasi dengan menjunjung tinggi sportivitas.', '- Mengembangkan kemampuan teknik dasar basket.\n- Membentuk karakter disiplin dan tanggung jawab.\n- Menjalin kekompakan dan kerja sama tim.\n- Berpartisipasi dalam kompetisi basket pelajar.', '- Latihan fisik dan teknik rutin.\n- Friendly match antar sekolah.\n- Pelatihan strategi permainan.\n- Seleksi atlet basket sekolah.\n- Mengikuti event basket tahunan.', '- Juara 1 Basket Competition Tingkat Kabupaten.\n- Most Valuable Player (MVP) Turnamen Pelajar.\n- Juara 3 Liga Basket SMA.', '198008232006042001'),

(3, 'Pramuka', 'Ekstrakurikuler Pramuka merupakan kegiatan pendidikan nonformal yang bertujuan membentuk karakter siswa agar menjadi pribadi yang mandiri, disiplin, bertanggung jawab, serta memiliki jiwa kepemimpinan.', 'pramuka.jpg', 'Membentuk generasi muda yang mandiri, disiplin, peduli lingkungan, dan berjiwa kepemimpinan.', '- Menanamkan nilai kedisiplinan dan tanggung jawab.\n- Melatih keterampilan kepramukaan.\n- Meningkatkan jiwa kepemimpinan dan kerja sama.\n- Mengembangkan kepedulian sosial dan lingkungan.', '- Perkemahan rutin.\n- Latihan baris-berbaris.\n- Bakti sosial.\n- Hiking dan kegiatan alam.\n- Pelatihan kepemimpinan.', '- Juara Umum Jambore Kota.\n- Juara 1 Lomba Pionering.\n- Juara 2 Lomba Semaphore.', '197504122003121002'),

(4, 'PMR', 'Ekstrakurikuler PMR adalah kegiatan yang bergerak di bidang kesehatan dan kemaanusiaan.', 'pmr.jpg', 'Menjadi ekstrakurikuler yang aktif dalam bidang kesehatan, kemanusiaan, dan kepedulian sosial.', '- Memberikan pengetahuan dasar pertolongan pertama.\n- Menanamkan rasa peduli terhadap sesama.\n- Meningkatkan keterampilan kesehatan remaja.\n- Mendukung kegiatan sosial dan kemanusiaan.', '- Pelatihan P3K.\n- Donor darah.\n- Penyuluhan kesehatan.\n- Bakti sosial.\n- Simulasi penanganan darurat.', '- Juara 1 Lomba Pertolongan Pertama.\n- Tim PMR Teraktif Tingkat Kota.\n- Juara 2 Cerdas Cermat Kesehatan.', '198609122010012005'),

(5, 'Paskibra', 'Ekstrakurikuler Paskibra merupakan kegiatan yang berfokus pada pelatihan baris-berbaris, kedisiplinan, kepemimpinan, dan nasionalisme.', 'paskibra.jpg', 'Membentuk anggota paskibra yang disiplin, tangguh, dan berjiwa nasionalisme tinggi.', '- Melatih keterampilan baris-berbaris.\n- Menanamkan rasa cinta tanah air.\n- Membentuk karakter disiplin dan tanggung jawab.\n- Menjadi petugas upacara terbaik sekolah.', '- Latihan PBB rutin.\n- Pelatihan kepemimpinan.\n- Seleksi pasukan inti.\n- Pengibaran bendera pada hari nasional.\n- Mengikuti lomba PBB.', '- Juara 1 Lomba PBB Tingkat Kabupaten.\n- Pasukan Pengibar Bendera Terbaik.\n- Juara Favorit Lomba Formasi.', '198308182009021001'),

(6, 'Tari', 'Ekstrakurikuler Tari adalah wadah bagi siswa yang memiliki minat dan bakat dalam seni tari, baik tari tradisional maupun modern.', 'tari.jpg', 'Menjadi wadah pengembangan seni tari yang kreatif, inovatif, dan berprestasi.', '- Mengembangkan bakat dan kreativitas siswa.\n- Melestarikan budaya tari tradisional.\n- Meningkatkan kemampuan seni pertunjukan.\n- Mengikuti festival dan lomba tari.', '- Latihan tari tradisional dan modern.\n- Pentas seni sekolah.\n- Workshop seni tari.\n- Kolaborasi pertunjukan budaya.\n- Mengikuti festival tari.', '- Juara 1 Festival Tari Tradisional.\n- Penampilan Terbaik Pentas Seni Kota.\n- Juara 2 Lomba Tari Kreasi.', '197802282005012002'),

(7, 'Musik', 'Ekstrakurikuler Musik merupakan kegiatan yang memberikan kesempatan kepada siswa untuk mengembangkan bakat dan kreativitas di bidang musik.', 'musik.jpg', 'Menjadi ekstrakurikuler musik yang kreatif, inovatif, dan mampu menghasilkan karya berkualitas.', '- Mengembangkan bakat musik siswa.\n- Melatih kemampuan vokal dan alat musik.\n- Mendorong kreativitas dalam berkarya. \n- Mengikuti festival dan kompetisi musik.', '- Latihan band rutin.\n- Workshop musik.\n- Pembuatan cover lagu.\n- Pentas seni sekolah.\n- Mengikuti lomba musik.', '- Juara 1 Band Competition Sekolah.\n- Best Performance Festival Musik.\n- Juara 2 Lomba Akustik Pelajar.', '199104052018011002'),

(8, 'English Club', 'English Club adalah ekstrakurikuler yang bertujuan meningkatkan kemampuan bahasa Inggris siswa secara aktif dan komunikatif.', 'englishclub.jpg', 'Menjadi wadah pengembangan kemampuan bahasa Inggris yang aktif, kreatif, dan komunikatif.', '- Meningkatkan kemampuan speaking, listening, reading, dan writing.\n- Membiasakan penggunaan bahasa Inggris dalam aktivitas.\n- Menumbuhkan rasa percaya diri dalam berkomunikasi.\n- Mengikuti lomba bahasa Inggris.', '- English Conversation.\n- Debate dan speech practice.\n- Vocabulary challenge.\n- English Day.\n- Mengikuti lomba debat dan pidato.', '- Juara 1 English Debate Competition.\n- Best Speaker Tingkat Kota.\n- Juara 2 Story Telling Contest.', '198207192008012004'),

(9, 'Bulu Tangkis', 'Ekstrakurikuler Bulu Tangkis merupakan kegiatan olahraga yang bertujuan mengembangkan kemampuan siswa dalam permainan bulu tangkis, baik secara teknik maupun strategi. Kegiatan ini melatih ketangkasan, kecepatan, fokus, dan sportivitas siswa melalui latihan rutin dan pertandingan.', 'bulutangkis.jpg', 'Menjadi ekstrakurikuler bulu tangkis yang aktif, disiplin, dan berprestasi di tingkat sekolah maupun daerah.', '- Melatih kemampuan teknik dasar bulu tangkis.\n- Menanamkan sikap disiplin dan sportivitas.\n- Mengembangkan bakat dan potensi siswa.\n- Mengikuti kompetisi bulu tangkis antar pelajar.', '- Latihan teknik dan fisik rutin.\n- Sparing antar anggota.\n- Seleksi atlet bulu tangkis sekolah.\n- Mengikuti turnamen pelajar.\n- Mengadakan pertandingan internal.', '- Juara 1 Turnamen Bulu Tangkis Antar SMA.\n- Best Player Competition Tingkat Kota.\n- Juara 2 Kejuaraan Pelajar Kabupaten.', '198403142009041002'),

(10, 'Voli', 'Ekstrakurikuler Voli adalah kegiatan olahraga yang bertujuan mengembangkan kemampuan siswa dalam permainan bola voli. Dalam kegiatan ini siswa dilatih teknik dasar, strategi permainan, kerja sama tim, dan kekuatan fisik.', 'voli.jpg', 'Mewujudkan tim voli yang solid, sportif, dan berprestasi.', '- Melatih teknik dasar dan strategi permainan voli.\n- Menanamkan nilai kerja sama dan sportivitas.\n- Mengembangkan kemampuan fisik dan mental siswa.\n- Mengikuti kompetisi voli tingkat pelajar.', '- Latihan rutin teknik dan fisik.\n- Friendly match antar sekolah.\n- Seleksi tim inti voli.\n- Mengikuti turnamen voli pelajar.\n- Mengadakan kompetisi internal sekolah.', '- Juara 1 Turnamen Voli Antar Sekolah.\n- Juara 2 Liga Voli Pelajar.\n- Tim Voli Terfavorit Tingkat Kabupaten.', '199301102019032003'),

(11, 'Seni Bela Diri', 'Ekstrakurikuler Seni Bela Diri merupakan kegiatan yang bertujuan melatih kemampuan bela diri, kebugaran fisik, disiplin, dan pengendalian diri siswa.', 'beladiri.jpg', 'Menjadi ekstrakurikuler bela diri yang disiplin, tangguh, dan berprestasi.', '- Melatih teknik dasar bela diri secara benar.\n- Menanamkan sikap disiplin dan tanggung jawab.\n- Mengembangkan mental percaya diri dan sportivitas.\n- Mengikuti kejuaraan bela diri tingkat pelajar.', '- Latihan teknik bela diri rutin.\n- Pelatihan fisik dan ketahanan tubuh.\n- Ujian kenaikan tingkat.\n- Demonstrasi bela diri.\n- Mengikuti kompetisi dan kejuaraan.', '- Juara 1 Kejuaraan Bela Diri Tingkat Kota.\n- Atlet Terbaik Kejuaraan Pelajar.\n- Juara 2 Turnamen Antar Sekolah.', '197309011999031002');

INSERT INTO jadwal_ekskul
(id_ekskul,hari,jam_mulai,jam_selesai)
VALUES
(1,'Senin','15:30:00','17:00:00'),
(1,'Kamis','15:30:00','17:00:00'),
(1,'Sabtu','13:00:00','15:00:00'),
(2,'Selasa','15:30:00','17:00:00'),
(2,'Jumat','15:30:00','17:00:00'),
(2,'Sabtu','15:00:00','17:00:00'),
(3,'Rabu','15:00:00','17:00:00'),
(3,'Sabtu','13:00:00','15:00:00'),
(3,'Minggu','08:00:00','10:00:00'),
(4,'Senin','13:30:00','15:00:00'),
(4,'Rabu','13:30:00','15:00:00'),
(4,'Jumat','13:30:00','15:00:00'),
(5,'Selasa','13:30:00','15:00:00'),
(5,'Kamis','13:30:00','15:00:00'),
(5,'Sabtu','15:00:00','17:00:00'),
(6,'Kamis','13:30:00','15:00:00'),
(6,'Sabtu','13:00:00','15:00:00'),
(6,'Minggu','13:00:00','15:00:00'),
(7,'Jumat','13:30:00','15:00:00'),
(7,'Sabtu','13:00:00','15:00:00'),
(7,'Minggu','15:00:00','17:00:00'),
(8,'Rabu','15:00:00','17:00:00'),
(8,'Jumat','15:00:00','17:00:00'),
(8,'Sabtu','13:00:00','15:00:00'),
(9,'Rabu','15:30:00','17:00:00'),
(9,'Jumat','15:30:00','17:00:00'),
(9,'Minggu','08:00:00','10:00:00'),
(10,'Rabu','13:30:00','15:00:00'),
(10,'Jumat','15:30:00','17:00:00'),
(10,'Sabtu','15:00:00','17:00:00'),
(11,'Selasa','15:30:00','17:00:00'),
(11,'Kamis','15:30:00','17:00:00'),
(11,'Sabtu','13:00:00','15:00:00');

