@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Docs</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Generated Documents</h6>
    </div>
    <div class="card-body">
        @if(count($docs))
            <ul class="list-group" id="docsList">
                @foreach($docs as $doc)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="#" class="view-doc" data-name="{{ $doc['name'] }}">
                            {{ $doc['name'] }}
                        </a>
                        <span class="text-muted small">{{ $doc['time'] }}</span>
                    </li>
                @endforeach
            </ul>
            <div id="docContent" class="border p-3 bg-light mt-3" style="display:none;"></div>
        @else
            <p class="text-muted">No documents found.</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.view-doc', function(e) {
        e.preventDefault();
        let filename = $(this).data('name');

        $.get(`/docs/view/${filename}`, function(res) {
            let html = marked.parse(res.content);
            $('#docContent').show().html(html);
        });
    });
});
</script>
@endpush
