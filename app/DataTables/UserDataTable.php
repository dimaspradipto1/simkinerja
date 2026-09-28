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
                if ($row->isProtectedAdmin()) {
                    return '<input type="checkbox" class="form-check-input" disabled title="Akun Super Admin / Admin tidak dapat dipilih" style="cursor: not-allowed; width: 18px; height: 18px;">';
                }
                return '<input type="checkbox" class="form-check-input select-user-checkbox" value="' . $row->id . '" style="cursor: pointer; width: 18px; height: 18px;">';
            })
            ->editColumn('roles', function ($row) {
                if (empty($row->roles)) {
                    return '-';
                }
                $roles = array_filter(array_map('trim', explode(',', $row->roles)));
                if (empty($roles)) {
                    return '-';
                }
                $badges = array_map(function ($r) {
                    $rLower = strtolower($r);
                    $class = 'bg-secondary';
                    if (in_array($rLower, ['super admin', 'superadmin', 'admin', 'admin ict'])) {
                        $class = 'bg-dark text-white';
                    } elseif (in_array($rLower, ['pimpinan rektorat', 'rektor', 'wakil rektor i', 'wakil rektor ii', 'wakil rektor iii'])) {
                        $class = 'bg-primary text-white';
                    } elseif (in_array($rLower, ['pimpinan unit', 'dekan', 'kepala biro', 'kepala lppm', 'kepala lpmi', 'kepala lpti', 'kepala perpustakaan'])) {
                        $class = 'bg-info text-dark';
                    } elseif (in_array($rLower, ['pimpinan sub unit', 'wakil dekan i', 'wakil dekan ii', 'ketua program studi', 'sekretaris program studi', 'kaprodi'])) {
                        $class = 'bg-primary-subtle text-primary border border-primary-subtle';
                    } elseif (in_array($rLower, ['kepanitiaan', 'panitia'])) {
                        $class = 'bg-success text-white';
                    } elseif (in_array($rLower, ['tim evaluasi', 'evaluasi'])) {
                        $class = 'bg-warning text-dark';
                    }
                    return '<span class="badge ' . $class . ' me-1 text-uppercase" style="font-size: 0.72rem; font-weight: 500;">' . e($r) . '</span>';
                }, $roles);
                return implode(' ', $badges);
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
                if ($row->id !== auth()->id() && !$row->isProtectedAdmin()) {
                    $btn .= '<button type="button" onclick="deleteUser(' . $row->id . ')" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus"><i class="bi bi-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('jabatan_kepanitiaan', function ($row) {
                $list = $row->daftar_jabatan_kepanitiaan;
                if (empty($list)) {
                    return '<span class="text-muted">-</span>';
                }
                $badges = [];
                foreach ($list as $item) {
                    $badges[] = '<div class="mb-1 text-nowrap"><span class="badge bg-light text-dark border me-1">' . e($item['nama']) . '</span><span class="badge bg-success-subtle text-success border border-success-subtle">' . e($item['jabatan']) . '</span></div>';
                }
                return implode('', $badges);
            })
            ->rawColumns(['checkbox', 'roles', 'is_active', 'jabatan_kepanitiaan', 'action']);
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
                    'infoFiltered' => '(difilter dari _MAX_ total data)',
                    'paginate' => [
                        'first' => 'Pertama',
                        'last' => 'Terakhir',
                        'next' => 'Berikutnya',
                        'previous' => 'Sebelumnya',
                    ],
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
            Column::make('nidn')->title('NUP')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('unit')->title('Unit')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::make('jabatan')->title('Jabatan')->addClass('text-nowrap align-middle')->defaultContent('-'),
            Column::computed('jabatan_kepanitiaan')
                ->title('Jabatan Kepanitiaan')
                ->addClass('text-nowrap align-middle'),
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
