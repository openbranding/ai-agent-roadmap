@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Agents</h1>
</div>

<div class="row">
    @foreach($agents as $agent)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card {{ $agent['rowClass'] }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                        {{ $agent['name'] }}
                    </div>
                    <div class="h5 mb-0 font-weight-bold">
                        <span class="badge {{ $agent['badgeClass'] }}">
                            {{ $agent['icon'] }} {{ $agent['label'] }}
                        </span>
                    </div>
                    <div class="mt-2 small text-muted">
                        Last task: {{ $agent['last_task'] }} <br>
                        Seen: {{ $agent['last_seen'] }}
                    </div>

                    <!-- Actions -->
                    <div class="mt-3">
                        <a href="{{ route('agents.history', $agent['name']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-history"></i> View History
                        </a>
                        <!-- Trigger modal -->
                        <button class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#taskModal-{{ $agent['name'] }}">
                            <i class="fas fa-plus"></i> Add Task
                        </button>

                        <!-- Extra: only show summaries button for ContentAgent -->
                        @if($agent['name'] === 'ContentAgent')
                            <button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#summaryModal">
                                <i class="fas fa-file-alt"></i> View Summaries
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Quick table summary -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Agent Summary</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Last Task</th>
                        <th>Last Seen</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        <tr class="{{ $agent['rowClass'] }}">
                            <td>{{ $agent['name'] }}</td>
                            <td>
                                <span class="badge {{ $agent['badgeClass'] }}">
                                    {{ $agent['icon'] }} {{ $agent['label'] }}
                                </span>
                            </td>
                            <td>{{ $agent['last_task'] }}</td>
                            <td>{{ $agent['last_seen'] }}</td>
                            <td>
                                <a href="{{ route('agents.history', $agent['name']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-history"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#taskModal-{{ $agent['name'] }}">
                                    <i class="fas fa-plus"></i>
                                </button>

                                @if($agent['name'] === 'ContentAgent')
                                    <button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#summaryModal">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No agents found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DevAgent managed routes -->
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">DevAgent Managed Test Routes</h6>
        <form action="{{ route('agents.cleanupRoutes') }}" method="POST" onsubmit="return confirm('Run cleanup now?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-broom"></i> Cleanup Routes
            </button>
        </form>
    </div>
    <div class="card-body">
        @if(!empty($devRoutes))
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th>Controller</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devRoutes as $route)
                            <tr>
                                <td><code>{{ $route['path'] }}</code></td>
                                <td>{{ $route['controller'] }}</td>
                                <td>
                                    <a href="{{ url($route['path']) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-external-link-alt"></i> Visit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No DevAgent test routes found.</p>
        @endif
    </div>
</div>

<!-- Task modals -->
@foreach($agents as $agent)
<div class="modal fade" id="taskModal-{{ $agent['name'] }}" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('agents.addTask', $agent['name']) }}">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task for {{ $agent['name'] }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <textarea name="task" class="form-control" placeholder="Enter task..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Dispatch</button>
            </div>
        </div>
    </form>
  </div>
</div>
@endforeach

<!-- ContentAgent Summaries Modal -->
<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="summaryModalLabel">ContentAgent Summaries</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="list-group mb-3" id="summaryList">
          <!-- Summaries will load here -->
        </div>
        <div id="summaryContent" class="border p-3 bg-light" style="white-space: pre-wrap; font-family: monospace; display:none;"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load summaries when modal opens
    $('#summaryModal').on('show.bs.modal', function () {
        $('#summaryList').html('<p>Loading...</p>');
        $('#summaryContent').hide();

        $.get("{{ route('agents.summaries') }}", function(data) {
            if (data.length === 0) {
                $('#summaryList').html('<p>No summaries found.</p>');
                return;
            }

            let html = '';
            data.forEach(file => {
                html += `<a href="#" class="list-group-item list-group-item-action view-summary" data-name="${file.name}">
                            ${file.name} <small class="text-muted">(${file.time})</small>
                         </a>`;
            });
            $('#summaryList').html(html);
        });
    });

    // Click handler for individual summary
    $(document).on('click', '.view-summary', function(e) {
        e.preventDefault();
        let filename = $(this).data('name');

        $.get(`/agents/summaries/${filename}`, function(res) {
            $('#summaryContent').show().text(res.content);
        });
    });
});
</script>
@endpush
