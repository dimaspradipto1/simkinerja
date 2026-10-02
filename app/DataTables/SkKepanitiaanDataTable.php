<?php

namespace App\DataTables;

use App\Models\SkKepanitiaan;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SkKepanitiaanDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<SkKepanitiaan> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('kepanitiaan_info', function ($row) {
                $html = '<div>';
                $html .= '<div class="fw-bold text-dark fs-6">' . e($row->nama_kepanitiaan) . '</div>';
                if ($row->nomor_sk) {
                    $html .= '<small class="text-muted"><i class="bi bi-file-earmark-text text-primary me-1"></i>No. SK: ' . e($row->nomor_sk) . '</small>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('file_preview', function ($row) {
                if ($row->file_sk) {
                    $url = asset('storage/' . $row->file_sk);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 shadow-2xs" style="border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;">'
                        . '<i class="bi bi-file-earmark-pdf-fill"></i> Lihat SK'
                        . '</a>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->addColumn('anggota_stat', function ($row) {
                $total = (int) $row->total_anggota;
                $matched = (int) $row->total_matched;
                $html = '<div class="text-nowrap">';
                $html .= '<span class="badge bg-primary me-1" title="Total Personel di SK"><i class="bi bi-people me-1"></i>' . $total . ' Personel</span>';
                if ($matched > 0) {
                    $html .= '<span class="badge bg-success" title="Terhubung ke Akun Pengguna"><i class="bi bi-check-circle me-1"></i>' . $matched . ' Terhubung</span>';
                } else {
                    $html .= '<span class="badge bg-warning text-dark" title="Belum Terhubung"><i class="bi bi-exclamation-triangle me-1"></i>0 Terhubung</span>';
                }
                $html .= '</div>';
                return $html;
            })
            ->editColumn('tanggal_sk', function ($row) {
                return $row->tanggal_sk ? $row->tanggal_sk->format('d/m/Y') : '-';
            })
            ->editColumn('status', function ($row) {
                $status = $row->status ?? 'Aktif';
                $bg = $status === 'Aktif' ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $bg . '">' . e($status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $showUrl = route('sk-kepanitiaan.show', $row->id);
                $editUrl = route('sk-kepanitiaan.edit', $row->id);

                $btn = '<div class="d-inline-flex gap-1 flex-nowrap align-items-center">';
                $btn .= '<a href="' . $showUrl . '" class="btn btn-sm btn-info text-white d-inline-flex align-items-center gap-1" title="Lihat Anggota & Detail SK" style="border-radius: 6px; padding: 4px 8px;"><i class="bi bi-eye"></i> Detail</a>';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-warning text-white d-inline-flex align-items-center gap-1" title="Edit SK" style="border-radius: 6px; padding: 4px 8px;"><i class="bi bi-pencil-square"></i></a>';
                $btn .= '<button type="button" onclick="deleteSk(' . $row->id . ', \'' . addslashes($row->nama_kepanitiaan) . '\')" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1" title="Hapus SK" style="border-radius: 6px; padding: 4px 8px;"><i class="bi bi-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['kepanitiaan_info', 'file_preview', 'anggota_stat', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<SkKepanitiaan>
     */
    public function query(SkKepanitiaan $model): QueryBuilder
    {
        return $model->newQuery()->latest();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sk-kepanitiaan-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'stateSave' => true,
                'autoWidth' => false,
                'ordering' => true,
                'scrollX' => true,
                'responsive' => true,
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Semua']
                ],
                'language' => [
                    'processing' => '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data SK...',
                    'search' => 'Cari SK:',
                    'lengthMenu' => 'Tampilkan _MENU_ data per halaman',
                    'info' => 'Menampilkan _START_ s/d _END_ dari _TOTAL_ data',
                    'infoEmpty' => 'Menampilkan 0 data',
                    'zeroRecords' => 'Belum ada SK Kepanitiaan yang diunggah.',
                    'paginate' => [
                        'first' => '<i class="bi bi-chevron-double-left"></i>',
                        'last' => '<i class="bi bi-chevron-double-right"></i>',
                        'next' => '<i class="bi bi-chevron-right"></i>',
                        'previous' => '<i class="bi bi-chevron-left"></i>'
                    ]
                ]
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false)->width(40)->addClass('text-center'),
            Column::make('kepanitiaan_info')->title('Nama Kepanitiaan & SK')->width(280),
            Column::make('tahun_akademik')->title('T.A')->width(110)->addClass('text-center'),
            Column::make('tanggal_sk')->title('Tgl SK')->width(100)->addClass('text-center'),
            Column::make('file_preview')->title('Berkas')->orderable(false)->searchable(false)->width(110)->addClass('text-center'),
            Column::make('anggota_stat')->title('Status Personel')->orderable(false)->searchable(false)->width(180)->addClass('text-center'),
            Column::make('status')->title('Status')->width(90)->addClass('text-center'),
            Column::computed('action')->title('Aksi')->exportable(false)->printable(false)->width(140)->addClass('text-center'),
        ];
    }
}
