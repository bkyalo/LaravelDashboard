@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ $title ?? 'Error' }}
                    </h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $description ?? 'An error occurred while processing your request.' }}</h5>
                    
                    <div class="alert alert-danger mt-4">
                        <strong>Error Message:</strong>
                        <pre class="mb-0 mt-2 p-2 bg-dark text-white rounded">{{ $error['message'] }}</pre>
                    </div>

                    <div class="mt-4">
                        <h5>Error Details:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th style="width: 120px;">File</th>
                                    <td>{{ $error['file'] }}</td>
                                </tr>
                                <tr>
                                    <th>Line</th>
                                    <td>{{ $error['line'] }}</td>
                                </tr>
                                <tr>
                                    <th>Time</th>
                                    <td>{{ now() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if(isset($error['trace']))
                    <div class="mt-4">
                        <h5>Stack Trace:</h5>
                        <div style="max-height: 300px; overflow-y: auto;" class="bg-dark text-white p-3 rounded">
                            <pre style="white-space: pre-wrap; margin: 0;">{{ $error['trace'] }}</pre>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="bi bi-house"></i> Go to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
