@extends('layouts.app')

@section('title', 'Mes dotations')

@section('content')
    <h3 class="mb-4">Mes dotations</h3>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="mesDotationsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Articles</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#mesDotationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('dotation.data') }}',
        columns: [
            { data: 'reference', name: 'reference' },
            { data: 'date_dotation', name: 'date_dotation' },
            { data: 'type_dotation', name: 'type_dotation' },
            { data: 'articles', name: 'articles', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });
});
</script>
@endpush
