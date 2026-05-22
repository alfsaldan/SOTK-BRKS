<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class SotkExcelImportService
{
    /**
     * Kolom wajib di file Excel (case-insensitive comparison)
     */
    public const REQUIRED_HEADERS = [
        'nik',
        'nama',
        'level_jabatan',
        'jabatan',
        'klasifikasi_jabatan',
        'kode_cabang',
        'unit_kantor',
        'kelas',
        'penempatan',
    ];



    /**
     * Baca dan validasi file Excel, kembalikan array hasil parse.
     *
     * @param UploadedFile $file
     * @return array{
     *   headers_valid: bool,
     *   missing_headers: array,
     *   total_rows: int,
     *   valid_rows: int,
     *   error_rows: int,
     *   rows: array
     * }
     */
    public function parse(UploadedFile $file): array
    {
        // Ambil heading dari baris pertama
        $headings = Excel::toArray(new HeadingRowImport, $file)[0][0] ?? [];
        $normalizedHeadings = array_map(fn($h) => mb_strtolower(trim((string)$h)), $headings);

        // Cek kelengkapan header
        $missingHeaders = [];
        foreach (self::REQUIRED_HEADERS as $required) {
            if (!in_array($required, $normalizedHeadings)) {
                $missingHeaders[] = $required;
            }
        }

        if (!empty($missingHeaders)) {
            return [
                'headers_valid'   => false,
                'missing_headers' => $missingHeaders,
                'total_rows'      => 0,
                'valid_rows'      => 0,
                'error_rows'      => 0,
                'rows'            => [],
            ];
        }

        // Baca semua data (lewati baris header)
        $rawData = Excel::toArray([], $file)[0] ?? [];
        array_shift($rawData); // hapus baris header

        // Map index kolom berdasarkan header
        $colMap = [];
        foreach (self::REQUIRED_HEADERS as $col) {
            $idx = array_search($col, $normalizedHeadings);
            $colMap[$col] = $idx !== false ? $idx : null;
        }

        $rows       = [];
        $validCount = 0;
        $errorCount = 0;
        $nikSeen    = []; // untuk deteksi duplikat NIK dalam satu file

        foreach ($rawData as $rowIndex => $row) {
            $excelRow = $rowIndex + 2; // +2 karena baris 1 = header, index 0-based

            // Skip baris yang benar-benar kosong
            $allEmpty = true;
            foreach ($row as $cell) {
                if (!is_null($cell) && trim((string)$cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            $data   = $this->extractRowData($row, $colMap);
            $errors = $this->validateRowData($data, $nikSeen, $excelRow);

            if (empty($errors)) {
                $validCount++;
                $nikSeen[$data['nik']] = $excelRow;
                $rows[] = [
                    'row_number'          => $excelRow,
                    'nik'                 => $data['nik'],
                    'nama'                => $data['nama'],
                    'level_jabatan'       => $data['level_jabatan'],
                    'jabatan'             => $data['jabatan'],
                    'klasifikasi_jabatan' => $data['klasifikasi_jabatan'],
                    'kode_cabang'         => $data['kode_cabang'],
                    'unit_kantor'         => $data['unit_kantor'],
                    'kelas'               => $data['kelas'],
                    'penempatan'          => $data['penempatan'],
                    'is_valid'            => true,
                    'errors'              => [],
                ];
            } else {
                $errorCount++;
                $rows[] = [
                    'row_number'          => $excelRow,
                    'nik'                 => $data['nik'],
                    'nama'                => $data['nama'],
                    'level_jabatan'       => $data['level_jabatan'],
                    'jabatan'             => $data['jabatan'],
                    'klasifikasi_jabatan' => $data['klasifikasi_jabatan'],
                    'kode_cabang'         => $data['kode_cabang'],
                    'unit_kantor'         => $data['unit_kantor'],
                    'kelas'               => $data['kelas'],
                    'penempatan'          => $data['penempatan'],
                    'is_valid'            => false,
                    'errors'              => $errors,
                ];
            }
        }

        return [
            'headers_valid'   => true,
            'missing_headers' => [],
            'total_rows'      => count($rows),
            'valid_rows'      => $validCount,
            'error_rows'      => $errorCount,
            'rows'            => $rows,
        ];
    }

    /**
     * Ekstrak data dari satu baris berdasarkan peta kolom.
     */
    private function extractRowData(array $row, array $colMap): array
    {
        $get = function (string $col) use ($row, $colMap): string {
            $idx = $colMap[$col] ?? null;
            if ($idx === null) return '';
            return trim((string)($row[$idx] ?? ''));
        };

        $nik = $get('nik');
        if (ctype_digit($nik) && strlen($nik) < 6) {
            $nik = str_pad($nik, 6, '0', STR_PAD_LEFT);
        }

        return [
            'nik'                 => $nik,
            'nama'                => $get('nama'),
            'level_jabatan'       => $get('level_jabatan'),
            'jabatan'             => $get('jabatan'),
            'klasifikasi_jabatan' => $get('klasifikasi_jabatan'),
            'kode_cabang'         => $get('kode_cabang'),
            'unit_kantor'         => $get('unit_kantor'),
            'kelas'               => $get('kelas'),
            'penempatan'          => $get('penempatan'),
        ];
    }

    /**
     * Validasi satu baris data. Kembalikan array pesan error (kosong = valid).
     */
    private function validateRowData(array $data, array $nikSeen, int $rowNum): array
    {
        $errors = [];

        // NIK
        if ($data['nik'] === '') {
            $errors[] = 'NIK wajib diisi';
        } elseif (!ctype_digit($data['nik'])) {
            $errors[] = 'NIK harus berupa angka';
        } elseif (strlen($data['nik']) !== 6) {
            $errors[] = 'NIK harus tepat 6 digit';
        } elseif (isset($nikSeen[$data['nik']])) {
            $errors[] = 'NIK duplikat dengan baris ' . $nikSeen[$data['nik']];
        }

        // Nama
        if ($data['nama'] === '') {
            $errors[] = 'Nama wajib diisi';
        } elseif (mb_strlen($data['nama']) > 150) {
            $errors[] = 'Nama maksimal 150 karakter';
        }

        // Level Jabatan
        if ($data['level_jabatan'] === '') {
            $errors[] = 'Level Jabatan wajib diisi';
        }

        // Jabatan
        if ($data['jabatan'] === '') {
            $errors[] = 'Jabatan wajib diisi';
        }

        // Klasifikasi Jabatan
        if ($data['klasifikasi_jabatan'] === '') {
            $errors[] = 'Klasifikasi Jabatan wajib diisi';
        }

        // Kode Cabang
        if ($data['kode_cabang'] === '') {
            $errors[] = 'Kode Cabang wajib diisi';
        } elseif (strlen($data['kode_cabang']) > 20) {
            $errors[] = 'Kode Cabang maksimal 20 karakter';
        }

        // Unit Kantor
        if ($data['unit_kantor'] === '') {
            $errors[] = 'Unit Kantor wajib diisi';
        }

        // Kelas
        if ($data['kelas'] === '') {
            $errors[] = 'Kelas wajib diisi';
        }

        // Penempatan
        if ($data['penempatan'] === '') {
            $errors[] = 'Penempatan wajib diisi';
        }

        return $errors;
    }
}
