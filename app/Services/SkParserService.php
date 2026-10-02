<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class SkParserService
{
    /**
     * Common academic and honorific titles to clean from names for fuzzy matching.
     */
    protected array $titles = [
        'assoc prof', 'prof', 'dr', 'drs', 'dra', 'ir', 'drg', 'hj', 'h',
        'st', 'mt', 'ipu', 'ipm', 'asean eng', 'asca', 'skm', 'mkkk', 'se', 'mak',
        'makt', 'msi', 'mm', 'meng', 'phd', 'mkm', 'mkes', 'skom', 'mkom', 'spd', 'mpdt',
        'mpd', 'sh', 'mh', 'ssos', 'msos', 'beng', 'msc', 'bsc', 'cmt', 'cpa',
        'cta', 'skl', 'shum', 'sfarm', 'sm', 'sak', 'amd', 'cps', 'ca', 'ak', 'magang',
        'akt', 'mag', 'sag', 'ss', 'sip'
    ];

    /**
     * Common action verbs / procedure keywords that must NEVER be parsed as person names.
     */
    protected array $forbiddenWords = [
        'melakukan', 'mengelola', 'merekapitulasi', 'menyusun', 'membuat', 'membantu',
        'menyiapkan', 'mengadakan', 'merawat', 'mengarsipkan', 'memasang', 'memvalidasi',
        'menerima', 'melayani', 'memantau', 'mengesahkan', 'menentukan', 'menetapkan',
        'prosedur', 'pedoman', 'panduan', 'standar', 'laporan', 'indikator', 'tugas',
        'ketersediaan', 'kesiapan', 'ketepatan', 'kelengkapan', 'pelaksanaan', 'penyelenggaraan',
        'pengadaan', 'penyiapan', 'pemasangan', 'perawatan', 'kuesioner', 'spanduk',
        'terkait', 'calon', 'seksi', 'koordinator', 'peminat', 'pendaftar', 'daftar ulang',
        'terpenuhi', 'membutuhkan', 'mengidentifikasi', 'relevan', 'misal', 'tentang',
        'nomor', 'hal.', 'halaman', 'lampiran', 'yayasan', 'universitas', 'ibnu sina',
        'fakultas', 'program studi', 'batam', 'tahun', 'akademik', 'waktu', 'klinik',
        'senat universitas', 'senat fakultas', 'buku tamu', 'penerima tamu',
        'sarpras', 'desain', 'transportasi', 'suara', 'konsumsi', 'perlengkapan',
        'dokumentasi', 'undangan', 'humas', 'acara', 'evaluasi', 'sekretariat', 'senat',
        // Decree terms, preamble and closing footers
        'tembusan', 'arsip', 'pendidikan', 'nasional', 'pendidikan nasional', 'sistem pendidikan',
        'pendidikan tinggi', 'perguruan tinggi', 'surat keputusan', 'undang-undang', 'peraturan',
        'pemerintah', 'statuta', 'menimbang', 'mengingat', 'memperhatikan', 'memutuskan',
        'menetapkan', 'ditetapkan', 'pertama', 'kedua', 'ketiga', 'keempat', 'kelima',
        'masing-masing', 'yang bersangkutan', 'salinan', 'rektorat', 'rektor', 'dekan',
        'bismillahirrahmanirrahim'
    ];

    /**
     * Parse uploaded PDF SK and extract metadata + committee members.
     */
    public function parsePdf(string $absoluteFilePath): array
    {
        $rawText = '';
        $pages = [];
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($absoluteFilePath);
            $rawText = $pdf->getText();
            $pages = $pdf->getPages();
        } catch (\Throwable $e) {
            Log::error('PDF Parser Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Gagal membaca isi PDF: ' . $e->getMessage(),
                'nomor_sk' => null,
                'nama_kepanitiaan' => null,
                'tahun_akademik' => null,
                'tanggal_sk' => null,
                'raw_text' => '',
                'members' => [],
            ];
        }

        $nomorSk = $this->extractNomorSk($rawText);
        $namaKepanitiaan = $this->extractNamaKepanitiaan($rawText);
        $tahunAkademik = $this->extractTahunAkademik($rawText);
        $tanggalSk = $this->extractTanggalSk($rawText);
        $members = $this->extractMembersFromPages($pages, $rawText);

        // Match extracted members with existing SIMKINERJA users
        $allUsers = User::where('status', 'Aktif')->orWhereNull('status')->get(['id', 'name', 'nidn', 'jabatan', 'unit']);
        $matchedCount = 0;

        foreach ($members as &$item) {
            $matchedUser = $this->matchUser($item['nama_sk'], $allUsers);
            if ($matchedUser) {
                $item['user_id'] = $matchedUser->id;
                $item['user_name'] = $matchedUser->name;
                $item['user_jabatan'] = $matchedUser->jabatan;
                $item['user_unit'] = $matchedUser->unit;
                $item['matched'] = true;
                $matchedCount++;
            } else {
                $item['user_id'] = null;
                $item['user_name'] = null;
                $item['user_jabatan'] = null;
                $item['user_unit'] = null;
                $item['matched'] = false;
            }
        }
        unset($item);

        return [
            'success' => true,
            'nomor_sk' => $nomorSk,
            'nama_kepanitiaan' => $namaKepanitiaan,
            'tahun_akademik' => $tahunAkademik,
            'tanggal_sk' => $tanggalSk,
            'raw_text' => $rawText,
            'total_anggota' => count($members),
            'total_matched' => $matchedCount,
            'members' => $members,
        ];
    }

    /**
     * Extract SK Number from text.
     */
    protected function extractNomorSk(string $text): ?string
    {
        if (preg_match('/Nomor\s*:\s*([0-9A-Za-z\/\.\-]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Extract committee name from title / subject.
     */
    protected function extractNamaKepanitiaan(string $text): ?string
    {
        if (preg_match('/Tentang\s*[\n\r]+\s*([^\n\r]+(?:\n[^\n\r]+)?)/i', $text, $matches)) {
            $cleaned = trim(preg_replace('/\s+/', ' ', $matches[1]));
            return $cleaned;
        }

        if (preg_match('/PEMBENTUKAN\s+PANITIA\s+([^\n\r]+(?:\n[^\n\r]+)?)/i', $text, $matches)) {
            return 'Panitia ' . trim(preg_replace('/\s+/', ' ', $matches[1]));
        }

        return 'Kepanitiaan Kegiatan';
    }

    /**
     * Extract Academic Year (e.g. 2026/2027).
     */
    protected function extractTahunAkademik(string $text): ?string
    {
        if (preg_match('/(?:TAHUN\s+AKADEMIK|T\.A|PERIODE)\s*([0-9]{4}\s*[\/\-]\s*[0-9]{4})/i', $text, $matches)) {
            return preg_replace('/\s+/', '', $matches[1]);
        }
        if (preg_match('/([0-9]{4}\s*[\/\-]\s*[0-9]{4})/', $text, $matches)) {
            return preg_replace('/\s+/', '', $matches[1]);
        }
        return date('Y') . '/' . (date('Y') + 1);
    }

    /**
     * Extract SK decree date.
     */
    protected function extractTanggalSk(string $text): ?string
    {
        if (preg_match('/(?:Pada tanggal\s*:\s*|tanggal\s*:\s*)([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4})/i', $text, $matches)) {
            $rawDate = trim($matches[1]);
            $bulanMap = [
                'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
                'January' => '01', 'February' => '02', 'March' => '03', 'May' => '05',
                'June' => '06', 'July' => '07', 'August' => '08', 'October' => '10', 'December' => '12'
            ];

            $parts = explode(' ', $rawDate);
            if (count($parts) === 3) {
                $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $month = $bulanMap[ucfirst(strtolower($parts[1]))] ?? '01';
                $year = $parts[2];
                return "{$year}-{$month}-{$day}";
            }
        }
        return date('Y-m-d');
    }

    /**
     * Extract committee members strictly from decree and Lampiran Susunan Panitia pages.
     * SOP, IKU, and TUPOKSI pages are explicitly excluded.
     */
    protected function extractMembersFromPages(array $pages, string $fallbackFullText): array
    {
        $members = [];

        if (empty($pages)) {
            return $this->extractMembersFromIsolatedText($fallbackFullText);
        }

        $totalPageCount = count($pages);

        // Detect start page: if Lampiran Susunan Panitia is present, skip Page 1 decree preambles
        $startPageIndex = 0;
        for ($p = 0; $p < $totalPageCount; $p++) {
            $t = $pages[$p]->getText();
            if (preg_match('/(?:Lampiran\s*(?:[0-9]+|[IVXLCDM]+)?\s*:\s*Susunan\s+Panitia|SUSUNAN\s+PANITIA)/i', $t)) {
                $startPageIndex = $p;
                break;
            }
        }

        $currentSection = 'Pelaksana';
        $currentRole = 'Anggota';

        // Iterate through committee pages; STOP as soon as TUPOKSI / SOP begins
        for ($pIdx = $startPageIndex; $pIdx < $totalPageCount; $pIdx++) {
            $pText = $pages[$pIdx]->getText();

            // Stop immediately if page contains TUPOKSI or SOP tables (e.g. Lampiran 2)
            if (preg_match('/(?:Lampiran\s*2\s*:\s*Tupoksi|TUPOKSI\s+PANITIA|TUGAS\s+POKOK\s+DAN\s+FUNGSI|SOP\s+Setiap\s+IKU|Indikator\s+Kinerja\s+Utama|STANDAR\s+OPERASIONAL)/i', $pText)) {
                break;
            }

            // Truncate at Tembusan or Ditetapkan di to ignore closing signatures, tembusan, and arsip
            $hasDitetapkan = false;
            if (preg_match('/(?:Ditetapkan di\s*:|Tembusan\s*:|TEMBUSAN\s*:)/i', $pText, $cutoffMatch, PREG_OFFSET_CAPTURE)) {
                $pText = substr($pText, 0, $cutoffMatch[0][1]);
                $hasDitetapkan = true;
            }

            $lines = preg_split('/[\r\n]+/', $pText);
            $totalLines = count($lines);

            for ($i = 0; $i < $totalLines; $i++) {
                $line = trim($lines[$i]);
                if (empty($line) || $line === ':') continue;

                // Skip page headers, letterheads
                if ($this->isHeaderOrMetadata($line)) {
                    continue;
                }

                // Check if line defines a major section (e.g. "1. Sekretariat", "2. Seksi Acara", "4. Seksi Perlengkapan / Sarpras", "A. Seksi Perencanaan")
                if (preg_match('/^(?:[0-9]+[\.\)]|[A-Z]\.)\s*(Seksi\s+[^:\n\r]+|Sekretariat|Tim\s+[^:\n\r]+)/i', $line, $secMatch)) {
                    $currentSection = trim($secMatch[1]);
                    $currentRole = 'Anggota';
                    continue;
                }

                if (preg_match('/^(Pelindung|Penanggung Jawab|Pengarah|Pelaksana|Bendahara|Wakil Bendahara|Seksi\s*–\s*Seksi|Senat\s+Universitas|Senat\s+Fakultas)/i', $line, $secMatch)) {
                    $matchedSec = trim($secMatch[1]);
                    if (in_array(strtolower($matchedSec), ['pelindung', 'penanggung jawab', 'pengarah', 'pelaksana', 'bendahara', 'wakil bendahara'])) {
                        $currentSection = ucfirst(strtolower($matchedSec));
                        $currentRole = $currentSection;
                    }

                    // Check if inline person e.g. "Pelindung : drg. Andi Tenri Ummu, MM"
                    if (str_contains($line, ':')) {
                        $parts = explode(':', $line, 2);
                        $afterColon = trim($parts[1]);
                        if (preg_match('/^(?:Ketua|Rektor|Dekan|Wakil Rektor)/i', $afterColon)) {
                            // Check next line for candidate name
                            if (isset($lines[$i + 1])) {
                                $next = trim($lines[$i + 1]);
                                if ($this->isValidPersonName($next)) {
                                    $members[] = [
                                        'posisi' => $matchedSec,
                                        'nama_sk' => $next
                                    ];
                                    $i++;
                                    continue;
                                }
                            }
                        } elseif ($this->isValidPersonName($afterColon)) {
                            $members[] = [
                                'posisi' => $matchedSec,
                                'nama_sk' => $afterColon
                            ];
                            continue;
                        }
                    }
                    continue;
                }

                // Check if line defines a position label e.g. "Koordinator", "Koordinator :", "Wakil Ketua 1", "Ketua :", "Sekretaris :"
                if (preg_match('/^(Koordinator|Ketua|Wakil Ketua\s*\d*|Wakil Sekretaris\s*\d*|Sekretaris|Wakil Bendahara\s*\d*|Bendahara|PIC\s+[^:\n\r]+|Buku Tamu|Penerima Tamu|Anggota)(?:\s*:\s*(.*))?$/i', $line, $posMatch)) {
                    $roleName = trim($posMatch[1]);
                    $afterColon = isset($posMatch[2]) ? trim($posMatch[2]) : '';

                    $currentRole = $roleName;

                    if (!empty($afterColon)) {
                        if (preg_match_all('/(?:[0-9]+[\.\)]\s*|^)([a-zA-Z\s\.,\(\)\'\-]+?)(?=(?:[0-9]+[\.\)]\s*[a-zA-Z])|$)/', $afterColon, $mNames)) {
                            foreach ($mNames[1] as $cName) {
                                $cName = trim(preg_replace('/^[0-9]+[\.\)]\s*/', '', $cName), " \t\n\r\0\x0B:-");
                                if ($this->isValidPersonName($cName)) {
                                    $posLabel = ($currentSection && stripos($roleName, $currentSection) === false)
                                        ? $currentSection . ' - ' . $roleName
                                        : $roleName;
                                    $members[] = ['posisi' => $posLabel, 'nama_sk' => $cName];
                                }
                            }
                        } elseif ($this->isValidPersonName($afterColon)) {
                            $posLabel = ($currentSection && stripos($roleName, $currentSection) === false)
                                ? $currentSection . ' - ' . $roleName
                                : $roleName;
                            $members[] = ['posisi' => $posLabel, 'nama_sk' => $afterColon];
                        }
                    }
                    continue;
                }

                // Check if line has colon e.g. ": Wakil Rektor I UIS" or "Ketua : Dr. Sudianto SE.,M.Si"
                if (str_contains($line, ':')) {
                    $parts = explode(':', $line, 2);
                    $left = trim($parts[0]);
                    $right = trim($parts[1]);

                    if (!empty($right) && $this->isValidPersonName($right)) {
                        $posLabel = !empty($left) ? $left : $currentRole;
                        if ($currentSection && stripos($posLabel, $currentSection) === false) {
                            $posLabel = $currentSection . ' - ' . $posLabel;
                        }
                        $members[] = ['posisi' => $posLabel, 'nama_sk' => $right];
                        continue;
                    } elseif (!empty($right) && preg_match('/^(Wakil Rektor|Dekan)/i', $right)) {
                        $posLabel = 'Pengarah (' . $right . ')';
                        if (isset($lines[$i + 1]) && $this->isValidPersonName(trim($lines[$i + 1]))) {
                            $members[] = ['posisi' => $posLabel, 'nama_sk' => trim($lines[$i + 1])];
                            $i++;
                            continue;
                        }
                    }
                }

                // Check if line contains numbered names e.g. "1. M. Fadli Luran, SE., MM" or multiple "1. Andi Taher 11. Rizal"
                if (preg_match_all('/(?:[0-9]+[\.\)]\s*|^)([a-zA-Z\s\.,\(\)\'\-]+?)(?=(?:[0-9]+[\.\)]\s*[a-zA-Z])|$)/', $line, $mNames)) {
                    $foundAny = false;
                    foreach ($mNames[1] as $cName) {
                        $cName = trim(preg_replace('/^[0-9]+[\.\)]\s*/', '', $cName), " \t\n\r\0\x0B:-");
                        if ($this->isValidPersonName($cName)) {
                            $posLabel = ($currentSection && stripos($currentRole, $currentSection) === false)
                                ? $currentSection . ' - ' . $currentRole
                                : ($currentSection ?: $currentRole);
                            $members[] = ['posisi' => $posLabel, 'nama_sk' => $cName];
                            $foundAny = true;
                        }
                    }
                    if ($foundAny) continue;
                }

                // Standalone name line under active section & role e.g. "Dr. Herdianti, S.KM., M.Kes."
                $cName = trim(preg_replace('/^[0-9]+[\.\)]\s*/', '', $line), " \t\n\r\0\x0B:-");
                if ($this->isValidPersonName($cName)) {
                    $posLabel = ($currentSection && stripos($currentRole, $currentSection) === false)
                        ? $currentSection . ' - ' . $currentRole
                        : ($currentSection ?: $currentRole);
                    $members[] = ['posisi' => $posLabel, 'nama_sk' => $cName];
                }
            }

            if ($hasDitetapkan) {
                break;
            }
        }

        // Deduplicate
        $unique = [];
        foreach ($members as $m) {
            $k = strtolower(trim($m['nama_sk']));
            if (!isset($unique[$k])) {
                $unique[$k] = $m;
            }
        }

        return array_values($unique);
    }

    /**
     * Fallback for single text stream where pages aren't available.
     */
    protected function extractMembersFromIsolatedText(string $text): array
    {
        $startPos = 0;
        if (preg_match('/(?:Lampiran\s*\d*\s*:\s*Susunan\s+Panitia|SUSUNAN\s+PANITIA)/i', $text, $match, PREG_OFFSET_CAPTURE)) {
            $startPos = $match[0][1];
        } elseif (preg_match('/MEMUTUSKAN/i', $text, $match, PREG_OFFSET_CAPTURE)) {
            $startPos = $match[0][1];
        }

        $subText = substr($text, $startPos);
        $endPos = strlen($subText);
        if (preg_match('/(?:TUGAS POKOK DAN FUNGSI|SOP Setiap IKU|Indikator Kinerja Utama|Ditetapkan di\s*:)/i', $subText, $endMatch, PREG_OFFSET_CAPTURE)) {
            $endPos = $endMatch[0][1];
        }

        $committeeText = substr($subText, 0, $endPos);
        $lines = preg_split('/[\r\n]+/', $committeeText);
        $members = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 3) continue;

            $candidate = preg_replace('/^[0-9]+[\.\)]\s*/', '', $line);
            $candidate = trim($candidate, " \t\n\r\0\x0B:-");

            if ($this->isValidPersonName($candidate)) {
                $members[] = [
                    'posisi' => 'Panitia',
                    'nama_sk' => $candidate
                ];
            }
        }

        $unique = [];
        foreach ($members as $m) {
            $k = strtolower(trim($m['nama_sk']));
            if (!isset($unique[$k])) {
                $unique[$k] = $m;
            }
        }

        return array_values($unique);
    }

    /**
     * Check if a candidate string is an authentic person name.
     */
    protected function isValidPersonName(string $text): bool
    {
        $text = trim($text);
        $len = strlen($text);
        if ($len < 3 || $len > 65) return false;

        $textLower = strtolower($text);

        // Discard forbidden action verbs / procedure phrases
        foreach ($this->forbiddenWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $textLower)) {
                return false;
            }
        }

        // Must not be numbers/symbols only
        if (preg_match('/^[0-9\W]+$/', $text)) {
            return false;
        }

        // Must not match structural position patterns (e.g. "Wakil Ketua I", "Ketua Pelaksana", "Sekretaris", "Bendahara")
        if (preg_match('/^(?:Ketua|Ketua Pelaksana|Wakil Ketua|Sekretaris|Wakil Sekretaris|Bendahara|Wakil Bendahara|Koordinator|Anggota|Pelaksana|Pengarah|Penanggung Jawab|Pelindung|Tim|Seksi|Seksi\s*–\s*Seksi)\b(?:\s*(?:[0-9]+|[IVXLCDM]+))?$/i', $text)) {
            return false;
        }

        // Must not start with common role/task prefixes
        if (preg_match('/^(?:PIC\b|Koordinator\b|Seksi\b|Tim\b|Monitoring\b|Analisis\b|Validasi\b|Perekapan\b|Pendaftaran\b|Buku Tamu|Penerima Tamu)/i', $text)) {
            return false;
        }

        // Must not be a standalone position keyword
        $purePositions = [
            'ketua pelaksana', 'wakil ketua', 'sekretaris', 'bendahara', 'koordinator',
            'pic sekolah', 'pic pemerintahan', 'pic pemerintah', 'pic sosial media',
            'pic perusahaan', 'pic event', 'pic rektorat', 'pic fst', 'pic feb',
            'pic fikes', 'pic tpa', 'pic jaringan', 'pic wawancara', 'pic zoom dan host',
            'monitoring kinerja', 'analisis kuesioner', 'validasi data', 'perekapan data',
            'tes potensi', 'tes wawancara', 'tes kesehatan', 'anggota', 'dekan feb',
            'dekan fst', 'dekan fikes', 'wakil rektor i uis', 'wakil rektor ii uis', 'wakil rektor iii uis'
        ];
        if (in_array($textLower, $purePositions)) {
            return false;
        }

        // Contains academic degree or honorific
        if (preg_match('/\b(drg?\.|dr|ir\.|drs?\.|prof|s\.t\.|st|m\.t\.|mt|s\.e\.|se|m\.m\.|mm|s\.kom|skom|m\.kom|mkom|s\.pd|spd|m\.pd|mpdt|s\.km|skm|m\.kes|m\.km|mkkk|s\.si|msi|s\.kl|skl|s\.hum|shum|s\.farm|sfarm|s\.m\.|sm|s\.ak|sak|s\.sos|ssos|a\.md|amd|magang)\b/i', $textLower)) {
            return true;
        }

        // 2 to 4 capitalized Indonesian name words
        $words = explode(' ', $text);
        if (count($words) >= 1 && count($words) <= 5) {
            $allAlpha = true;
            foreach ($words as $w) {
                $wClean = trim($w, ".,'-() ");
                if (!ctype_alpha($wClean)) {
                    $allAlpha = false;
                    break;
                }
            }
            if ($allAlpha && strlen($words[0]) >= 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a line is a header or university metadata.
     */
    protected function isHeaderOrMetadata(string $line): bool
    {
        $line = trim($line);
        if (empty($line)) return true;
        if (preg_match('/^(YAYASAN|UNIVERSITAS|Jalan\s+Teuku|Email\s*:|Hal\.\s*\d+|SURAT\s+KEPUTUSAN|Nomor\s*:|Tentang|PEMBENTUKAN|TAHUN\s+AKADEMIK|Bismillahirrahmanirrahim|Dengan\s+bertawakal|Menimbang|Mengingat|MEMUTUSKAN|Ditetapkan|Pada\s+tanggal|Rektor|NIP\.)/i', $line)) {
            return true;
        }
        return false;
    }

    /**
     * Normalize name by stripping academic titles, honorifics, dots, commas, and extra spaces.
     */
    public function cleanName(string $name): string
    {
        $clean = strtolower($name);

        // Replace punctuation with space
        $clean = str_replace([',', ';', '(', ')', '[', ']', '-'], ' ', $clean);

        // Normalize spaced titles e.g. "s kom" -> "skom"
        $clean = preg_replace('/\b(s|m)\s+(kom|ti|farm|ak|si|hum|kl|pd|e|t)\b/i', '$1$2', $clean);

        // Remove dots completely
        $clean = str_replace('.', '', $clean);

        // Remove titles as full words
        foreach ($this->titles as $t) {
            $pattern = '/\b' . preg_quote($t, '/') . '\b/i';
            $clean = preg_replace($pattern, ' ', $clean);
        }

        // Expand common Indonesian abbreviations e.g. M. -> Muhammad
        $clean = preg_replace('/\b(m|moh|muh)\b/i', 'muhammad', $clean);

        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    /**
     * Multi-pass smart matching of person name from SK against User records.
     */
    public function matchUser(string $nameSk, $allUsers): ?User
    {
        $cleanSk = $this->cleanName($nameSk);
        if (empty($cleanSk) || strlen($cleanSk) < 3) {
            return null;
        }

        // Pass 1: Exact cleaned match
        foreach ($allUsers as $u) {
            $cleanDb = $this->cleanName($u->name);
            if ($cleanSk === $cleanDb) {
                return $u;
            }
        }

        // Pass 2: Token array exact match
        $skTokens = array_values(array_filter(explode(' ', $cleanSk), fn($w) => strlen($w) >= 3));
        foreach ($allUsers as $u) {
            $cleanDb = $this->cleanName($u->name);
            $dbTokens = array_values(array_filter(explode(' ', $cleanDb), fn($w) => strlen($w) >= 3));
            if ($skTokens === $dbTokens) {
                return $u;
            }
        }

        // Pass 3: Distinctive word match (stripping common honorific/prefix words)
        $skDistinct = array_values(array_filter($skTokens, fn($w) => !in_array($w, ['muhammad', 'andi', 'dr', 'ir'])));
        foreach ($allUsers as $u) {
            $cleanDb = $this->cleanName($u->name);
            $dbDistinct = array_values(array_filter(explode(' ', $cleanDb), fn($w) => !in_array($w, ['muhammad', 'andi', 'dr', 'ir'])));

            if (!empty($skDistinct) && !empty($dbDistinct)) {
                if ($skDistinct === $dbDistinct) {
                    return $u;
                }
                $intersect = array_intersect($skDistinct, $dbDistinct);
                if (count($intersect) >= 2 && count($intersect) === min(count($skDistinct), count($dbDistinct))) {
                    return $u;
                }
            }
        }

        // Pass 4: Fuzzy similarity (threshold 85%)
        $bestUser = null;
        $highestScore = 0;
        foreach ($allUsers as $u) {
            $cleanDb = $this->cleanName($u->name);
            similar_text($cleanSk, $cleanDb, $percent);
            if ($percent > $highestScore && $percent >= 85) {
                $highestScore = $percent;
                $bestUser = $u;
            }
        }

        return $bestUser;
    }
}
