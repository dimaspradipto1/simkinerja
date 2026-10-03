<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengaturanPesanWhatsappRequest;
use App\Models\PengaturanPesanWhatsapp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class PengaturanPesanWhatsappController extends Controller
{
    public function index()
    {
        return redirect()->route('pengaturan-pesan-whatsapp.create');
    }

    public function create()
    {
        $templates = PengaturanPesanWhatsapp::query()
            ->with('recipients:id')
            ->orderBy('id')
            ->get()
            ->groupBy('status');

        $messages = [];
        foreach (PengaturanPesanWhatsapp::STATUSES as $status) {
            $messages[$status] = $templates->get($status)?->first();
        }

        $configuredRecipientIds = $templates->flatten()
            ->flatMap(fn (PengaturanPesanWhatsapp $template) => $template->recipients->modelKeys())
            ->unique()
            ->values()
            ->all();

        $users = User::query()
            ->where('is_active', true)
            ->whereNotNull('supervisor_id')
            ->whereNotNull('nomor_wa')
            ->where('nomor_wa', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan', 'unit', 'nomor_wa']);

        $existingManualItems = $this->buildManualItems(
            $users->whereIn('id', $configuredRecipientIds),
            $messages
        );

        return view('pages.pengaturan-pesan-whatsapp.create', compact(
            'messages',
            'users',
            'configuredRecipientIds',
            'existingManualItems'
        ));
    }

    public function store(PengaturanPesanWhatsappRequest $request)
    {
        $validated = $request->validated();
        $categories = ['Rencana Kerja', 'Kepanitiaan', 'Insidentil'];
        $templates = PengaturanPesanWhatsapp::query()->get()->keyBy(
            fn (PengaturanPesanWhatsapp $template) => $template->kategori . '|' . $template->status
        );

        DB::transaction(function () use ($validated, $categories, $templates): void {
            foreach ($categories as $category) {
                foreach (PengaturanPesanWhatsapp::STATUSES as $status) {
                    $template = $templates->get($category . '|' . $status);
                    if (!$template) {
                        throw new \RuntimeException(
                            "Template WhatsApp untuk {$category} dengan status {$status} belum tersedia."
                        );
                    }

                    $settings = $validated['pesan'][$status];
                    $scheduledTime = !empty($settings['waktu_kirim']) ? $settings['waktu_kirim'] : null;
                    if ($scheduledTime && strlen($scheduledTime) === 5) {
                        $scheduledTime .= ':00';
                    }

                    $template->update([
                        'isi_pesan' => $settings['isi_pesan'],
                        'mode_pengiriman' => $scheduledTime ? 'terjadwal' : 'langsung',
                        'waktu_kirim' => $scheduledTime,
                        'is_active' => (bool) $settings['is_active'],
                    ]);
                    $template->recipients()->sync($validated['penerima_user_ids']);
                }
            }
        });

        $recipients = User::query()
            ->whereIn('id', (array) $validated['penerima_user_ids'])
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan', 'unit', 'nomor_wa']);

        $manualItems = [];
        $senderName = Auth::user()?->name ?? 'Pimpinan';

        foreach (PengaturanPesanWhatsapp::STATUSES as $status) {
            $settings = $validated['pesan'][$status];
            if (!empty($settings['is_active'])) {
                $statusItems = [];
                foreach ($recipients as $recipient) {
                    $cleanNumber = $this->formatWaNumber($recipient->nomor_wa);
                    if (!$cleanNumber) {
                        continue;
                    }

                    $messageText = strtr($settings['isi_pesan'], [
                        '{nama_penerima}' => $recipient->name,
                        '{nama_pemimpin}' => $senderName,
                    ]);

                    $statusItems[] = [
                        'recipient_id' => $recipient->id,
                        'recipient_name' => $recipient->name,
                        'recipient_jabatan' => $recipient->jabatan ?: $recipient->unit ?: '-',
                        'recipient_wa' => $recipient->nomor_wa,
                        'clean_wa' => $cleanNumber,
                        'message' => $messageText,
                        'wa_url' => 'https://wa.me/' . $cleanNumber . '?text=' . rawurlencode($messageText),
                    ];
                }

                $manualItems[$status] = [
                    'label' => match ($status) {
                        'Proses/Berjalan' => 'Proses / Berjalan',
                        default => $status,
                    },
                    'mode' => !empty($settings['waktu_kirim']) ? 'terjadwal' : 'langsung',
                    'waktu_kirim' => $settings['waktu_kirim'] ?? null,
                    'items' => $statusItems,
                ];
            }
        }

        session()->flash('manual_wa_recipients', $manualItems);
        Alert::success('Berhasil', 'Pengaturan disimpan. Silakan klik tombol "Kirim via WhatsApp" pada daftar penerima.');

        return redirect()->route('pengaturan-pesan-whatsapp.create');
    }

    private function buildManualItems($recipients, array $messages): array
    {
        $manualItems = [];
        $senderName = Auth::user()?->name ?? 'Pimpinan';

        foreach (PengaturanPesanWhatsapp::STATUSES as $status) {
            $template = $messages[$status] ?? null;
            if ($template && $template->is_active) {
                $statusItems = [];
                foreach ($recipients as $recipient) {
                    $cleanNumber = $this->formatWaNumber($recipient->nomor_wa);
                    if (!$cleanNumber) {
                        continue;
                    }

                    $messageText = strtr($template->isi_pesan, [
                        '{nama_penerima}' => $recipient->name,
                        '{nama_pemimpin}' => $senderName,
                    ]);

                    $statusItems[] = [
                        'recipient_id' => $recipient->id,
                        'recipient_name' => $recipient->name,
                        'recipient_jabatan' => $recipient->jabatan ?: $recipient->unit ?: '-',
                        'recipient_wa' => $recipient->nomor_wa,
                        'clean_wa' => $cleanNumber,
                        'message' => $messageText,
                        'wa_url' => 'https://wa.me/' . $cleanNumber . '?text=' . rawurlencode($messageText),
                    ];
                }

                $manualItems[$status] = [
                    'label' => match ($status) {
                        'Proses/Berjalan' => 'Proses / Berjalan',
                        default => $status,
                    },
                    'mode' => $template->mode_pengiriman,
                    'waktu_kirim' => $template->waktu_kirim ? substr((string) $template->waktu_kirim, 0, 5) : null,
                    'items' => $statusItems,
                ];
            }
        }

        return $manualItems;
    }

    private function formatWaNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (!$clean) {
            return null;
        }

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }
}
