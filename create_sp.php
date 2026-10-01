<?php

use Illuminate\Support\Facades\DB;

$procedure = "
DROP PROCEDURE IF EXISTS `sp_sync_register_lembur`;

CREATE PROCEDURE `sp_sync_register_lembur`(IN p_tanggal_mulai DATE, IN p_tanggal_selesai DATE)
BEGIN
    DECLARE v_id_pegawai INT;
    DECLARE v_nama_pegawai VARCHAR(255);
    DECLARE v_tanggal DATE;
    DECLARE v_jam_kehadiran TIME;
    DECLARE v_jam_kepulangan TIME;
    DECLARE v_hari VARCHAR(50);
    DECLARE v_jenis_spl VARCHAR(50);
    DECLARE v_durasi_lembur DECIMAL(8,2);
    DECLARE v_uang_makan DECIMAL(15,2);
    
    DECLARE done INT DEFAULT FALSE;
    
    DECLARE cur_absensi CURSOR FOR 
        SELECT 
            p.ID_PEGAWAI, 
            p.NM_PEGAWAI,
            a.tanggal, 
            a.jam_kehadiran, 
            a.jam_kepulangan
        FROM t_data_absensi a
        JOIN M_PEGAWAI p ON p.ID_PEGAWAI_MESIN = a.id_pegawai
        WHERE a.tanggal BETWEEN p_tanggal_mulai AND p_tanggal_selesai
          AND a.jam_kehadiran IS NOT NULL 
          AND a.jam_kepulangan IS NOT NULL
          AND a.jam_kepulangan > '17:00:00'
          AND WEEKDAY(a.tanggal) < 5;
          
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur_absensi;
    
    read_loop: LOOP
        FETCH cur_absensi INTO v_id_pegawai, v_nama_pegawai, v_tanggal, v_jam_kehadiran, v_jam_kepulangan;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET v_jenis_spl = '';
        SET v_durasi_lembur = 0;
        SET v_uang_makan = 0;
        
        SET v_hari = ELT(WEEKDAY(v_tanggal) + 1, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu');
        
        IF v_jam_kepulangan > '17:00:00' AND v_jam_kepulangan <= '18:00:00' THEN
            SET v_jenis_spl = 'Lembur A';
            SET v_durasi_lembur = FLOOR(TIME_TO_SEC(TIMEDIFF(v_jam_kepulangan, '17:00:00')) / 3600);
            
        ELSEIF v_jam_kepulangan > '18:00:00' THEN
            SET v_jenis_spl = 'Lembur B';
            SET v_durasi_lembur = FLOOR(TIME_TO_SEC(TIMEDIFF(v_jam_kepulangan, '17:00:00')) / 3600);
        END IF;
        
        IF v_jam_kepulangan >= '19:00:00' THEN
            SET v_uang_makan = 15000;
        END IF;
        
        IF v_jenis_spl != '' THEN
            IF EXISTS (SELECT 1 FROM t_register_lembur WHERE id_pegawai = v_id_pegawai AND tanggal = v_tanggal) THEN
                UPDATE t_register_lembur 
                SET jenis_spl = v_jenis_spl,
                    jam_mulai = '17:00:00',
                    jam_selesai = v_jam_kepulangan,
                    durasi_lembur = v_durasi_lembur,
                    uang_makan = v_uang_makan,
                    updated_at = NOW()
                WHERE id_pegawai = v_id_pegawai AND tanggal = v_tanggal;
            ELSE
                INSERT INTO t_register_lembur (
                    id_pegawai, nama_pegawai, tanggal, hari, jam_mulai, jam_selesai, jenis_spl, catatan, durasi_lembur, uang_makan, created_at, updated_at
                ) VALUES (
                    v_id_pegawai, v_nama_pegawai, v_tanggal, v_hari, '17:00:00', v_jam_kepulangan, v_jenis_spl, CONCAT('Auto Sync ', v_jenis_spl), v_durasi_lembur, v_uang_makan, NOW(), NOW()
                );
            END IF;
        END IF;
        
    END LOOP;
    
    CLOSE cur_absensi;
    
END;
";

DB::unprepared($procedure);
echo "Stored procedure sp_sync_register_lembur created successfully.\n";
