<?php

namespace App\Helpers;

use Carbon\Carbon;

class MilestoneHelper
{
    /**
     * Render HTML card widget for Milestone Points formatted as a Horizontal Timeline Stepper (Left to Right).
     */
    public static function renderWidget($milestones, $milestonableId, $milestonableType)
    {
        $totalMilestones = $milestones->count();
        $selesaiMilestones = $milestones->where('status', 'Selesai')->count();

        // Calculate overall total active seconds
        $totalDetikAll = $milestones->sum(function ($m) {
            return $m->total_durasi_detik;
        });

        $html = '<div class="milestone-widget-container mt-2 p-2 px-3 rounded-2 bg-white border shadow-xs w-100" style="border-left: 4px solid #15432d !important; max-width: 100%; box-sizing: border-box;" data-task-id="' . $milestonableId . '" data-task-type="' . e($milestonableType) . '">';
        
        // Header Row: Title, Progress & Total Waktu
        $html .= '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-1 border-bottom">';
        $html .= '<div class="d-flex align-items-center gap-2">';
        $html .= '<span class="fw-bold text-dark small"><i class="bi bi-diagram-3-fill text-success me-1"></i>Timeline Milestone Kinerja</span>';
        
        if ($totalMilestones > 0) {
            $percent = round(($selesaiMilestones / $totalMilestones) * 100);
            $html .= '<span class="badge px-2 py-1" style="background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; font-weight: 600; font-size: 0.74rem;"><i class="bi bi-check2-circle me-1"></i>' . $selesaiMilestones . '/' . $totalMilestones . ' Point Selesai (' . $percent . '%)</span>';
        } else {
            $html .= '<span class="badge bg-light text-muted border" style="font-size: 0.72rem;">Belum ada point</span>';
        }
        
        $html .= '</div>';

        // Overall Total Duration Badge
        $html .= '<div class="d-flex align-items-center gap-2">';
        $html .= '<span class="badge bg-dark text-white px-2 py-1 overall-milestone-timer" style="font-size: 0.74rem;" data-total-detik="' . $totalDetikAll . '"><i class="bi bi-stopwatch-fill me-1 text-warning"></i>Total Waktu: <span class="overall-timer-text">' . self::formatDuration($totalDetikAll) . '</span></span>';
        $html .= '</div>';
        $html .= '</div>'; // end header row

        // Horizontal Timeline Stepper (Left to Right) - Compact Responsive Layout (No ScrollX)
        if ($totalMilestones > 0) {
            $html .= '<div class="horizontal-milestone-wrapper position-relative py-1 w-100">';
            $html .= '<div class="horizontal-timeline d-flex flex-wrap align-items-start gap-2 position-relative px-1 py-1 w-100">';

            $idx = 1;
            foreach ($milestones as $m) {
                // Status configurations for node and badge
                $nodeBg = '#ffffff';
                $nodeColor = '#64748b';
                $nodeRing = '#cbd5e1';
                $nodeIcon = 'bi-circle-fill';
                $badgeStyle = 'background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                $statusBadgeText = $m->status;

                if ($m->status === 'Selesai') {
                    $nodeBg = '#15432d';
                    $nodeColor = '#ffffff';
                    $nodeRing = '#a7f3d0';
                    $nodeIcon = 'bi-check-lg';
                    $badgeStyle = 'background-color: #dcfce7; color: #15803d; border: 1px solid #86efac;';
                    $statusBadgeText = '✔ SELESAI';
                } elseif ($m->status === 'Berjalan') {
                    $nodeBg = '#0284c7';
                    $nodeColor = '#ffffff';
                    $nodeRing = '#bae6fd';
                    $nodeIcon = 'bi-play-fill';
                    $badgeStyle = 'background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;';
                    $statusBadgeText = '▶ BERJALAN';
                } elseif ($m->status === 'Di-pause') {
                    $nodeBg = '#d97706';
                    $nodeColor = '#ffffff';
                    $nodeRing = '#fef3c7';
                    $nodeIcon = 'bi-pause-fill';
                    $badgeStyle = 'background-color: #fef3c7; color: #b45309; border: 1px solid #fde047;';
                    $statusBadgeText = '⏸ DI-PAUSE';
                }

                $lastStartedTs = ($m->status === 'Berjalan' && $m->last_started_at) ? $m->last_started_at->timestamp : 0;
                $baseDetik = $m->durasi_detik ?? 0;
                $totalDetik = $m->total_durasi_detik;

                $html .= '<div class="horizontal-step-item position-relative d-flex flex-column align-items-center milestone-item" style="flex: 1 1 170px; min-width: 160px; max-width: 220px; z-index: 2;" ';
                $html .= 'data-milestone-id="' . $m->id . '" ';
                $html .= 'data-status="' . $m->status . '" ';
                $html .= 'data-base-detik="' . $baseDetik . '" ';
                $html .= 'data-last-started-ts="' . $lastStartedTs . '">';

                // Node Circle Dot (Centered on top of horizontal line)
                $html .= '<div class="step-node rounded-circle d-flex align-items-center justify-content-center shadow-2xs mb-2" style="width: 32px; height: 32px; background-color: ' . $nodeBg . '; color: ' . $nodeColor . '; border: 2px solid #ffffff; box-shadow: 0 0 0 2px ' . $nodeRing . '; font-size: 0.82rem; font-weight: bold; transition: all 0.2s;">';
                if ($m->status === 'Belum Dimulai') {
                    $html .= '<span style="font-size: 0.78rem;">' . $idx . '</span>';
                } else {
                    $html .= '<i class="bi ' . $nodeIcon . '"></i>';
                }
                $html .= '</div>';

                // Step Content Card Box (Placed below node dot)
                $html .= '<div class="step-card bg-white p-2 rounded-2 border shadow-2xs w-100 position-relative d-flex flex-column justify-content-between" style="min-height: 140px;">';
                
                // Card Header: Step Index & Status Badge
                $html .= '<div>';
                $html .= '<div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-1">';
                $html .= '<span class="badge bg-dark text-white fw-bold" style="font-size: 0.68rem;">Point #' . $idx . '</span>';
                $html .= '<span class="badge milestone-status-badge px-1 py-0 fw-bold" style="font-size: 0.68rem; ' . $badgeStyle . '">' . e($statusBadgeText) . '</span>';
                $html .= '</div>';

                // Milestone Name
                $html .= '<h6 class="fw-bold text-dark mb-1 text-break" style="font-size: 0.84rem; line-height: 1.25;">' . e($m->nama_milestone) . '</h6>';

                // Timestamps Info
                $html .= '<div class="small text-secondary mb-1" style="font-size: 0.72rem; line-height: 1.2;">';
                if ($m->waktu_mulai) {
                    $html .= '<div><i class="bi bi-play-circle text-primary me-1"></i><strong>Mulai:</strong> ' . $m->waktu_mulai->format('d/m/Y H:i') . '</div>';
                }
                if ($m->waktu_selesai) {
                    $html .= '<div><i class="bi bi-check-circle text-success me-1"></i><strong>Selesai:</strong> ' . $m->waktu_selesai->format('d/m/Y H:i') . '</div>';
                }
                if (!$m->waktu_mulai && !$m->waktu_selesai) {
                    $html .= '<div class="fst-italic text-muted"><i class="bi bi-clock me-1"></i>Belum dimulai</div>';
                }
                if ($m->catatan) {
                    $html .= '<div class="mt-1 text-muted text-truncate" title="' . e($m->catatan) . '"><i class="bi bi-chat-left-text me-1"></i>' . e($m->catatan) . '</div>';
                }
                $html .= '</div>';
                $html .= '</div>'; // end top content

                // Card Footer: Active Timer & Control Buttons
                $html .= '<div class="border-top pt-1 mt-1">';
                $html .= '<div class="d-flex align-items-center justify-content-between mb-1">';
                $html .= '<span class="text-muted small" style="font-size: 0.7rem;">Durasi:</span>';
                $html .= '<div class="badge bg-light text-dark border px-1 py-0 fw-bold milestone-timer-badge" style="font-size: 0.72rem; font-family: monospace;">';
                $html .= '<i class="bi bi-stopwatch text-success me-1"></i><span class="milestone-timer-text">' . self::formatDuration($totalDetik) . '</span>';
                $html .= '</div>';
                $html .= '</div>'; // end timer row

                // Control Action Buttons
                $html .= '<div class="btn-group btn-group-sm w-100" role="group">';

                if ($m->status === 'Belum Dimulai') {
                    $html .= '<button type="button" class="btn btn-sm btn-success text-white fw-bold py-0 px-2 w-100" style="font-size: 0.72rem;" onclick="window.startMilestone(' . $m->id . ')" title="Mulai Milestone"><i class="bi bi-play-fill me-1"></i>Mulai</button>';
                } elseif ($m->status === 'Berjalan') {
                    $html .= '<button type="button" class="btn btn-sm btn-warning text-dark fw-bold py-0 px-1" style="font-size: 0.72rem;" onclick="window.pauseMilestone(' . $m->id . ')" title="Pause Timer"><i class="bi bi-pause-fill me-1"></i>Pause</button>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger text-white fw-bold py-0 px-1" style="font-size: 0.72rem;" onclick="window.stopMilestone(' . $m->id . ')" title="Berhenti / Selesai"><i class="bi bi-stop-fill me-1"></i>Berhenti</button>';
                } elseif ($m->status === 'Di-pause') {
                    $html .= '<button type="button" class="btn btn-sm btn-success text-white fw-bold py-0 px-1" style="font-size: 0.72rem;" onclick="window.startMilestone(' . $m->id . ')" title="Lanjut (Resume)"><i class="bi bi-play-fill me-1"></i>Lanjut</button>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger text-white fw-bold py-0 px-1" style="font-size: 0.72rem;" onclick="window.stopMilestone(' . $m->id . ')" title="Berhenti / Selesai"><i class="bi bi-stop-fill me-1"></i>Berhenti</button>';
                } else { // Selesai
                    $html .= '<span class="btn btn-sm btn-light text-success fw-bold disabled py-0 px-1 w-100 border text-center" style="font-size: 0.72rem; opacity: 1;"><i class="bi bi-check-all me-1"></i>Selesai</span>';
                }

                $html .= '</div>'; // end btn group
                $html .= '</div>'; // end card footer

                $html .= '</div>'; // end step card
                $html .= '</div>'; // end horizontal step item

                $idx++;
            }

            $html .= '</div>'; // end horizontal timeline
            $html .= '</div>'; // end wrapper
        } else {
            $html .= '<div class="text-center py-2 bg-light rounded-2 border border-dashed my-1">';
            $html .= '<i class="bi bi-diagram-3 text-success fs-4 d-block mb-1"></i>';
            $html .= '<div class="fw-semibold text-dark small mb-1">Belum Ada Point Milestone</div>';
            $html .= '<div class="text-muted" style="font-size: 0.74rem;">Klik <strong>+ Tambah Point</strong> untuk membuat alur kerja.</div>';
            $html .= '</div>';
        }

        $html .= '</div>'; // end widget container
        return $html;
    }

    /**
     * Format duration seconds to human readable string (00h 00m 00s)
     */
    public static function formatDuration($totalDetik)
    {
        if ($totalDetik <= 0) {
            return '0s';
        }

        $days = floor($totalDetik / 86400);
        $hours = floor(($totalDetik % 86400) / 3600);
        $minutes = floor(($totalDetik % 3600) / 60);
        $seconds = $totalDetik % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . 'h';
        if ($hours > 0) $parts[] = $hours . 'j';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($seconds > 0 || empty($parts)) $parts[] = $seconds . 's';

        return implode(' ', $parts);
    }
}
