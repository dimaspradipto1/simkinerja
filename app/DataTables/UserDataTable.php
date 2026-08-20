<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<User> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                if (auth()->check() && $row->id === auth()->id()) {
                    return '<input type="checkbox" class="form-check-input" disabled title="Tidak dapat memilih akun sendiri" style="cursor: not-allowed; width: 18px; height: 18px;">';
                }
                return '<input type="checkbox" class="form-check-input select-user-checkbox" value="' . $row->id . '" style="cursor: pointer; width: 18px; height: 18px;">';
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('user.edit', $row->id);
                $btn = '<div class="d-inline-flex gap-1 flex-nowrap align-items-center">';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm text-white d-inline-flex align-items-center justify-content-center" title="Edit"><i class="bi bi-pencil-square"></i></a>';
                $btn .= '<button type="button" onclick="openPasswordModal(' . $row->id . ', \'' . addslashes($row->name) . '\')" class="btn btn-info btn-sm text-white d-inline-flex align-items-center justify-content-center" title="Ganti Password"><i class="bi bi-key"></i></button>';
                $btn .= '<button type="button" onclick="deleteUser(' . $row->id . ')" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus"><i class="bi bi-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['checkbox', 'is_active', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        $authUser = auth()->user();
        $query = $model->newQuery();

        if ($authUser) {
            if ($authUser->isAdmin() || $authUser->isPimpinanRektorat()) {
                // Superadmin, Admin, Pimpinan Rektorat -> Seluruh user
            } elseif ($authUser->isPimpinanUnit()) {
                // Pimpinan Unit -> User di unitnya
                $query->where('unit', $authUser->unit);
            } else {
                // Staff regular -> Data diri sendiri
                $query->where('id', $authUser->id);
            }
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('user-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->parameters([
                'autoWidth' => false,
                'ordering' => true,
                'scrollX' => true,
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
            Column::computed('checkbox')
                ->title('<input type="checkbox" id="check-all-users" class="form-check-input" style="cursor: pointer; width: 18px; height: 18px;" title="Pilih Semua">')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width('3%')
                ->addClass('text-center align-middle'),
            Column::computed('DT_RowIndex')
                ->title('No')
                ->orderable(false)
                ->searchable(false)
                ->width('4%')
                ->addClass('text-center align-middle'),
            Column::make('name')->title('Nama Pegawai')->addClass('text-nowrap align-middle'),
            Column::make('email')->title('Email')->addClass('text-nowrap align-middle'),
            Column::make('nidn')->title('NIDN')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('unit')->title('Unit')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('jabatan')->title('Jabatan')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('jabatan_pkkmb')->title('Jabatan PKKMB')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('jabatan_esq')->title('Jabatan ESQ')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('jabatan_milad')->title('Jabatan MILAD')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('jabatan_kuliah_umum')->title('Jabatan Kuliah Umum')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('roles')->title('Role')->addClass('text-nowrap align-middle'),
            Column::make('is_active')->title('Status')->addClass('text-nowrap text-center align-middle'),
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
        return 'User_' . date('YmdHis');
    }
}
