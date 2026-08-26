<?php

namespace App\DataTables;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class KategoriDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Kategori> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $editUrl = route('kategori.edit', $row->id);
                $btn = '<div class="d-inline-flex gap-1 flex-nowrap align-items-center">';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center gap-1 text-nowrap"><i class="bi bi-pencil-square"></i> Edit</a>';
                $btn .= '<button type="button" onclick="deleteKategori(' . $row->id . ')" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 text-nowrap"><i class="bi bi-trash"></i> Hapus</button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Kategori>
     */
    public function query(Kategori $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('id', 'asc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('kategori-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
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
            Column::make('nama_kategori')->title('Nama Kategori')->addClass('text-nowrap align-middle fw-semibold'),
            Column::make('deskripsi')->title('Deskripsi')->addClass('align-middle'),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center text-nowrap align-middle'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Kategori_' . date('YmdHis');
    }
}
