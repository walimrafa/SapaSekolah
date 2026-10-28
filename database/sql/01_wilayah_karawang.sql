SET NAMES utf8mb4;

-- =============================================
-- 1. INSERT PROVINCE: Jawa Barat
-- =============================================
INSERT INTO provinces (name) VALUES ('Jawa Barat')
ON DUPLICATE KEY UPDATE name = name;

-- =============================================
-- 2. INSERT REGENCY: Kabupaten Karawang
-- =============================================
INSERT INTO regencies (province_id, name)
SELECT id, 'Kabupaten Karawang' FROM provinces WHERE name = 'Jawa Barat' LIMIT 1;

-- =============================================
-- 3. INSERT ALL DISTRICTS (Kecamatan) Kabupaten Karawang
-- =============================================
SET @regency_id = (SELECT id FROM regencies WHERE name = 'Kabupaten Karawang' LIMIT 1);

INSERT INTO districts (regency_id, name) VALUES
(@regency_id, 'Pangkalan'),
(@regency_id, 'Tegalwaru'),
(@regency_id, 'Ciampel'),
(@regency_id, 'Telukjambe Timur'),
(@regency_id, 'Telukjambe Barat'),
(@regency_id, 'Klari'),
(@regency_id, 'Cikampek'),
(@regency_id, 'Purwasari'),
(@regency_id, 'Tirtamulya'),
(@regency_id, 'Jatisari'),
(@regency_id, 'Banyusari'),
(@regency_id, 'Kotabaru'),
(@regency_id, 'Cilamaya Wetan'),
(@regency_id, 'Cilamaya Kulon'),
(@regency_id, 'Lemahabang'),
(@regency_id, 'Telagasari'),
(@regency_id, 'Majalaya'),
(@regency_id, 'Karawang Timur'),
(@regency_id, 'Karawang Barat'),
(@regency_id, 'Rawamerta'),
(@regency_id, 'Tempuran'),
(@regency_id, 'Kutawaluya'),
(@regency_id, 'Rengasdengklok'),
(@regency_id, 'Jayakerta'),
(@regency_id, 'Pedes'),
(@regency_id, 'Cilebar'),
(@regency_id, 'Cibuaya'),
(@regency_id, 'Tirtajaya'),
(@regency_id, 'Batujaya'),
(@regency_id, 'Pakisjaya');

-- =============================================
-- 4. INSERT VILLAGES (Desa/Kelurahan) per Kecamatan
-- =============================================

-- Kecamatan Ciampel
SET @district_id = (SELECT id FROM districts WHERE name = 'Ciampel' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Kutapohaci'),
(@district_id, 'Kutanegara'),
(@district_id, 'Mulyasejati'),
(@district_id, 'Ciampel'),
(@district_id, 'Mulyamekar'),
(@district_id, 'Mulyasari'),
(@district_id, 'Kutamaneuh');

-- Kecamatan Telukjambe Timur
SET @district_id = (SELECT id FROM districts WHERE name = 'Telukjambe Timur' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Pinayungan'),
(@district_id, 'Purwadana'),
(@district_id, 'Puseurjaya'),
(@district_id, 'Sukaharja'),
(@district_id, 'Wadas'),
(@district_id, 'Sirnabaya'),
(@district_id, 'Sukaluyu'),
(@district_id, 'Telukjambe'),
(@district_id, 'Sukamakmur'),
(@district_id, 'Margamulya'),
(@district_id, 'Sukasejati');

-- Kecamatan Telukjambe Barat
SET @district_id = (SELECT id FROM districts WHERE name = 'Telukjambe Barat' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Wanasari'),
(@district_id, 'Margamulya'),
(@district_id, 'Karangligar'),
(@district_id, 'Wanakerta'),
(@district_id, 'Margakaya'),
(@district_id, 'Wanareja'),
(@district_id, 'Parungmulya'),
(@district_id, 'Lemahduhur');

-- Kecamatan Klari
SET @district_id = (SELECT id FROM districts WHERE name = 'Klari' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Duren'),
(@district_id, 'Karanganyar'),
(@district_id, 'Belendung'),
(@district_id, 'Pancawati'),
(@district_id, 'Cimahi'),
(@district_id, 'Kiarapayung'),
(@district_id, 'Klari'),
(@district_id, 'Curug'),
(@district_id, 'Gintungkerta'),
(@district_id, 'Sumurkondang'),
(@district_id, 'Kapasari'),
(@district_id, 'Anggadita');

-- Kecamatan Lemahabang
SET @district_id = (SELECT id FROM districts WHERE name = 'Lemahabang' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Kedawung'),
(@district_id, 'Lemahmukti'),
(@district_id, 'Lemahabang'),
(@district_id, 'Kertasari'),
(@district_id, 'Sindangmulya'),
(@district_id, 'Cikarang'),
(@district_id, 'Lemahabang Kulon'),
(@district_id, 'Lemahabang Wetan');

-- Kecamatan Telagasari
SET @district_id = (SELECT id FROM districts WHERE name = 'Telagasari' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Pasirtalaga'),
(@district_id, 'Cariumulya'),
(@district_id, 'Pasirmukti'),
(@district_id, 'Talagamulya'),
(@district_id, 'Ciwulan'),
(@district_id, 'Talagasari'),
(@district_id, 'Kalisari'),
(@district_id, 'Pasirkamuning'),
(@district_id, 'Linggarsari'),
(@district_id, 'Kalijaya');

-- Kecamatan Majalaya
SET @district_id = (SELECT id FROM districts WHERE name = 'Majalaya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Lemahmulya'),
(@district_id, 'Bengle'),
(@district_id, 'Majalaya'),
(@district_id, 'Pasirjengkol'),
(@district_id, 'Lemahsubur'),
(@district_id, 'Lemahkarya'),
(@district_id, 'Kutawaringin'),
(@district_id, 'Ciptasari');

-- Kecamatan Karawang Timur
SET @district_id = (SELECT id FROM districts WHERE name = 'Karawang Timur' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Palawad'),
(@district_id, 'Palumbonsari'),
(@district_id, 'Karawang Wetan'),
(@district_id, 'Adiarsa Timur'),
(@district_id, 'Kondangjaya'),
(@district_id, 'Warungbambu'),
(@district_id, 'Plawad'),
(@district_id, 'Margasari');

-- Kecamatan Karawang Barat
SET @district_id = (SELECT id FROM districts WHERE name = 'Karawang Barat' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Nagasari'),
(@district_id, 'Karawang Kulon'),
(@district_id, 'Tanjungpura'),
(@district_id, 'Karangpawitan'),
(@district_id, 'Tunggakjati'),
(@district_id, 'Tanjungmekar'),
(@district_id, 'Adiarsa Barat'),
(@district_id, 'Mekarjati'),
(@district_id, 'Karawang');

-- Kecamatan Pangkalan
SET @district_id = (SELECT id FROM districts WHERE name = 'Pangkalan' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Pangkalan'),
(@district_id, 'Cintawargi'),
(@district_id, 'Wargasetra'),
(@district_id, 'Tanjung'),
(@district_id, 'Kertajaya'),
(@district_id, 'Mulangsari');

-- Kecamatan Tegalwaru
SET @district_id = (SELECT id FROM districts WHERE name = 'Tegalwaru' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Tegalwaru'),
(@district_id, 'Cintalanggeng'),
(@district_id, 'Wargaluyu'),
(@district_id, 'Cintaasih'),
(@district_id, 'Kutalanggeng'),
(@district_id, 'Kutakarya'),
(@district_id, 'Cintamanik');

-- Kecamatan Cikampek
SET @district_id = (SELECT id FROM districts WHERE name = 'Cikampek' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Cikampek Selatan'),
(@district_id, 'Cikampek Timur'),
(@district_id, 'Cikampek Barat'),
(@district_id, 'Cikampek Kota'),
(@district_id, 'Dawuan Tengah'),
(@district_id, 'Dawuan Timur'),
(@district_id, 'Dawuan Barat'),
(@district_id, 'Kamojing'),
(@district_id, 'Kalihurip');

-- Kecamatan Purwasari
SET @district_id = (SELECT id FROM districts WHERE name = 'Purwasari' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Purwasari'),
(@district_id, 'Tamelang'),
(@district_id, 'Cengklong'),
(@district_id, 'Darawolong'),
(@district_id, 'Mekarsari'),
(@district_id, 'Mekarjaya');

-- Kecamatan Tirtamulya
SET @district_id = (SELECT id FROM districts WHERE name = 'Tirtamulya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Tirtamulya'),
(@district_id, 'Kampungbaru'),
(@district_id, 'Cikarang'),
(@district_id, 'Sarimulya'),
(@district_id, 'Kertamulya');

-- Kecamatan Jatisari
SET @district_id = (SELECT id FROM districts WHERE name = 'Jatisari' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Jatisari'),
(@district_id, 'Jatimulya'),
(@district_id, 'Jatiragas'),
(@district_id, 'Jatireja'),
(@district_id, 'Balongsari'),
(@district_id, 'Barugbug'),
(@district_id, 'Cikampek Pusaka'),
(@district_id, 'Kertajaya');

-- Kecamatan Banyusari
SET @district_id = (SELECT id FROM districts WHERE name = 'Banyusari' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Banyusari'),
(@district_id, 'Gempol'),
(@district_id, 'Tanjungjaya'),
(@district_id, 'Jayamulya'),
(@district_id, 'Sukamakmur'),
(@district_id, 'Gempol Kolot');

-- Kecamatan Kotabaru
SET @district_id = (SELECT id FROM districts WHERE name = 'Kotabaru' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Kotabaru'),
(@district_id, 'Pangulah Selatan'),
(@district_id, 'Pangulah Utara'),
(@district_id, 'Ciparagejaya'),
(@district_id, 'Jomin'),
(@district_id, 'Jomin Barat'),
(@district_id, 'Jomin Timur'),
(@district_id, 'Cikampek Utara');

-- Kecamatan Cilamaya Wetan
SET @district_id = (SELECT id FROM districts WHERE name = 'Cilamaya Wetan' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Cilamaya'),
(@district_id, 'Rawagempol Kulon'),
(@district_id, 'Rawagempol Wetan'),
(@district_id, 'Muara'),
(@district_id, 'Sukatani'),
(@district_id, 'Sumurgede');

-- Kecamatan Cilamaya Kulon
SET @district_id = (SELECT id FROM districts WHERE name = 'Cilamaya Kulon' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Cilamaya'),
(@district_id, 'Pasirjaya'),
(@district_id, 'Muktijaya'),
(@district_id, 'Sukatani'),
(@district_id, 'Tegalurung'),
(@district_id, 'Kiara');

-- Kecamatan Rawamerta
SET @district_id = (SELECT id FROM districts WHERE name = 'Rawamerta' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Rawamerta'),
(@district_id, 'Panyingkiran'),
(@district_id, 'Balongsari'),
(@district_id, 'Sukamerta'),
(@district_id, 'Sukaraja'),
(@district_id, 'Mekarjaya');

-- Kecamatan Tempuran
SET @district_id = (SELECT id FROM districts WHERE name = 'Tempuran' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Tempuran'),
(@district_id, 'Lemahmakmur'),
(@district_id, 'Lemahkarya'),
(@district_id, 'Pancakarya'),
(@district_id, 'Pagadungan'),
(@district_id, 'Dayeuhluhur');

-- Kecamatan Kutawaluya
SET @district_id = (SELECT id FROM districts WHERE name = 'Kutawaluya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Kutawaluya'),
(@district_id, 'Sindangkarya'),
(@district_id, 'Sindangsari'),
(@district_id, 'Mulyajaya'),
(@district_id, 'Mulyasari'),
(@district_id, 'Kutamandiri');

-- Kecamatan Rengasdengklok
SET @district_id = (SELECT id FROM districts WHERE name = 'Rengasdengklok' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Rengasdengklok Selatan'),
(@district_id, 'Rengasdengklok Utara'),
(@district_id, 'Kalangsari'),
(@district_id, 'Kalangsurya'),
(@district_id, 'Dewisari'),
(@district_id, 'Kertasari'),
(@district_id, 'Dukuhkarya');

-- Kecamatan Jayakerta
SET @district_id = (SELECT id FROM districts WHERE name = 'Jayakerta' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Jayakerta'),
(@district_id, 'Kemiri'),
(@district_id, 'Medangasem'),
(@district_id, 'Ciptamarga'),
(@district_id, 'Telarsih'),
(@district_id, 'Kampungsawah');

-- Kecamatan Pedes
SET @district_id = (SELECT id FROM districts WHERE name = 'Pedes' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Pedes'),
(@district_id, 'Srikamulyan'),
(@district_id, 'Payungsari'),
(@district_id, 'Sungaibuntu'),
(@district_id, 'Kertamulya'),
(@district_id, 'Rangdumulya');

-- Kecamatan Cilebar
SET @district_id = (SELECT id FROM districts WHERE name = 'Cilebar' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Cilebar'),
(@district_id, 'Ciptamargi'),
(@district_id, 'Rawasari'),
(@district_id, 'Pulosari'),
(@district_id, 'Kertarahayu');

-- Kecamatan Cibuaya
SET @district_id = (SELECT id FROM districts WHERE name = 'Cibuaya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Cibuaya'),
(@district_id, 'Jayamakmur'),
(@district_id, 'Kedungjeruk'),
(@district_id, 'Sukajaya'),
(@district_id, 'Pasirukem'),
(@district_id, 'Sedari');

-- Kecamatan Tirtajaya
SET @district_id = (SELECT id FROM districts WHERE name = 'Tirtajaya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Tirtajaya'),
(@district_id, 'Gempolkurung'),
(@district_id, 'Kertamukti'),
(@district_id, 'Landungsari'),
(@district_id, 'Tambaksari'),
(@district_id, 'Tambaksumur');

-- Kecamatan Batujaya
SET @district_id = (SELECT id FROM districts WHERE name = 'Batujaya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Batujaya'),
(@district_id, 'Segaran'),
(@district_id, 'Telukbuyung'),
(@district_id, 'Segarjaya'),
(@district_id, 'Batumiskin'),
(@district_id, 'Rengasbandung');

-- Kecamatan Pakisjaya
SET @district_id = (SELECT id FROM districts WHERE name = 'Pakisjaya' AND regency_id = @regency_id LIMIT 1);
INSERT INTO villages (district_id, name) VALUES
(@district_id, 'Pakisjaya'),
(@district_id, 'Tanjungjaya'),
(@district_id, 'Telukjaya'),
(@district_id, 'Solokan'),
(@district_id, 'Talagajaya');
