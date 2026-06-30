<table>
    {{-- Project Title --}}
    <tr>
        <td colspan="9" style="text-align: center; font-size: 18px; font-weight: bold;">
            {{ strtoupper($project->name) }}
        </td>
    </tr>

    <tr></tr>

    {{-- Project Information --}}
    <tr>
        <td><strong>Project Name</strong></td>
        <td>{{ $project->name }}</td>
    </tr>

    <tr>
        <td><strong>Status</strong></td>
        <td>{{ $project->status }}</td>
    </tr>

    <tr>
        <td><strong>Team Leader</strong></td>
        <td>{{ optional($project->teamLeader)->name }}</td>
    </tr>

    <tr>
        <td><strong>Deadline</strong></td>
        <td>{{ $project->deadline }}</td>
    </tr>

    <tr>
        <td><strong>Description</strong></td>
        <td>{{ $project->description }}</td>
    </tr>

    <tr></tr>
    <tr></tr>

    {{-- Task Heading --}}
    <tr>
        <td colspan="9">
            <strong>Task Details</strong>
        </td>
    </tr>

    {{-- Table Header --}}
    <tr>
        <th>Sr No</th>
        <th>Task Name</th>
        <th>Description</th>
        <th>Member</th>
        <th>Status</th>
        <th>Priority</th>
        <th>Allocated Duration</th>
        <th>Start Time</th>
        <th>Due Date</th>
    </tr>

    @foreach($project->tasks as $index => $task)
        <tr>
            <td>{{ $index + 1 }}</td>

            <td>{{ $task->title }}</td>

            <td>{{ $task->description }}</td>

            <td>
                @if($task->assigned_members)
                    {{ $task->assigned_members->pluck('name')->join(', ') }}
                @endif
            </td>

            <td>{{ $task->status }}</td>

            <td>{{ $task->priority }}</td>

            <td>{{ $task->allocated_duration }}</td>

            <td>{{ $task->start_time }}</td>

            <td>{{ $task->due_date }}</td>
        </tr>
    @endforeach
</table>
