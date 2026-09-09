<?php

namespace App\DataTables;

use App\Models\Lpj;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LpjDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Lpj> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('pengunggah', function ($row) {
                $user = $row->user;
                if (!$user) {
                    return '<span class="text-muted">-</span>';
                }
                $initial = strtoupper(substr($user->name, 0, 1));
                $jabatan = $user->jabatan ? '<small class="text-muted d-block" style="font-size: 0.72rem;">' . e($user->jabatan) . '</small>' : '';
                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold flex-shrink-0" style="width: 28px; height: 28px; background-color: #e0f2fe; font-size: 0.75rem;">
                            ' . $initial . '
                        </div>
                        <div class="min-w-0">
                            <span class="fw-semibold text-dark d-block" style="font-size: 0.85rem;">' . e($user->name) . '</span>
                            ' . $jabatan . '
                        </div>
                    </div>
                ';
            })
            ->editColumn('nama_lpj', function ($row) {
                $html = '<div class="fw-bold text-dark" style="font-size: 0.88rem;">' . e($row->nama_lpj) . '</div>';
                if (!empty($row->keterangan)) {
                    $html .= '<div class="text-muted small text-truncate mt-0.5" style="max-width: 280px; font-size: 0.75rem;">' . e($row->keterangan) . '</div>';
                }
                return $html;
            })
            ->editColumn('nama_kepanitiaan', function ($row) {
                return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium px-2 py-1" style="font-size: 0.78rem;"><i class="bi bi-people me-1"></i>' . e($row->nama_kepanitiaan) . '</span>';
            })
            ->editColumn('tanggal_kegiatan', function ($row) {
                if (!$row->tanggal_kegiatan) {
                    return '<span class="text-muted">-</span>';
                }
                return '<span class="text-nowrap" style="font-size: 0.82rem;"><i class="bi bi-calendar3 me-1 text-secondary"></i>' . \Carbon\Carbon::parse($row->tanggal_kegiatan)->translatedFormat('d M Y') . '</span>';
            })
            ->addColumn('berkas', function ($row) {
                $buttons = [];
                if (!empty($row->file_lpj)) {
                    $fileUrl = asset('storage/' . $row->file_lpj);
                    $downloadRoute = route('lpj.download', $row->id);
                    $buttons[] = '<a href="' . $downloadRoute . '" class="btn btn-outline-primary btn-sm py-1 px-2 d-inline-flex align-items-center gap-1" title="Unduh Berkas"><i class="bi bi-file-earmark-arrow-down-fill"></i> File</a>';
                }
                if (!empty($row->link_lpj)) {
                    $buttons[] = '<a href="' . e($row->link_lpj) . '" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm py-1 px-2 d-inline-flex align-items-center gap-1" title="Buka Link LPJ"><i class="bi bi-box-arrow-up-right"></i> Link</a>';
                }
                if (empty($buttons)) {
                    return '<span class="text-muted small">-</span>';
                }
                return '<div class="d-flex align-items-center gap-1 flex-wrap">' . implode('', $buttons) . '</div>';
            })
            ->addColumn('action', function ($row) {
                $user = Auth::user();
                $canManage = Auth::check() && $user && (
                    $user->isAdmin() ||
                    $user->isSuperAdmin() ||
                    $user->isPimpinanRektorat() ||
                    $user->isPimpinanUnit() ||
                    $user->id === $row->user_id
                );

                $showUrl = route('lpj.show', $row->id);
                $editUrl = route('lpj.edit', $row->id);

                $btn = '<div class="d-inline-flex gap-1 flex-nowrap align-items-center">';
                $btn .= '<a href="' . $showUrl . '" class="btn btn-info btn-sm text-white d-inline-flex align-items-center gap-1" title="Detail"><i class="bi bi-eye"></i></a>';

                if ($canManage) {
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center gap-1" title="Edit"><i class="bi bi-pencil-square"></i></a>';
                    $btn .= '<button type="button" onclick="deleteLpj(' . $row->id . ')" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1" title="Hapus"><i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['pengunggah', 'nama_lpj', 'nama_kepanitiaan', 'tanggal_kegiatan', 'berkas', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Lpj>
     */
    public function query(Lpj $model): QueryBuilder
    {
        return $model->newQuery()->with('user')->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('lpj-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'autoWidth' => false,
                'ordering' => true,
                'scrollX' => true,
                'responsive' => false,
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Semua']
                ],
                'pageLength' => 10,
                'language' => [
                    'search' => 'Cari:',
                    'lengthMenu' => 'Tampilkan _MENU_ data',
                    'zeroRecords' => 'Data LPJ tidak ditemukan',
                    'info' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    'infoEmpty' => 'Tidak ada data LPJ',
                    'infoFiltered' => '(difilter dari _MAX_ total data)',
                    'paginate' => [
                        'first' => 'Pertama',
                        'last' => 'Terakhir',
                        'next' => 'Selanjutnya',
                        'previous' => 'Sebelumnya'
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
            Column::computed('DT_RowIndex')
                ->title('No')
                ->orderable(false)
                ->searchable(false)
                ->width('4%')
                ->addClass('text-center align-middle'),
            Column::make('nama_lpj')->title('Nama / Judul LPJ')->addClass('align-middle'),
            Column::computed('pengunggah')->title('Pengunggah')->addClass('align-middle text-nowrap'),
            Column::make('nama_kepanitiaan')->title('Nama Kepanitiaan')->addClass('align-middle text-nowrap'),
            Column::make('tanggal_kegiatan')->title('Tanggal')->addClass('align-middle text-nowrap'),
            Column::computed('berkas')->title('Berkas / Link')->orderable(false)->searchable(false)->addClass('align-middle text-nowrap'),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center text-nowrap align-middle')
                ->width('10%'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'LPJ_' . date('YmdHis');
    }
}
