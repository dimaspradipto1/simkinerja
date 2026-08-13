<?php

namespace App\Http\Controllers;

class ScanAbsensiController extends Controller
{
    /**
     * Show the global QR Code scanner view for self-attendance.
     * The QR itself encodes the full target URL (per-event scan-proses
     * endpoint), so this single scanner works for any active event.
     */
    public function index()
    {
        return view('pages.scan-absensi.index');
    }
}
