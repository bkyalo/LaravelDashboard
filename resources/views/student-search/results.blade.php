<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Student Search Results</h3>
    <button type="button" class="btn btn-outline-secondary" onclick="$('#searchResults').html(''); $('#course_id').val(null).trigger('change');">
        <i class="bi bi-arrow-left me-2"></i>New Search
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Course: {{ $course->fullname }}</h5>
    </div>
    <div class="card-body">
        @if($students->isEmpty())
            <div class="alert alert-info mb-0">No students found for this course.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->firstname }} {{ $student->lastname }}</td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="View Profile">
                                        <i class="bi bi-person"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
