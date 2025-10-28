@extends('templates.app')
@section('content')
    <div class="container mt-3">
        @if (Session::get('succes'))
            <div class="alert alert-success">{{ Session::get('succes') }}</div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="{{ route('staff.promos.export') }}" class="btn btn-secondary me-2">Export (.xlsx)</a>
               <a href="{{ route('staff.promos.trash') }} " class="btn btn-secondary me-2">Data sampah</a>
            <a href="{{ route('staff.promos.create') }}" class="btn btn-success">Tambah Data</a>
        </div>
        <h5 class="mt-3">Data Promo</h5>
        <table class="table table-bordered" id="tablePromo">
            <thead>
                    <th>#</th>
                    <th>Kode Promo</th>
                    <th>Total Potongan</th>
                    <th>Aksi</th>
            </thead>
            {{-- @foreach ($promos as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item['promo_code'] }}</td>
                    <td>
                        @if ($item['type'] == 'rupiah')
                            <small class="badge badge-primary">Rp {{ number_format($item['discount'], 0, ',', '.') }}</small>
                        @else
                            <small class="badge badge-primary">{{ $item['discount'] }} %</small>
                        @endif
                    </td>
                    <td class="d-flex gap-2 justify-content-center">
                        <a href="{{route('staff.promos.edit', $item['id'])}}" class="btn btn-primary">Edit</a>
                        <form action="{{route('staff.promos.delete', $item['id'])}}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach --}}
        </table>
    </div>
@endsection

@push('script')
    <script>
        $(function(){
            $("#tablePromo").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('staff.promos.datatables') }}",
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'promo_code',
                        name: 'promo_code',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'discount',
                        name: 'discount',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'btnActions',
                        name: 'btnActions',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        })
    </script>
@endpush
