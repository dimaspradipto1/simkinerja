<?php

namespace App\DataTables;

use App\Models\AbsensiEsqKetiga;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AbsensiEsqKetigaDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<AbsensiEsqKetiga> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                return $row->user ? $row->user->name : '-';
            })
            ->addColumn('waktu_datang_formatted', function ($row) {
                return $row->waktu_datang ? substr($row->waktu_datang, 0, 5) : '-';
            })
            ->addColumn('waktu_pulang_formatted', function ($row) {
                return $row->waktu_pulang ? substr($row->waktu_pulang, 0, 5) : '-';
            })
            ->addColumn('bukti_izin_badge', function ($row) {
                if ($row->bukti_izin) {
                    $url = asset('storage/' . $row->bukti_izin);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-outline-info btn-sm py-1 px-2 rounded"><i class="bi bi-file-earmark-text"></i> Lihat Berkas</a>';
                }
                return '<span class="badge bg-secondary">Tidak ada</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('absensi-esq-ketiga.edit', $row->id);
                $btn = '<div class="d-inline-flex gap-1 flex-nowrap align-items-center">';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center justify-content-center" title="Edit"><i class="bi bi-pencil-square"></i></a>';
                $btn .= '<button type="button" onclick="deleteAbsensi(' . $row->id . ')" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus"><i class="bi bi-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['bukti_izin_badge', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<AbsensiEsqKetiga>
     */
    public function query(AbsensiEsqKetiga $model): QueryBuilder
    {
        return $model->newQuery()->with('user')->orderBy('id', 'asc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('absensiesqketiga-table')
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
            Column::computed('user_name')
                ->title('NAMA PENGGUNA')
                ->addClass('align-middle'),
            Column::make('hadir_datang')
                ->title('HADIR DATANG')
                ->addClass('text-center align-middle'),
            Column::computed('waktu_datang_formatted')
                ->title('WAKTU DATANG')
                ->addClass('text-center align-middle'),
            Column::make('catatan_hadir_datang')
                ->title('CATATAN DATANG')
                ->addClass('align-middle'),
            Column::make('hadir_pulang')
                ->title('HADIR PULANG')
                ->addClass('text-center align-middle'),
            Column::computed('waktu_pulang_formatted')
                ->title('WAKTU PULANG')
                ->addClass('text-center align-middle'),
            Column::make('catatan_hadir_pulang')
                ->title('CATATAN PULANG')
                ->addClass('align-middle'),
            Column::computed('bukti_izin_badge')
                ->title('BUKTI IZIN')
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
        return 'AbsensiEsqKetiga_' . date('YmdHis');
    }
}
