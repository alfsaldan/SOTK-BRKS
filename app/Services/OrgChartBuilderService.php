<?php

namespace App\Services;

use App\Models\SotkMaster;
use Illuminate\Support\Collection;

class OrgChartBuilderService
{
    const LEVEL_RANK = [
        // ── Level 1: Board / Komisaris / C-Level ──────────────────
        'komisaris utama'             => 1,
        'komisaris independen'        => 1,
        'komisaris'                   => 1,
        'dewan pengawas syariah'      => 1,
        'dewan pengawas'              => 1,
        'direktur utama'              => 1,
        'plt. direktur utama'         => 1,
        'plt direktur utama'          => 1,
        'wakil direktur utama'        => 1,
        'komite'                      => 1,

        // ── Level 2: Direktur / Branch Head ───────────────────────
        'direktur'                    => 2,
        'general manager'             => 2,
        'branch manager'              => 2,
        'pemimpin cabang pembantu'    => 2,
        'pemimpin cabang'             => 2,
        'pemimpin kedai'              => 2,
        'pincab'                      => 2,
        'pincapem'                    => 2,

        // ── Level 3: Pemimpin Divisi / Koordinator ─────────────────
        'pemimpin divisi'             => 3,
        'pindiv'                      => 3,
        'pemimpin bagian cabang'      => 3,
        'koordinator penyelesaian'    => 3,
        'koordinator dana'            => 3,
        'koordinator'                 => 3,
        'ketua tim khusus'            => 3,
        'ketua desk'                  => 3,
        'ketua tim'                   => 3,

        // ── Level 4: Pemimpin Bagian / Supervisor / Team Leader ────
        'pemimpin bagian'             => 4,
        'pinbag'                      => 4,
        'manager operasional'         => 4,
        'manager bisnis'              => 4,
        'manager'                     => 4,
        'supervisor administrasi'     => 4,
        'supervisor kas'              => 4,
        'supervisor layanan'          => 4,
        'supervisor operasional'      => 4,
        'supervisor'                  => 4,
        'team leader'                 => 4,
        'head teller'                 => 4,
        'kepala kantor kas'           => 4,
        'pemimpin kas'                => 4,
        'kepala unit'                 => 4,
        'kepala'                      => 4,
        'penyelia'                    => 4,
        'auditor utama'               => 4,

        // ── Level 5: Staf / Officer / Executing Staff ────────────────────────
        'staf ahli'                   => 5,   // fungsional – handled separately
        'staf khusus'                 => 5,
        'staf financing'              => 5,
        'staf bagian'                 => 5,
        'staf quality'                => 5,
        'staf'                        => 5,
        'auditor bagian'              => 5,
        'auditor'                     => 5,
        'pejabat'                     => 5,
        'analis'                      => 5,
        'petugas'                     => 5,
        'pegawai divisi'              => 5,   // oval – handled separately
        'pegawai'                     => 5,
        'account officer'             => 5,
        'funding officer'             => 5,
        'priority banking officer'    => 5,
        'pbo'                         => 5,
        'teller'                      => 5,
        'customer service'            => 5,
        'support assistance'          => 5,
        'administrasi pembiayaan'     => 5,
        'administrasi'                => 5,
        'anggota tim'                 => 5,
        // Jabatan non-organik (rank 5 – Umum)
        'satpam'                      => 5,
        'cleaning service'            => 5,
        'sopir'                       => 5,
        'driver'                      => 5,
        'pengantar surat'             => 5,
        'penjaga malam'               => 5,
        'arsiptaris'                  => 5,

        // ── Level 6: Pelaksana / Pgs. Staf / PTT – Sub-staff (di bawah Staf) ───
        // NOTE: 'pgs. staf' entries MUST appear in LEVEL_RANK before generic 'staf'
        // because getRank() uses longest-key-first matching (see getRank implementation)
        'pelaksana bagian'            => 6,
        'pelaksana'                   => 6,
        'pgs. staf bagian'            => 6,   // Acting staf – below Bagian (not below a specific Staf)
        'pgs. staf'                   => 6,
        'ptt bagian'                  => 6,
        'ptt'                         => 6,
    ];

    const NODE_STYLES = [
        1 => ['type' => 'direktur_utama', 'bg' => '#a00000', 'color' => '#ffffff', 'border' => '#5e0000', 'accent' => '#f2b705', 'shadow' => false],
        2 => ['type' => 'direktur',       'bg' => '#a00000', 'color' => '#ffffff', 'border' => '#5e0000', 'accent' => '#f2b705', 'shadow' => false],
        3 => ['type' => 'divisi',         'bg' => '#f2cc5c', 'color' => '#000000', 'border' => '#f2cc5c', 'accent' => '#a00000', 'shadow' => false],
        4 => ['type' => 'bagian',         'bg' => '#fff5cc', 'color' => '#000000', 'border' => '#000000', 'accent' => '#a00000', 'shadow' => false],
        5 => ['type' => 'staf',           'bg' => '#ffffff', 'color' => '#000000', 'border' => '#000000', 'accent' => '#a00000', 'shadow' => false],
        6 => ['type' => 'pelaksana',      'bg' => '#ffffff', 'color' => '#000000', 'border' => '#aaaaaa', 'accent' => 'rgba(191, 97, 97, 1)', 'shadow' => false],
    ];

    // Warna oval Pegawai Divisi (hijau muda) & Fungsional (biru-hijau)
    const OVAL_PEGAWAI_DIVISI = ['bg' => '#d1fae5', 'color' => '#065f46', 'border' => '#6ee7b7'];
    const OVAL_FUNGSIONAL     = ['bg' => '#dbeafe', 'color' => '#1e3a8a', 'border' => '#93c5fd'];

    /**
     * Build org chart node tree for given period + unit filter.
     */
    public function build(int $periodId, ?string $unitKantor = null): array
    {
        $query = SotkMaster::where('period_id', $periodId);

        if ($unitKantor) {
            $query->where('unit_kantor', $unitKantor);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return [];
        }

        // Assign rank to each row
        $rows = $rows->map(function ($row) {
            $row->_rank = $this->getRank($row->level_jabatan);
            return $row;
        })->sortBy('_rank');

        return $this->buildFlat($rows);
    }

    /**
     * Tentukan apakah baris ini adalah "Pegawai Divisi" (termasuk MPP)
     * Klasifikasi: jabatan mengandung "mpp" ATAU "pegawai divisi"
     */
    private function isPegawaiDivisi($row): bool
    {
        $jabLow   = strtolower((string)$row->jabatan);
        $levelLow = strtolower((string)$row->level_jabatan);
        $klasLow  = strtolower((string)($row->klasifikasi_jabatan ?? ''));

        return str_contains($jabLow, 'mpp')
            || str_contains($levelLow, 'mpp')
            || str_contains($jabLow, 'pegawai divisi')
            || str_contains($levelLow, 'pegawai divisi')
            || str_contains($klasLow, 'pegawai divisi');
    }

    /**
     * Tentukan apakah baris ini adalah Fungsional/Widyaiswara
     */
    private function isFungsional($row): bool
    {
        $jabLow   = strtolower((string)$row->jabatan);
        $levelLow = strtolower((string)$row->level_jabatan);
        $klasLow  = strtolower((string)($row->klasifikasi_jabatan ?? ''));

        return str_contains($jabLow, 'widyaiswara')
            || str_contains($levelLow, 'widyaiswara')
            || str_contains($klasLow, 'widyaiswara')
            || str_contains($jabLow, 'staf ahli')
            || str_contains($levelLow, 'staf ahli')
            || (str_contains($jabLow, 'fungsional') && !str_contains($jabLow, 'jabatan fungsional'))
            || str_contains($levelLow, 'fungsional');
    }

    /**
     * Cek apakah unit ini adalah Divisi (bukan cabang/capem/kedai)
     */
    private function isDivisiUnit(Collection $rows): bool
    {
        foreach ($rows as $row) {
            $lv = strtolower((string)$row->level_jabatan);
            if (str_contains($lv, 'pemimpin divisi') || str_contains($lv, 'pindiv')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build a flat array of nodes with pid references (for d3-org-chart).
     */
    private function buildFlat(Collection $rows): array
    {
        $nodes = [];

        // Group rows by unit_kantor
        $byUnit = $rows->groupBy('unit_kantor');

        // First pass: create a node per employee
        foreach ($rows as $row) {
            $isMpp         = str_contains(strtolower((string)$row->jabatan), 'mpp')
                          || str_contains(strtolower((string)$row->level_jabatan), 'mpp');
            $isPegDiv      = $this->isPegawaiDivisi($row);
            $isFung        = $this->isFungsional($row);

            // Tentukan style dasar
            $style = self::NODE_STYLES[$row->_rank] ?? self::NODE_STYLES[5];

            // Override style untuk oval nodes
            if ($isPegDiv) {
                $style = array_merge($style, self::OVAL_PEGAWAI_DIVISI);
            } elseif ($isFung) {
                $style = array_merge($style, self::OVAL_FUNGSIONAL);
            }

            $nodes['emp_' . $row->id] = [
                'id'                  => 'emp_' . $row->id,
                'parentId'            => null,
                'rank'                => $row->_rank,
                'type'                => $style['type'],
                'bg'                  => $style['bg'],
                'color'               => $style['color'],
                'border'              => $style['border'],
                'accent'              => $style['accent'],
                'shadow'              => $style['shadow'] ?? false,
                'nik'                 => $row->nik,
                'nama'                => $row->nama,
                'level_jabatan'       => $row->level_jabatan,
                'jabatan'             => $row->jabatan,
                'klasifikasi_jabatan' => $row->klasifikasi_jabatan,
                'kode_cabang'         => $row->kode_cabang,
                'unit_kantor'         => $row->unit_kantor,
                'kelas'               => $row->kelas,
                'penempatan'          => $row->penempatan,
                'is_vacant'           => false,
                'is_mpp'              => $isMpp,
                'is_pegawai_divisi'   => $isPegDiv,
                'is_fungsional'       => $isFung,
                // Oval shape flag: dua klasifikasi khusus menggunakan oval
                'node_shape'          => ($isPegDiv || $isFung) ? 'oval' : 'rect',
                // Label tampil di card: override untuk kedua klasifikasi khusus
                'display_label'       => $isPegDiv ? 'Pegawai Divisi' : ($isFung ? 'Fungsional' : null),
                'connection_type'     => ($isPegDiv || $isFung) ? 'dashed' : 'solid',
            ];
        }

        // Second pass: assign parent-child within each unit cluster
        foreach ($byUnit as $unit => $unitRows) {
            $unitRows = $unitRows->sortBy('_rank');
            $nodesByRank = []; // rank => array of rows

            // Pisahkan oval khusus dari nodesByRank biasa
            $specialRows = []; // pegawai divisi & fungsional
            foreach ($unitRows as $row) {
                if ($this->isPegawaiDivisi($row) || $this->isFungsional($row)) {
                    $specialRows[] = $row;
                } else {
                    $nodesByRank[$row->_rank][] = $row;
                }
            }

            // Temukan pimpinan divisi (rank 3) atau pimpinan tertinggi di unit ini
            $divLeaderId = null;
            if (!empty($nodesByRank[3])) {
                $divLeaderId = 'emp_' . $nodesByRank[3][0]->id;
            } elseif (!empty($nodesByRank[2])) {
                $divLeaderId = 'emp_' . $nodesByRank[2][0]->id;
            } elseif (!empty($nodesByRank[1])) {
                $divLeaderId = 'emp_' . $nodesByRank[1][0]->id;
            }

            // Assign parent untuk special (oval) nodes langsung ke pimpinan divisi
            foreach ($specialRows as $row) {
                $nodeId = 'emp_' . $row->id;
                $nodes[$nodeId]['parentId'] = $divLeaderId;
            }

            // Assign parent untuk non-special nodes
            $nonSpecialRows = collect(array_merge(...array_values($nodesByRank)));
            foreach ($nonSpecialRows as $row) {
                $nodeId = 'emp_' . $row->id;
                $rank   = $row->_rank;

                // Find parent (look upward in rank)
                // RULE: rank-6 nodes (Pelaksana/PTT/Pgs.Staf) skip rank-5 (Staf)
                // and connect directly to rank-4 (Pemimpin Bagian) as their group parent.
                // This avoids false supervisor–subordinate lines between individual Staf and Pelaksana.
                $parentId  = null;
                $startRank = ($rank === 6) ? $rank - 2 : $rank - 1; // rank6→start at 4; others→rank-1
                for ($r = $startRank; $r >= 1; $r--) {
                    if (!empty($nodesByRank[$r])) {
                        if (count($nodesByRank[$r]) === 1) {
                            $parentId = 'emp_' . $nodesByRank[$r][0]->id;
                            break;
                        }

                        // ── Token-Similarity Matching (bilingual, auto-scalable) ─
                        // Score every candidate parent by domain-token overlap.
                        $bestMatch = null;
                        $bestScore = 0.0;

                        foreach ($nodesByRank[$r] as $parentRow) {
                            $score = $this->jabatanSimilarity($row->jabatan, $parentRow->jabatan);
                            if ($score > $bestScore) {
                                $bestScore = $score;
                                $bestMatch = 'emp_' . $parentRow->id;
                            }
                        }

                        // ── Accept if similarity is meaningful (>= 10%) ───────────
                        if ($bestScore >= 0.10 && $bestMatch) {
                            $parentId = $bestMatch;
                            break;
                        }

                        // ── Skip-and-escalate: try a higher structural rank first ─
                        // If there's a higher rank available, don't force-assign here.
                        // This lets orphan jabatan (Teller in Divisi SDI) escalate
                        // all the way up to the Divisi head instead of a random Bagian.
                        $hasHigherNonEmpty = false;
                        for ($rr = $r - 1; $rr >= 1; $rr--) {
                            if (!empty($nodesByRank[$rr])) {
                                $hasHigherNonEmpty = true;
                                break;
                            }
                        }
                        if ($hasHigherNonEmpty) {
                            // Skip this rank — loop continues upward
                            continue;
                        }

                        // ── Absolute last resort: first non-MPP at this rank ──────
                        $fallbackParent = null;
                        foreach ($nodesByRank[$r] as $p) {
                            $pjLow = strtolower($p->jabatan);
                            if (!str_contains($pjLow, 'mpp') && !str_contains($pjLow, 'pegawai divisi')) {
                                $fallbackParent = $p;
                                break;
                            }
                        }
                        if (!$fallbackParent) $fallbackParent = $nodesByRank[$r][0];
                        $parentId = 'emp_' . $fallbackParent->id;
                        break;
                    }
                }
                $nodes[$nodeId]['parentId'] = $parentId;
            }

            // Add vacant nodes where expected levels are missing
            $regularRanks = collect(array_keys($nodesByRank));

            // If rank-3 (divisi head) exists but no rank-4 (bagian), add vacant bagian
            if ($regularRanks->contains(3) && !$regularRanks->contains(4)) {
                $divisiHead = collect($nodesByRank[3])->first();
                $vacantId = 'vacant_bagian_' . md5($unit);
                $style = self::NODE_STYLES[4];
                $nodes[$vacantId] = $this->makeVacant($vacantId, 'emp_' . $divisiHead->id, 4, $style, 'Pemimpin Bagian', $unit);
            }

            // If rank-4 exists but no rank-5 (staf), add vacant staf
            if ($regularRanks->contains(4) && !$regularRanks->contains(5)) {
                $bagianHead = collect($nodesByRank[4])->first();
                $vacantId = 'vacant_staf_' . md5($unit);
                $style = self::NODE_STYLES[5];
                $nodes[$vacantId] = $this->makeVacant($vacantId, 'emp_' . $bagianHead->id, 5, $style, 'Staf', $unit);
            }
        }

        $nodes = array_values($nodes);

        // Cek apakah semua root adalah unit divisi — jika ya, TIDAK perlu virtual root navy biru
        $rootNodes = array_filter($nodes, fn($n) => $n['parentId'] === null);

        if (count($rootNodes) > 1) {
            // Cek apakah ini adalah divisi (ada rank 3 di root nodes)
            $isDivisi = false;
            foreach ($rootNodes as $rn) {
                if ($rn['rank'] === 3) {
                    $isDivisi = true;
                    break;
                }
            }

            if (!$isDivisi) {
                // Bukan divisi: buat virtual root untuk menyatukan
                $firstUnit    = $rows->first()->unit_kantor ?? 'Unit';
                $virtualRootId = 'virtual_root_' . md5(uniqid());
                $virtualRoot  = $this->makeVacant($virtualRootId, null, 0, self::NODE_STYLES[1], 'Kepala ' . $firstUnit, $firstUnit);
                $virtualRoot['nama']     = $firstUnit;
                $virtualRoot['bg']       = '#1e293b';
                $virtualRoot['color']    = '#ffffff';
                $virtualRoot['border']   = '#0f172a';
                $virtualRoot['accent']   = '#3b82f6';
                $virtualRoot['is_vacant'] = false;
                
                $nodes[] = $virtualRoot;
                
                foreach ($nodes as &$n) {
                    if ($n['parentId'] === null && $n['id'] !== $virtualRootId) {
                        $n['parentId'] = $virtualRootId;
                    }
                }
            }
            // Jika divisi: biarkan saja, tidak perlu virtual root
        }

        return $nodes;
    }

    private function makeVacant(string $id, ?string $parentId, int $rank, array $style, string $label, string $unit): array
    {
        return [
            'id'                  => $id,
            'parentId'            => $parentId,
            'rank'                => $rank,
            'type'                => $style['type'],
            'bg'                  => '#ffffff',
            'color'               => '#666666',
            'border'              => '#cccccc',
            'accent'              => '#cccccc',
            'shadow'              => false,
            'nik'                 => '',
            'nama'                => '— Belum Terisi —',
            'level_jabatan'       => $label,
            'jabatan'             => $label,
            'klasifikasi_jabatan' => '',
            'kode_cabang'         => '',
            'unit_kantor'         => $unit,
            'kelas'               => '',
            'penempatan'          => '',
            'is_vacant'           => true,
            'is_mpp'              => false,
            'is_pegawai_divisi'   => false,
            'is_fungsional'       => false,
            'node_shape'          => 'rect',
            'display_label'       => null,
            'connection_type'     => 'solid',
        ];
    }

    public function getRank(string $levelJabatan): int
    {
        $lower = mb_strtolower(trim($levelJabatan));

        // Sort by key length DESC so more specific entries win over generic ones.
        // e.g. 'pgs. staf bagian' (rank 6) is found before 'staf bagian' (rank 5).
        $sorted = self::LEVEL_RANK;
        uksort($sorted, static fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($sorted as $key => $rank) {
            if (str_contains($lower, $key)) {
                return $rank;
            }
        }
        return 5;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TOKEN-SIMILARITY ENGINE
    //  Matches parent↔child jabatan using bilingual domain-vocabulary tokens.
    //  Future-proof: add new job titles freely — only update SYNONYM_MAP
    //  when a truly NEW word/language appears.
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Banking-domain synonym/normalization map.
     * Each entry maps a token to its canonical set (EN + ID equivalents).
     * → To support a new language/term: just add an entry here. No other code changes needed.
     */
    private const SYNONYM_MAP = [
        // ── IT ──────────────────────────────────────────────────────────────
        'it'              => ['it', 'ti', 'teknologi'],
        'ti'              => ['ti', 'it', 'teknologi'],
        'teknologi'       => ['teknologi', 'it', 'ti', 'technology'],
        'technology'      => ['technology', 'teknologi', 'ti', 'it'],
        'infrastruktur'   => ['infrastruktur', 'infrastructure', 'keamanan'],
        'infrastructure'  => ['infrastructure', 'infrastruktur'],
        'security'        => ['security', 'keamanan'],
        'keamanan'        => ['keamanan', 'security'],
        'development'     => ['development', 'pengembangan'],
        'pengembangan'    => ['pengembangan', 'development'],
        'operation'       => ['operation', 'operasional'],
        'operations'      => ['operations', 'operasional'],
        'operational'     => ['operational', 'operasional'],
        'operasional'     => ['operasional', 'operational', 'operation'],
        'planning'        => ['planning', 'perencanaan', 'tata kelola'],
        'perencanaan'     => ['perencanaan', 'planning'],
        'assurance'       => ['assurance', 'penjaminan'],
        'monitoring'      => ['monitoring', 'pemantauan'],
        'network'         => ['network', 'jaringan'],
        'jaringan'        => ['jaringan', 'network'],
        'sistem'          => ['sistem', 'system', 'it'],
        'system'          => ['system', 'sistem'],
        'digital'         => ['digital'],
        // ── Finance / Banking ────────────────────────────────────────────────
        'financing'       => ['financing', 'pembiayaan'],
        'pembiayaan'      => ['pembiayaan', 'financing'],
        'funding'         => ['funding', 'dana', 'pendanaan'],
        'dana'            => ['dana', 'funding'],
        'treasury'        => ['treasury', 'perbendaharaan'],
        'akuntansi'       => ['akuntansi', 'accounting'],
        'accounting'      => ['accounting', 'akuntansi'],
        'asuransi'        => ['asuransi', 'insurance'],
        'insurance'       => ['insurance', 'asuransi'],
        'retail'          => ['retail', 'ritel'],
        'ritel'           => ['ritel', 'retail'],
        'priority'        => ['priority', 'prioritas'],
        'prioritas'       => ['prioritas', 'priority'],
        // ── Business ────────────────────────────────────────────────────────
        'bisnis'          => ['bisnis', 'business'],
        'business'        => ['business', 'bisnis'],
        'komersial'       => ['komersial', 'commercial'],
        'commercial'      => ['commercial', 'komersial'],
        'konsumer'        => ['konsumer', 'consumer'],
        'consumer'        => ['consumer', 'konsumer'],
        'sindikasi'       => ['sindikasi', 'syndication'],
        'corporate'       => ['corporate', 'korporasi', 'perusahaan'],
        'korporasi'       => ['korporasi', 'corporate'],
        // ── Risk & Compliance ────────────────────────────────────────────────
        'risiko'          => ['risiko', 'risk'],
        'risk'            => ['risk', 'risiko'],
        'kepatuhan'       => ['kepatuhan', 'compliance'],
        'compliance'      => ['compliance', 'kepatuhan'],
        'audit'           => ['audit'],
        'fraud'           => ['fraud', 'kecurangan'],
        'aml'             => ['aml', 'anti money laundering', 'pencucian uang'],
        // ── HR / SDI ────────────────────────────────────────────────────────
        'sdi'             => ['sdi', 'sdm', 'sumber daya insani', 'human resources'],
        'sdm'             => ['sdm', 'sdi', 'sumber daya'],
        'insani'          => ['insani', 'sdi', 'human'],
        'human'           => ['human', 'insani', 'sdi'],
        'administrasi'    => ['administrasi', 'administration', 'admin'],
        'administration'  => ['administration', 'administrasi'],
        'admin'           => ['admin', 'administrasi'],
        'learning'        => ['learning', 'pembelajaran', 'pelatihan'],
        'pembelajaran'    => ['pembelajaran', 'learning'],
        // ── Operations / Service ────────────────────────────────────────────
        'layanan'         => ['layanan', 'service'],
        'service'         => ['service', 'layanan'],
        'umum'            => ['umum', 'general'],
        'general'         => ['general', 'umum'],
        'pemeliharaan'    => ['pemeliharaan', 'maintenance'],
        'maintenance'     => ['maintenance', 'pemeliharaan'],
        'sentral'         => ['sentral', 'central'],
        'central'         => ['central', 'sentral'],
        // ── Legal ────────────────────────────────────────────────────────────
        'hukum'           => ['hukum', 'legal'],
        'legal'           => ['legal', 'hukum'],
        'litigasi'        => ['litigasi', 'litigation'],
        'litigation'      => ['litigation', 'litigasi'],
        // ── International / Treasury ─────────────────────────────────────────
        'international'   => ['international', 'internasional'],
        'internasional'   => ['internasional', 'international'],
        // ── Asset Management ─────────────────────────────────────────────────
        'asset'           => ['asset', 'aset'],
        'aset'            => ['aset', 'asset'],
        'penyelesaian'    => ['penyelesaian', 'settlement'],
        'settlement'      => ['settlement', 'penyelesaian'],
        'penyelamatan'    => ['penyelamatan', 'rescue', 'recovery'],
        // ── Communication / Secretariat ──────────────────────────────────────
        'komunikasi'      => ['komunikasi', 'communication'],
        'communication'   => ['communication', 'komunikasi'],
        'sekretariat'     => ['sekretariat', 'secretariat'],
        'kesekretariatan' => ['kesekretariatan', 'secretariat'],
        'protokoler'      => ['protokoler', 'protocol'],
        'investor'        => ['investor'],
        'relation'        => ['relation', 'hubungan', 'relasi'],
        'hubungan'        => ['hubungan', 'relation'],
        // ── MKM ──────────────────────────────────────────────────────────────
        'mkm'             => ['mkm', 'mikro', 'kecil', 'menengah'],
        'mikro'           => ['mikro', 'mkm'],
        // ── Quality ──────────────────────────────────────────────────────────
        'quality'         => ['quality', 'mutu', 'kualitas'],
        'mutu'            => ['mutu', 'quality'],
        'kualitas'        => ['kualitas', 'quality'],
        'collateral'      => ['collateral', 'agunan', 'jaminan'],
        // ── Payment / E-channel ───────────────────────────────────────────────
        'payment'         => ['payment', 'pembayaran'],
        'pembayaran'      => ['pembayaran', 'payment'],
        'echannel'        => ['echannel', 'e-channel', 'elektronik'],
        // ── Syariah ──────────────────────────────────────────────────────────
        'syariah'         => ['syariah', 'sharia'],
        'sharia'          => ['sharia', 'syariah'],
    ];

    /** Positional prefixes stripped before domain-token extraction (longest first). */
    private const STRIP_PREFIXES = [
        'pgs. pemimpin bagian ', 'pjs. pemimpin bagian ', 'pgs. pemimpin divisi ', 'pjs. pemimpin divisi ',
        'pemimpin bagian ', 'pemimpin divisi ', 'pemimpin cabang pembantu ', 'pemimpin cabang ',
        'pgs. pemimpin kedai ', 'pgs. staf bagian ', 'pgs. pelaksana bagian ',
        'pgs. supervisor ', 'pgs. team leader ', 'pgs. branch manager ',
        'pgs. bagian ', 'pjs. bagian ',
        'kepala bagian ', 'kepala unit ', 'kepala ',
        'team leader pembiayaan produktif merangkap pembiayaan konsumer ',
        'team leader pembiayaan produktif ', 'team leader pembiayaan konsumer ',
        'team leader dana ', 'team leader bisnis ', 'team leader ',
        'supervisor administrasi ', 'supervisor layanan ', 'supervisor operasional ',
        'supervisor kas ', 'supervisor ',
        'manager operasional ', 'manager bisnis ', 'manager ',
        'staf bagian ', 'staf financing ', 'staf khusus ', 'staf quality ', 'staf ',
        'pelaksana bagian ', 'pelaksana ',
        'auditor bagian ', 'auditor ',
        'account officer konsumer ', 'account officer mkm ', 'account officer komersial ',
        'account officer produktif ', 'account officer rahn ', 'account officer ',
        'funding officer ', 'support assistance ', 'customer service ',
        'administrasi pembiayaan ', 'administrasi ',
        'anggota tim khusus ', 'anggota tim ', 'ketua tim khusus ', 'ketua tim ',
        'ketua desk ', 'koordinator ',
        'ptt bagian ', 'ptt ',
        'pgs. ', 'pjs. ', 'plt. ',
        'head teller ', 'teller ',
    ];

    /**
     * Tokenize a jabatan string into a normalized, synonym-expanded set of domain tokens.
     * Used by jabatanSimilarity() for score-based parent matching.
     */
    private function tokenizeJabatan(string $jabatan): array
    {
        $s = mb_strtolower(trim($jabatan));

        // 1. Strip positional prefix (longest match first)
        foreach (self::STRIP_PREFIXES as $prefix) {
            if (str_starts_with($s, $prefix)) {
                $s = substr($s, strlen($prefix));
                break;
            }
        }

        // 2. Normalize punctuation/special chars to spaces
        $s = str_replace(['&', '(', ')', ',', '/', '\\', '"', "'", '.'], ' ', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));

        // 3. Tokenize on whitespace
        $tokens = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);

        // 4. Remove semantic stopwords that carry no domain meaning
        $stop = ['dan', 'the', 'of', 'and', 'or', 'untuk', 'dengan', 'bagian', 'divisi',
                 'serta', 'antara', 'dalam', 'pada', 'ke', 'di', 'yang', 'atas'];
        $tokens = array_diff($tokens, $stop);

        // 5. Expand via synonym map (bidirectional)
        $expanded = [];
        foreach ($tokens as $token) {
            $expanded[] = $token;
            if (isset(self::SYNONYM_MAP[$token])) {
                foreach (self::SYNONYM_MAP[$token] as $syn) {
                    $expanded[] = $syn;
                }
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * Compute Jaccard token-overlap similarity between two jabatan strings.
     * Returns 0.0 (no overlap) to 1.0 (identical token sets).
     *
     * Example:
     *   "Pemimpin Bagian Infrastruktur & Keamanan TI" → tokens: {infrastruktur, keamanan, ti, it, security}
     *   "Staf Bagian IT Security"                    → tokens: {it, ti, security, keamanan}
     *   Jaccard = |{it, security, ti, keamanan}| / |{infrastruktur, keamanan, ti, it, security}| = 4/5 = 0.80 ✓
     */
    private function jabatanSimilarity(string $jabatanA, string $jabatanB): float
    {
        $a = $this->tokenizeJabatan($jabatanA);
        $b = $this->tokenizeJabatan($jabatanB);

        if (empty($a) || empty($b)) return 0.0;

        $intersection = count(array_intersect($a, $b));
        $union        = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? round($intersection / $union, 4) : 0.0;
    }

    /**
     * Get all unique unit_kantor for a period, grouped by kelas.
     */
    public function getUnitsByPeriod(int $periodId): array
    {
        $rows = SotkMaster::where('period_id', $periodId)
            ->select('unit_kantor', 'kelas')
            ->distinct()
            ->orderBy('kelas')
            ->orderBy('unit_kantor')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $kelas = $row->kelas ?: 'Lainnya';
            $grouped[$kelas][] = $row->unit_kantor;
        }
        return $grouped;
    }
}
