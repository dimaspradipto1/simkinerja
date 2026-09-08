<?php

namespace App\DataTables;

use App\Models\AbsensiPkkmbPertama;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AbsensiPkkmbPertamaDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<AbsensiPkkmbPertama> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                if ($row->user) {
                    return '<span class="fw-medium text-dark">' . e($row->user->name) . '</span>';
                }
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="User ID: ' . $row->user_id . ' tidak ditemukan di database (relasi terputus)"><i class="bi bi-person-x-fill me-1"></i> User #' . $row->user_id . ' (Tidak Ditemukan)</span>';
            })
            ->filterColumn('user_name', function ($query, $keyword) {
                $table = $query->getModel()->getTable();
                $query->where(function ($sub) use ($keyword, $table) {
                    $sub->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                          ->orWhere('nidn', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%");
                    })
                    ->orWhere($table . '.user_id', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('user_name', function ($query, $order) {
                $table = $query->getModel()->getTable();
                $query->orderByRaw(
                    "COALESCE((SELECT name FROM users WHERE users.id = {$table}.user_id), CONCAT('ZZZ_', {$table}.user_id)) {$order}"
                );
            })
            ->addColumn('waktu_datang_formatted', function ($row) {
                return $row->waktu_datang ? substr($row->waktu_datang, 0, 5) : '-';
            })
            ->filterColumn('waktu_datang_formatted', function ($query, $keyword) {
                $query->where('waktu_datang', 'like', "%{$keyword}%");
            })
            ->orderColumn('waktu_datang_formatted', function ($query, $order) {
                $query->orderBy('waktu_datang', $order);
            })
            ->addColumn('waktu_pulang_formatted', function ($row) {
                return $row->waktu_pulang ? substr($row->waktu_pulang, 0, 5) : '-';
            })
            ->filterColumn('waktu_pulang_formatted', function ($query, $keyword) {
                $query->where('waktu_pulang', 'like', "%{$keyword}%");
            })
            ->orderColumn('waktu_pulang_formatted', function ($query, $order) {
                $query->orderBy('waktu_pulang', $order);
            })
            ->addColumn('bukti_izin_badge', function ($row) {
                if ($row->bukti_izin) {
                    $url = asset('storage/' . $row->bukti_izin);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-outline-info btn-sm py-1 px-2 rounded"><i class="bi bi-file-earmark-text"></i> Lihat Berkas</a>';
                }
                return '<span class="badge bg-secondary">Tidak ada</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('absensi-pkkmb-pertama.edit', $row->id);
                $btn = '<div class="d-inline-flex gap-1 flex-nowrap align-items-center">';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center justify-content-center" title="Edit"><i class="bi bi-pencil-square"></i></a>';
                $btn .= '<button type="button" onclick="deleteAbsensi(' . $row->id . ')" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus"><i class="bi bi-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['user_name', 'bukti_izin_badge', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<AbsensiPkkmbPertama>
     */
    public function query(AbsensiPkkmbPertama $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user')->orderBy('id', 'asc');
        $authUser = auth()->user();
        if ($authUser && !$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            $query->where('user_id', $authUser->id);
        }
        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('absensipkkmbpertama-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->parameters([
                        'autoWidth' => false,
                        'ordering' => true,
                        'scrollX' => true,
                        'language' => [
                            'search' => 'Search:',
                            'lengthMenu' => 'Tampilkan _MENU_ data',
                            'zeroRecords' => 'Data tidak ditemukan',
                            'info' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                            'infoEmpty' => 'Tidak ada data',
                        ]
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::computed('DT_RowIndex')
                ->title('NO')
                ->orderable(false)
                ->searchable(false)
                ->width('4%')
                ->addClass('text-center align-middle'),
            Column::make('user_name')
                ->title('NAMA PENGGUNA')
                ->searchable(true)
                ->orderable(true)
                ->addClass('align-middle'),
            Column::make('hadir_datang')
                ->title('HADIR DATANG')
                ->addClass('text-center align-middle'),
            Column::make('waktu_datang_formatted')
                ->title('WAKTU DATANG')
                ->searchable(true)
                ->orderable(true)
                ->addClass('text-center align-middle'),
            Column::make('catatan_hadir_datang')
                ->title('CATATAN DATANG')
                ->addClass('align-middle'),
            Column::make('hadir_pulang')
                ->title('HADIR PULANG')
                ->addClass('text-center align-middle'),
            Column::make('waktu_pulang_formatted')
                ->title('WAKTU PULANG')
                ->searchable(true)
                ->orderable(true)
                ->addClass('text-center align-middle'),
            Column::make('catatan_hadir_pulang')
                ->title('CATATAN PULANG')
                ->addClass('align-middle'),
            Column::computed('bukti_izin_badge')
                ->title('BUKTI IZIN')
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-center align-middle'),
        ];

        $authUser = auth()->user();
        if ($authUser && ($authUser->isAdmin() || $authUser->isSuperAdmin())) {
            $columns[] = Column::computed('action')
                ->title('AKSI')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle');
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AbsensiPkkmbPertama_' . date('YmdHis');
    }
}
