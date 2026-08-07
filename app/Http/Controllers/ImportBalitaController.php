<?php

namespace App\Http\Controllers;

use App\Imports\BalitaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <-- Tambahkan ini
use Inertia\Inertia;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Writer\XLSX\Options;

class ImportBalitaController extends Controller
{
    public function page()
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();
        $posyandu = \App\Models\Posyandu::whereIn('id', $ids)->get(['id', 'nama']);

        return Inertia::render('Balita/Import', [
            'posyandu'  => $posyandu,
            'roleAktor' => $user->role,
        ]);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();

        $request->validate([
            'file'        => 'required|file|mimes:xlsx,csv|max:5120',
            'posyandu_id' => 'required|integer',
        ]);

        abort_unless(in_array((int) $request->posyandu_id, $ids), 403,
            'Anda tidak memiliki akses ke posyandu ini.');

        $file = $request->file('file');
        $path = $file->store('imports/temp');
        
        // PERBAIKAN: Gunakan Storage::path() agar lebih aman dan dinamis (tidak bergantung pada folder "private")
        $fullPath = Storage::path($path); 
        $ext  = $file->getClientOriginalExtension();

        $importer = new BalitaImport(
            posyanduId: (int) $request->posyandu_id,
            dicatatOleh: Auth::id(),
        );

        $importer->import($fullPath, $ext);

        // Hapus file temp
        Storage::delete($path);

        return back()->with('importResult', [
            'berhasil'        => $importer->berhasil, // <-- TAMBAHAN: untuk UI Frontend
            'balita_baru'     => $importer->balitaBaru,
            'pengukuran_baru' => $importer->pengukuranBaru,
            'errors'          => $importer->errors,
        ]);
    }

    public function template()
    {
        $writer = new Writer();
        $path   = tempnam(sys_get_temp_dir(), 'template_') . '.xlsx';
        $writer->openToFile($path);

        // Header
        $header = ['NIK Balita', 'Nama Balita', 'Tanggal Lahir (YYYY-MM-DD)',
                   'Jenis Kelamin (L/P)', 'Nama Ibu', 'Alamat',
                   'Tanggal Ukur (YYYY-MM-DD)', 'Berat Badan (kg)', 'Tinggi Badan (cm)',
                   'Prematur (Y/N)', 'Usia Gestasi (minggu)'];
        $writer->addRow(Row::fromValues($header));

        // Contoh data
        $contoh = [
            ['3273040000000001', 'Anisa Putri',   '2024-01-15', 'P', 'Siti Rahayu', 'Jl. Melati No. 1', '2026-05-10', '8.5',  '72.0', 'N', ''],
            ['3273040000000001', 'Anisa Putri',   '2024-01-15', 'P', 'Siti Rahayu', 'Jl. Melati No. 1', '2026-06-10', '8.8',  '73.5', 'N', ''],
            ['3273040000000001', 'Anisa Putri',   '2024-01-15', 'P', 'Siti Rahayu', 'Jl. Melati No. 1', '2026-07-10', '9.1',  '74.2', 'N', ''],
            ['3273040000000002', 'Budi Santoso',  '2024-03-01', 'L', 'Rina Wati',   'Jl. Mawar No. 5',  '2026-06-15', '5.8',  '62.0', 'Y', '35'],
            ['3273040000000002', 'Budi Santoso',  '2024-03-01', 'L', 'Rina Wati',   'Jl. Mawar No. 5',  '2026-07-15', '6.1',  '63.5', 'Y', '35'],
        ];

        foreach ($contoh as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()->download($path, 'Template_Import_Balita.xlsx')->deleteFileAfterSend();
    }
}