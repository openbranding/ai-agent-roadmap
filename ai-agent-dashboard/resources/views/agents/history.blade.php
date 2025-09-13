@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ $agentName }} – History</h1>
    <div>
        <a href="{{ route('agents.index') }}" class="btn btn-sm btn-secondary mr-2">
            ← Back to Agents
        </a>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTaskModal">
            <i class="fas fa-plus"></i> Add Task
        </button>
    </div>
</div>

<!-- History Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Activity Log</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width: 180px;">Time</th>
                        <th style="width: 160px;">Status</th>
                        <th>Task</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr class="{{ $entry['rowClass'] ?? '' }}">
                            <td>{{ $entry['time'] }}</td>
                            <td>
                                <span class="badge {{ $entry['badgeClass'] ?? 'badge-secondary' }}">
                                    {{ $entry['icon'] ?? '📝' }} {{ $entry['label'] ?? ucfirst($entry['status']) }}
                                </span>
                            </td>
                            <td>{{ $entry['task'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No activity yet for {{ $agentName }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('agents.addTask', $agentName) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add Task for {{ $agentName }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="task">Task Description</label>
                        <input type="text" class="form-control" name="task" id="task" placeholder="Enter task..." required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Add Task</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
