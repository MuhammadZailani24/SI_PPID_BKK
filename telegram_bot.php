<?php
// =================================================================
// SCRIPT BOT TELEGRAM PPID - BERSIH 100% BEBAS ERROR
// =================================================================

set_time_limit(0);
error_reporting(E_ALL);

require_once 'includes/koneksi.php';

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ppid_bkk_banjarmasin";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi PDO Gagal: " . $e->getMessage());
}

$bot_token = '8317540330:AAExILR-ug8ua8SL-GrVFAhbEBI48Ipylj8';
$admin_chat_id = '8966610923';

// Hapus webhook otomatis
file_get_contents("https://api.telegram.org/bot" . $bot_token . "/deleteWebhook");

echo "=============================================\n";
echo " BOT TELEGRAM PPID BKK BANJARMASIN AKTIF!\n";
echo " Sistem Reply-Upload File telah berjalan...\n";
echo "=============================================\n\n";

$last_update_id = 0;

// =================================================================
// FUNGSI-FUNGSI PENDUKUNG
// =================================================================

function teleRequest($method, $data) {
    global $bot_token;
    $url = "https://api.telegram.org/bot" . $bot_token . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function teleSendDocument($chat_id, $file_path, $caption = "") {
    global $bot_token;
    $url = "https://api.telegram.org/bot" . $bot_token . "/sendDocument";
    $cfile = new CURLFile(realpath($file_path));
    $data = array(
        'chat_id' => $chat_id,
        'document' => $cfile,
        'caption' => $caption
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function kirimEmailPPID($to, $subject, $htmlMessage, $filePath = "", $fileName = "") {
    $boundary = md5(time());
    $headers = "From: PPID BKK Banjarmasin <admin@ppid-bkkbanjarmasin.com>\r\n";

    if (!empty($filePath) AND file_exists($filePath)) {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlMessage));
        $file_content = chunk_split(base64_encode(file_get_contents($filePath)));
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: application/pdf; name=\"$fileName\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $file_content;
        $body .= "--$boundary--";
    } else {
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $body = $htmlMessage;
    }
    return @mail($to, $subject, $body, $headers);
}

// =================================================================
// LOOP UTAMA BOT
// =================================================================

while (true) {
    $url_updates = "https://api.telegram.org/bot" . $bot_token . "/getUpdates?offset=" . ($last_update_id + 1) . "&timeout=5";
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $url_updates);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch2);
    curl_close($ch2);

    if ($response) {
        $updates = json_decode($response, true);

        if (isset($updates['result']) AND is_array($updates['result'])) {
            foreach ($updates['result'] as $update) {
                $last_update_id = $update['update_id'];

                try {
                    $chat_id = "";
                    if (isset($update['message']['chat']['id'])) {
                        $chat_id = (string)$update['message']['chat']['id'];
                    } elseif (isset($update['callback_query']['message']['chat']['id'])) {
                        $chat_id = (string)$update['callback_query']['message']['chat']['id'];
                    }

                    if ($chat_id !== (string)$admin_chat_id) {
                        continue;
                    }

                    // ==========================================
                    // 1. TANGANI BALASAN (REPLY) TEKS UNTUK PENOLAKAN
                    // ==========================================
                    if (isset($update['message']['reply_to_message']['text']) && !isset($update['message']['document'])) {
                        $reply_text = $update['message']['reply_to_message']['text'];
                        $alasan_admin = trim($update['message']['text']);

                        if (strpos($reply_text, 'alasan penolakan untuk') !== false) {
                            $parts = explode('alasan penolakan untuk', $reply_text);
                            if (isset($parts[1])) {
                                $noreg = trim(str_replace(':', '', trim($parts[1])));
                                $is_req = (strpos($noreg, 'REQ-') !== false);
                                $is_kbr = (strpos($noreg, 'KBR-') !== false);

                                if ($is_req OR $is_kbr) {
                                    $tabel = $is_req ? 'permohonan_informasi' : 'keberatan_informasi';
                                    $kolom_alasan = $is_req ? 'alasan_penolakan' : 'tanggapan';

                                    $stmt = $pdo->prepare("UPDATE " . $tabel . " SET status = 'Ditolak', " . $kolom_alasan . " = ? WHERE nomor_registrasi = ?");
                                    $stmt->execute(array($alasan_admin, $noreg));

                                    $stmt2 = $pdo->prepare("SELECT email, nama_pemohon FROM " . $tabel . " WHERE nomor_registrasi = ?");
                                    $stmt2->execute(array($noreg));
                                    $user_data = $stmt2->fetch();

                                    if ($user_data AND !empty($user_data['email'])) {
                                        $subjek = "Penolakan Layanan Informasi - " . $noreg;
                                        $msg = "<h3>Pemberitahuan Penolakan</h3>
                                                <p>Halo <b>" . htmlspecialchars($user_data['nama_pemohon']) . "</b>,</p>
                                                <p>Mohon maaf, layanan Anda dengan nomor registrasi <b>" . $noreg . "</b> harus kami tolak dengan alasan berikut:</p>
                                                <div style='background:#fee2e2; padding:15px; border-left:4px solid #dc2626; margin:10px 0;'><b>" . htmlspecialchars($alasan_admin) . "</b></div>
                                                <p>Terima kasih atas pengertiannya.</p>";
                                        kirimEmailPPID($user_data['email'], $subjek, $msg);
                                    }

                                    teleRequest('sendMessage', array(
                                        'chat_id' => $chat_id,
                                        'text' => "❌ *PROSES PENOLAKAN SELESAI*\n\nStatus `$noreg` berhasil diubah menjadi *Ditolak*.\nAlasan penolakan telah tersimpan dan dikirimkan ke email pemohon.",
                                        'parse_mode' => 'Markdown'
                                    ));
                                    continue;
                                }
                            }
                        }
                    }

                    // ==========================================
                    // 2. TANGANI UPLOAD FILE (REPLY KETIKA KLIK SELESAI)
                    // ==========================================
                    if (isset($update['message']['document'])) {
                        $noreg = "";

                        if (isset($update['message']['reply_to_message']['text'])) {
                            $reply_text = $update['message']['reply_to_message']['text'];
                            if (strpos($reply_text, 'dokumen PDF untuk') !== false) {
                                $parts = explode('dokumen PDF untuk', $reply_text);
                                if (isset($parts[1])) {
                                    $noreg = trim(str_replace(':', '', trim($parts[1])));
                                }
                            }
                        } elseif (isset($update['message']['caption'])) {
                            $caption = trim($update['message']['caption']);
                            if (strpos($caption, 'REQ-') === 0 || strpos($caption, 'KBR-') === 0) {
                                $noreg = $caption;
                            }
                        }

                        if (!empty($noreg) && (strpos($noreg, 'REQ-') === 0 || strpos($noreg, 'KBR-') === 0)) {
                            $is_req_doc = (strpos($noreg, 'REQ-') === 0);
                            $file_id = $update['message']['document']['file_id'];
                            $fileInfoJson = file_get_contents("https://api.telegram.org/bot" . $bot_token . "/getFile?file_id=" . $file_id);
                            $fileInfo = json_decode($fileInfoJson, true);
                            $file_url = "https://api.telegram.org/file/bot" . $bot_token . "/" . $fileInfo['result']['file_path'];

                            $file_content = file_get_contents($file_url);
                            $new_filename = time() . "_" . $noreg . ".pdf";

                            if ($is_req_doc) {
                                $save_dir = "uploads/jawaban/";
                                if (!is_dir($save_dir)) { mkdir($save_dir, 0777, true); }
                                $save_path = $save_dir . $new_filename;
                                file_put_contents($save_path, $file_content);

                                $stmt = $pdo->prepare("UPDATE permohonan_informasi SET status = 'Selesai', file_jawaban_pdf = ? WHERE nomor_registrasi = ?");
                                $stmt->execute(array($save_path, $noreg));
                                $stmt_email = $pdo->prepare("SELECT email, nama_pemohon FROM permohonan_informasi WHERE nomor_registrasi = ?");
                            } else {
                                $save_dir = "uploads/jawaban_keberatan/";
                                if (!is_dir($save_dir)) { mkdir($save_dir, 0777, true); }
                                $save_path = $save_dir . $new_filename;
                                file_put_contents($save_path, $file_content);

                                $stmt = $pdo->prepare("UPDATE keberatan_informasi SET status = 'Selesai', file_jawaban_pdf = ? WHERE nomor_registrasi = ?");
                                $stmt->execute(array($save_path, $noreg));
                                $stmt_email = $pdo->prepare("SELECT email, nama_pemohon FROM keberatan_informasi WHERE nomor_registrasi = ?");
                            }

                            $stmt_email->execute(array($noreg));
                            $pemohon = $stmt_email->fetch();

                            $pesan_tambahan = "";
                            if ($pemohon AND !empty($pemohon['email'])) {
                                $msg_html = "<h3>Dokumen Tanggapan PPID Telah Diterbitkan</h3>
                                             <p>Halo <b>" . htmlspecialchars($pemohon['nama_pemohon']) . "</b>,</p>
                                             <p>Tanggapan untuk nomor registrasi <b>" . $noreg . "</b> telah selesai diproses.</p>
                                             <p>Silakan unduh dokumen resmi (PDF) pada lampiran email ini.</p>";
                                kirimEmailPPID($pemohon['email'], "Dokumen PPID Diterbitkan - " . $noreg, $msg_html, $save_path, $new_filename);
                                $pesan_tambahan = "📧 PDF tanggapan berhasil dikirim ke Email: *" . $pemohon['email'] . "*";
                            }

                            teleRequest('sendMessage', array(
                                'chat_id' => $chat_id,
                                'text' => "✅ *PENYELESAIAN SUKSES*\nFile untuk `$noreg` berhasil diunggah. Status menjadi *Selesai*.\n\n$pesan_tambahan",
                                'parse_mode' => 'Markdown'
                            ));
                            continue;
                        }
                    }

                    // ==========================================
                    // 3. TANGANI PESAN TEKS BIASA DARI ADMIN
                    // ==========================================
                    if (isset($update['message']['text']) && !isset($update['message']['reply_to_message'])) {
                        $text = strtolower(trim($update['message']['text']));

                        $perintah_bantuan = array('/start', 'menu', 'help');
                        if (in_array($text, $perintah_bantuan)) {
                            $pesan_bantuan = "🤖 *Menu Admin PPID*\n\n"
                                           . "👉 `cek permohonan` (Tampil yg Pending)\n"
                                           . "👉 `list permohonan` (Tampil Semua Data)\n"
                                           . "👉 `cek keberatan` (Tampil yg Pending)\n"
                                           . "👉 `list keberatan` (Tampil Semua Data)";
                            teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => $pesan_bantuan, 'parse_mode' => 'Markdown'));
                        }

                        elseif ($text === 'cek permohonan' OR $text === 'list permohonan') {
                            if ($text === 'cek permohonan') {
                                $stmt = $pdo->query("SELECT nomor_registrasi, nama_pemohon, status FROM permohonan_informasi WHERE status = 'Menunggu Verifikasi' OR status = 'Pending' OR status = '' ORDER BY id DESC LIMIT 10");
                                $judul_list = "*Permohonan Pending:*";
                            } else {
                                $stmt = $pdo->query("SELECT nomor_registrasi, nama_pemohon, status FROM permohonan_informasi ORDER BY id DESC LIMIT 15");
                                $judul_list = "*Semua Permohonan (15 Terakhir):*";
                            }
                            $data_req = $stmt->fetchAll();

                            if (!$data_req) {
                                teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => "Data permohonan kosong."));
                                continue;
                            }

                            $keyboard = array();
                            foreach ($data_req as $row) {
                                $status_tampil = empty($row['status']) ? 'Menunggu Verifikasi' : $row['status'];
                                $btn_text = "[" . $row['nomor_registrasi'] . "] - " . $row['nama_pemohon'] . " (" . $status_tampil . ")";
                                $btn_cb = "dtl_req_" . $row['nomor_registrasi'];
                                $keyboard[] = array(array('text' => $btn_text, 'callback_data' => $btn_cb));
                            }
                            teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => $judul_list . "\nPilih nomor registrasi:", 'parse_mode' => 'Markdown', 'reply_markup' => array('inline_keyboard' => $keyboard)));
                        }

                        elseif ($text === 'cek keberatan' OR $text === 'list keberatan') {
                            if ($text === 'cek keberatan') {
                                $stmt = $pdo->query("SELECT nomor_registrasi, nama_pemohon, status FROM keberatan_informasi WHERE status = 'Pending' OR status = 'Menunggu Verifikasi' OR status = '' ORDER BY id DESC LIMIT 10");
                                $judul_list = "*Keberatan Pending:*";
                            } else {
                                $stmt = $pdo->query("SELECT nomor_registrasi, nama_pemohon, status FROM keberatan_informasi ORDER BY id DESC LIMIT 15");
                                $judul_list = "*Semua Keberatan (15 Terakhir):*";
                            }
                            $data_keb = $stmt->fetchAll();

                            if (!$data_keb) {
                                teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => "Data keberatan kosong."));
                                continue;
                            }

                            $keyboard = array();
                            foreach ($data_keb as $row) {
                                $status_tampil = empty($row['status']) ? 'Pending' : $row['status'];
                                $btn_text = "[" . $row['nomor_registrasi'] . "] - " . $row['nama_pemohon'] . " (" . $status_tampil . ")";
                                $btn_cb = "dtl_keb_" . $row['nomor_registrasi'];
                                $keyboard[] = array(array('text' => $btn_text, 'callback_data' => $btn_cb));
                            }
                            teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => $judul_list . "\nPilih nomor registrasi:", 'parse_mode' => 'Markdown', 'reply_markup' => array('inline_keyboard' => $keyboard)));
                        }
                    }

                    // ==========================================
                    // 4. TANGANI TOMBOL (CALLBACK QUERY)
                    // ==========================================
                    if (isset($update['callback_query'])) {
                        $cb_id = $update['callback_query']['id'];
                        $data_cb = $update['callback_query']['data'];
                        $msg_id = $update['callback_query']['message']['message_id'];

                        // TAMPILKAN SEMUA DATA & FILE PERMOHONAN
                        if (strpos($data_cb, 'dtl_req_') === 0) {
                            $noreg = str_replace('dtl_req_', '', $data_cb);
                            $stmt = $pdo->prepare("SELECT * FROM permohonan_informasi WHERE nomor_registrasi = ?");
                            $stmt->execute(array($noreg));
                            $row = $stmt->fetch();

                            if ($row) {
                                $status = empty($row['status']) ? 'Menunggu Verifikasi' : $row['status'];
                                $nama = !empty($row['nama_pemohon']) ? $row['nama_pemohon'] : $row['nama_lengkap'];
                                $kategori = !empty($row['kategori_permohonan']) ? $row['kategori_permohonan'] : $row['kategori_pemohon'];
                                $hp = !empty($row['no_hp']) ? $row['no_hp'] : $row['no_telp'];

                                $teks = "📄 *DATA LENGKAP PERMOHONAN*\n" .
                                        "━━━━━━━━━━━━━━━━━━━━\n" .
                                        "📌 *No Reg:* `$noreg`\n" .
                                        "📁 *Kategori:* $kategori\n" .
                                        "👤 *Nama:* $nama\n" .
                                        "🪪 *NIK:* " . $row['nik'] . "\n" .
                                        "📧 *Email:* " . $row['email'] . "\n" .
                                        "📞 *No HP:* $hp\n" .
                                        "💼 *Pekerjaan:* " . $row['pekerjaan'] . "\n" .
                                        "🏠 *Alamat:* " . $row['alamat'] . "\n\n" .
                                        "📝 *Rincian Info:* " . $row['rincian_informasi'] . "\n" .
                                        "🎯 *Tujuan Info:* " . $row['tujuan_penggunaan'] . "\n" .
                                        "👁️ *Cara Peroleh:* " . $row['cara_memperoleh'] . " | " . $row['bentuk_salinan'] . " | " . $row['cara_mendapatkan'] . "\n" .
                                        "🚥 *Status Saat Ini:* *$status*";

                                teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => $teks, 'parse_mode' => 'Markdown'));

                                if (!empty($row['ktp_path']) AND file_exists($row['ktp_path'])) {
                                    teleSendDocument($chat_id, $row['ktp_path'], "File KTP: $nama ($noreg)");
                                }
                                if (!empty($row['dokumen_tambahan_path']) AND file_exists($row['dokumen_tambahan_path'])) {
                                    teleSendDocument($chat_id, $row['dokumen_tambahan_path'], "Dokumen Tambahan: $nama ($noreg)");
                                }

                                $keyboard = array();
                                if (strtolower($status) !== 'selesai' AND strtolower($status) !== 'ditolak') {
                                    $keyboard[] = array(array('text' => '✅ Ubah ke Diproses (Setujui Berkas)', 'callback_data' => "upd_req_Proses_" . $noreg));
                                }
                                teleRequest('sendMessage', array(
                                    'chat_id' => $chat_id,
                                    'text' => "Apakah berkas di atas sudah sesuai? Jika ya, silakan proses.",
                                    'reply_markup' => array('inline_keyboard' => $keyboard)
                                ));

                                teleRequest('deleteMessage', array('chat_id' => $chat_id, 'message_id' => $msg_id));
                            }
                        }

                        // TAMPILKAN SEMUA DATA & FILE KEBERATAN
                        if (strpos($data_cb, 'dtl_keb_') === 0) {
                            $noreg = str_replace('dtl_keb_', '', $data_cb);
                            $stmt = $pdo->prepare("SELECT * FROM keberatan_informasi WHERE nomor_registrasi = ?");
                            $stmt->execute(array($noreg));
                            $row = $stmt->fetch();

                            if ($row) {
                                $status = empty($row['status']) ? 'Pending' : $row['status'];
                                $teks = "⚖️ *DATA LENGKAP KEBERATAN*\n" .
                                        "━━━━━━━━━━━━━━━━━━━━\n" .
                                        "📌 *No Keberatan:* `$noreg`\n" .
                                        "🔗 *No Permohonan:* `" . $row['kode_permohonan'] . "`\n" .
                                        "👤 *Nama:* " . $row['nama_pemohon'] . "\n" .
                                        "🪪 *NIK:* " . $row['nik'] . "\n" .
                                        "📧 *Email:* " . $row['email'] . "\n" .
                                        "📞 *No HP:* " . $row['no_hp'] . "\n" .
                                        "🧑‍⚖️ *Dikuasakan:* " . $row['dikuasakan'] . "\n\n" .
                                        "🛑 *Alasan Keberatan:*\n" . $row['alasan_keberatan'] . "\n\n" .
                                        "📜 *Kronologi:*\n" . $row['kronologi'] . "\n\n" .
                                        "🚥 *Status Saat Ini:* *$status*";

                                teleRequest('sendMessage', array('chat_id' => $chat_id, 'text' => $teks, 'parse_mode' => 'Markdown'));

                                if (!empty($row['surat_keberatan_path']) AND file_exists($row['surat_keberatan_path'])) {
                                    teleSendDocument($chat_id, $row['surat_keberatan_path'], "Surat Keberatan: " . $row['nama_pemohon'] . " ($noreg)");
                                }

                                $keyboard = array();
                                if (strtolower($status) !== 'selesai' AND strtolower($status) !== 'ditolak') {
                                    $keyboard[] = array(array('text' => '✅ Ubah ke Diproses (Setujui Berkas)', 'callback_data' => "upd_keb_Proses_" . $noreg));
                                }
                                teleRequest('sendMessage', array(
                                    'chat_id' => $chat_id,
                                    'text' => "Apakah berkas di atas sudah sesuai? Jika ya, silakan proses.",
                                    'reply_markup' => array('inline_keyboard' => $keyboard)
                                ));

                                teleRequest('deleteMessage', array('chat_id' => $chat_id, 'message_id' => $msg_id));
                            }
                        }

                        // ==========================================
                        // AKSI UBAH KE DIPROSES & BERI TOMBOL AKHIR
                        // ==========================================
                        $is_upd_req = (strpos($data_cb, 'upd_req_Proses_') === 0);
                        $is_upd_keb = (strpos($data_cb, 'upd_keb_Proses_') === 0);

                        if ($is_upd_req OR $is_upd_keb) {
                            $jenis = $is_upd_req ? 'permohonan' : 'keberatan';
                            $noreg = str_replace(array("upd_req_Proses_", "upd_keb_Proses_"), '', $data_cb);
                            $tabel = ($jenis == 'permohonan') ? 'permohonan_informasi' : 'keberatan_informasi';

                            $stmt = $pdo->prepare("UPDATE " . $tabel . " SET status = 'Diproses' WHERE nomor_registrasi = ?");
                            $stmt->execute(array($noreg));

                            $stmt2 = $pdo->prepare("SELECT email, nama_pemohon FROM " . $tabel . " WHERE nomor_registrasi = ?");
                            $stmt2->execute(array($noreg));
                            $dt_user = $stmt2->fetch();
                            if ($dt_user AND !empty($dt_user['email'])) {
                                $msg = "<h3>Layanan Diproses</h3><p>Halo <b>" . htmlspecialchars($dt_user['nama_pemohon']) . "</b>,</p><p>Permohonan/Keberatan Anda dengan Nomor Registrasi <b>" . $noreg . "</b> saat ini sedang diproses oleh petugas.</p>";
                                kirimEmailPPID($dt_user['email'], "Status Diperbarui - " . $noreg, $msg);
                            }

                            teleRequest('deleteMessage', array('chat_id' => $chat_id, 'message_id' => $msg_id));

                            $instruksi = "🔄 *Status $noreg menjadi DIPROSES*\n\n"
                                       . "Tentukan tindakan akhir:\n\n"
                                       . "🟢 *SELESAI:* Klik tombol di bawah ini lalu balas (reply) dengan dokumen PDF.\n"
                                       . "🔴 *DITOLAK:* Klik tombol tolak untuk memasukkan alasan.";

                            $keyboard_final = array(
                                array(array('text' => '✅ SELESAI & UPLOAD FILE', 'callback_data' => "btn_selesai_" . $noreg)),
                                array(array('text' => '❌ TOLAK PERMOHONAN INI', 'callback_data' => "btn_tolak_" . $noreg))
                            );

                            teleRequest('sendMessage', array(
                                'chat_id' => $chat_id,
                                'text' => $instruksi,
                                'parse_mode' => 'Markdown',
                                'reply_markup' => array('inline_keyboard' => $keyboard_final)
                            ));
                        }

                        // ==========================================
                        // AKSI KLIK TOMBOL SELESAI (MUNCULKAN REPLY PDF)
                        // ==========================================
                        if (strpos($data_cb, 'btn_selesai_') === 0) {
                            $noreg = str_replace('btn_selesai_', '', $data_cb);
                            $teks_reply = "⚠️ Silakan balas (Reply) pesan ini dengan mengunggah (upload) dokumen PDF untuk $noreg :";
                            teleRequest('sendMessage', array(
                                'chat_id' => $chat_id,
                                'text' => $teks_reply,
                                'reply_markup' => array('force_reply' => true, 'selective' => true)
                            ));
                            teleRequest('answerCallbackQuery', array('callback_query_id' => $cb_id));
                        }

                        // ==========================================
                        // AKSI KLIK TOMBOL TOLAK (MUNCULKAN REPLY ALASAN)
                        // ==========================================
                        if (strpos($data_cb, 'btn_tolak_') === 0) {
                            $noreg = str_replace('btn_tolak_', '', $data_cb);
                            $teks_reply = "⚠️ Silakan balas (Reply) pesan ini dengan mengetikkan alasan penolakan untuk $noreg :";
                            teleRequest('sendMessage', array(
                                'chat_id' => $chat_id,
                                'text' => $teks_reply,
                                'reply_markup' => array('force_reply' => true, 'selective' => true)
                            ));
                            teleRequest('answerCallbackQuery', array('callback_query_id' => $cb_id));
                        }
                    }

                } catch (Exception $e) {
                    teleRequest('sendMessage', array('chat_id' => $admin_chat_id, 'text' => "⚠️ *Terjadi Kesalahan SQL:*\n" . $e->getMessage(), 'parse_mode' => 'Markdown'));
                }
            }
        }
    }
    sleep(2);
}
?>