<?php

namespace App\DataTables;

use App\Models\PeriodeAkademik;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PeriodeAkademikDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<PeriodeAkademik> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                if ($row->is_active) {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>';
                }
                return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('periode-akademik.edit', $row->id);
                $btn = '<div class="d-inline-flex align-items-center justify-content-center flex-nowrap" style="gap: 6px;">';
                if (!$row->is_active) {
                    $btn .= '<button type="button" onclick="setActivePeriode(' . $row->id . ', \'' . addslashes($row->formatted_ta) . '\')" class="btn btn-outline-success btn-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 6px; margin: 0 2px; flex-shrink: 0;" title="Jadikan Periode Aktif"><i class="bi bi-check2-circle fs-6"></i></button>';
                }
                $btn .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 6px; margin: 0 2px; flex-shrink: 0;" title="Edit Periode"><i class="bi bi-pencil-square fs-6"></i></a>';
                $btn .= '<button type="button" onclick="deletePeriode(' . $row->id . ')" class="btn btn-danger btn-sm text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 6px; margin: 0 2px; flex-shrink: 0;" title="Hapus Periode"><i class="bi bi-trash fs-6"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<PeriodeAkademik>
     */
    public function query(PeriodeAkademik $model): QueryBuilder
    {
        return $model->newQuery()->orderByDesc('is_active')->orderBy('id', 'asc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('periodeakademik-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'stateSave' => true,
                'autoWidth' => false,
                'ordering' => true,
                'scrollX' => true,
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Semua']
                ],
                'pageLength' => 10,
                'language' => [
                    'search' => 'Cari:',
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
        return [
            Column::computed('DT_RowIndex')
                ->title('No')
                ->orderable(false)
                ->searchable(false)
                ->width('4%')
                ->addClass('text-center align-middle'),
            Column::make('nama_periode')->title('Nama Periode')->addClass('text-nowrap align-middle'),
            Column::computed('status')
                ->title('Status')
                ->orderable(false)
                ->searchable(false)
                ->width('12%')
                ->addClass('text-center text-nowrap align-middle'),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->width('120px')
                ->addClass('text-center text-nowrap align-middle px-2'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PeriodeAkademik_' . date('YmdHis');
    }
}
