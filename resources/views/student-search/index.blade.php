@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-search me-2"></i>Student Search</h1>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="fas fa-graduation-cap me-2"></i>Search Students by Course
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="courseSearchForm">
                        @csrf
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="category_id" class="form-label fw-semibold">
                                        <i class="fas fa-folder-open me-2"></i>Category
                                    </label>
                                    <select class="form-select form-select-lg shadow-sm" id="category_id" name="category_id" required>
                                        <option value="">Select a category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="course_id" class="form-label fw-semibold">
                                        <i class="fas fa-book me-2"></i>Course
                                    </label>
                                    <select class="form-select form-select-lg shadow-sm" id="course_id" name="course_id" required disabled>
                                        <option value="">Select a course</option>
                                    </select>
                                    <small class="form-text text-muted mt-1">
                                        <i class="fas fa-info-circle me-1"></i>Select a category first, then choose a course
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="searchError" class="alert alert-danger mt-4 d-none"></div>
                    </form>
                    
                    <div id="searchResults" class="mt-4">
                        <!-- Results will be loaded here via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function loadSearchResults(courseId, page = 1) {
        if (!courseId) {
            console.log('No course ID provided');
            return;
        }
        
        console.log('Loading search results for course ID:', courseId);
        
        // Get category ID
        const categoryId = $('#category_id').val();
        
        // Update URL with search parameters
        const url = new URL(window.location);
        url.searchParams.set('course_id', courseId);
        if (categoryId) {
            url.searchParams.set('category_id', categoryId);
        }
        if (page > 1) {
            url.searchParams.set('page', page);
        } else {
            url.searchParams.delete('page');
        }
        window.history.pushState({}, '', url);
        
        // Prepare request data
        const requestData = {
            course_id: courseId,
            category_id: categoryId,
            page: page,
            _token: '{{ csrf_token() }}'
        };
        
        console.log('Making AJAX request to:', '{{ route("student-search.search") }}');
        console.log('Request data:', requestData);
        
        // Make sure we're sending the correct content type
        $.ajax({
            url: '{{ route("student-search.search") }}',
            type: 'GET',
            data: requestData,
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                console.log('AJAX request started');
                $('#searchResults').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                $('#searchError').addClass('d-none');
            },
            success: function(response, status, xhr) {
                console.log('AJAX success - Status:', xhr.status);
                console.log('Response type:', typeof response);
                console.log('Response:', response);
                
                if (!response) {
                    console.error('Empty response received');
                    $('#searchError').text('Empty response from server').removeClass('d-none');
                    return;
                }
                
                // Check if response is a JSON object with html property
                if (typeof response === 'object' && response.html) {
                    // Create a container for the results
                    const $container = $('<div class="search-results-container"></div>');
                    
                    // Add the main content
                    $container.append(response.html);
                    
                    // Add pagination if available
                    if (response.pagination) {
                        const $pagination = $('<div class="mt-4"></div>').html(response.pagination);
                        $container.append($pagination);
                        
                        // Handle pagination clicks
                        $pagination.on('click', 'a.page-link', function(e) {
                            e.preventDefault();
                            const url = $(this).attr('href');
                            if (url) {
                                // Extract page number from URL
                                const page = new URL(url, window.location.origin).searchParams.get('page');
                                if (page) {
                                    // Get current course ID and load results for the new page
                                    const courseId = $('#course_id').val();
                                    loadSearchResults(courseId, page);
                                }
                            }
                        });
                    }
                    
                    // Update the DOM
                    $('#searchResults').html($container);
                } 
                // If it's already HTML (fallback)
                else if (typeof response === 'string') {
                    $('#searchResults').html(response);
                } 
                // If it's a different format
                else {
                    console.error('Unexpected response format:', response);
                    $('#searchError').html('Unexpected response format from server. See console for details.').removeClass('d-none');
                }
                
                console.log('Results updated in DOM');
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                let errorMessage = 'An error occurred while loading the results.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const jsonResponse = JSON.parse(xhr.responseText);
                        errorMessage = jsonResponse.message || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText.substring(0, 200);
                    }
                }
                
                $('#searchError').html('Error: ' + errorMessage + '<br>Status: ' + xhr.status + ' ' + xhr.statusText).removeClass('d-none');
                $('#searchResults').html('');
            },
            complete: function(xhr, status) {
                console.log('AJAX request completed with status:', status);
            }
        });
    }

    // Function to load courses for a category
    function loadCoursesForCategory(categoryId, selectedCourseId = null) {
        if (!categoryId) {
            $('#course_id').prop('disabled', true).empty().append('<option value="">Select a course</option>');
            $('#searchBtn').prop('disabled', true);
            return;
        }
        
        // Show loading state
        const courseSelect = $('#course_id');
        courseSelect.prop('disabled', true).empty().append('<option value="">Loading courses...</option>');
        
        $.ajax({
            url: '{{ route("student-search.courses") }}',
            type: 'GET',
            data: {
                category_id: categoryId
            },
            success: function(data) {
                courseSelect.empty().append('<option value="">Select a course</option>');
                
                if (data && data.length > 0) {
                    data.forEach(function(course) {
                        const option = new Option(course.text, course.id, false, selectedCourseId == course.id);
                        courseSelect.append(option);
                    });
                    
                    if (selectedCourseId) {
                        courseSelect.val(selectedCourseId).trigger('change');
                    }
                }
                
                courseSelect.prop('disabled', false);
                $('#searchBtn').prop('disabled', !selectedCourseId);
            },
            error: function() {
                courseSelect.empty().append('<option value="">Error loading courses</option>');
            }
        });
    }
    
    // Format course display in dropdown
    function formatCourse(course) {
        if (!course.id) return course.text;
        return $('<span>').text(course.text).addClass('text-dark');
    }
    
    // Format selected course
    function formatCourseSelection(course) {
        if (!course.id) return course.text;
        return $('<span>').text(course.text).addClass('text-dark');
    }
    
    // Function to handle course selection and trigger search
    function handleCourseSelection() {
        const courseId = $('#course_id').val();
        if (courseId) {
            loadSearchResults(courseId);
        } else {
            $('#searchResults').html('');
        }
    }
    
    // Function to load a specific course by ID
    function loadCourseById(courseId, categoryId) {
        if (!courseId) return;
        
        $.ajax({
            url: '{{ route("student-search.courses") }}',
            type: 'GET',
            data: {
                course_id: courseId
            },
            success: function(data) {
                if (data && data.length > 0) {
                    const course = data[0];
                    // Update the course select
                    const $courseSelect = $('#course_id');
                    if ($courseSelect.find('option[value="' + courseId + '"]').length === 0) {
                        const newOption = new Option(course.text, course.id, true, true);
                        $courseSelect.append(newOption).trigger('change');
                    }
                    $courseSelect.val(courseId).trigger('change');
                    
                    // Load the results
                    loadSearchResults(courseId);
                }
            },
            error: function() {
                console.error('Error loading course details');
            }
        });
    }
    
    $(document).ready(function() {
        // Check for URL parameters to restore search
        const urlParams = new URLSearchParams(window.location.search);
        const courseId = urlParams.get('course_id');
        const categoryId = urlParams.get('category_id');
        
        // Enable course select if category is already selected
        if (categoryId) {
            $('#category_id').val(categoryId);
            $('#course_id').prop('disabled', false);
            
            // If we have a course ID, load it
            if (courseId) {
                loadCourseById(courseId, categoryId);
            }
        }
        
        // Initialize Select2 for course dropdown
        const courseSelect = $('#course_id').select2({
            placeholder: 'Select a course',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("student-search.courses") }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        category_id: $('#category_id').val(),
                        search: params.term || '',
                        page: params.page || 1,
                        _token: '{{ csrf_token() }}'
                    };
                },
                processResults: function(data, params) {
                    // If there's an error, show it and return empty results
                    if (data.error) {
                        console.error('Error loading courses:', data);
                        showAlert('error', data.error);
                        return { results: [] };
                    }
                    
                    return {
                        results: data,
                        pagination: {
                            more: false
                        }
                    };
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showAlert('error', 'Error loading courses. Please try again.');
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: formatCourse,
            templateSelection: formatCourseSelection
        });
        
        // Initialize Select2 for course dropdown
        $('#course_id').select2({
            placeholder: 'Select a course',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("student-search.courses") }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        category_id: $('#category_id').val(),
                        search: params.term || '',
                        page: params.page || 1,
                        _token: '{{ csrf_token() }}'
                    };
                },
                processResults: function(data, params) {
                    // If there's an error, show it and return empty results
                    if (data.error) {
                        console.error('Error loading courses:', data);
                        showAlert('error', data.error);
                        return { results: [] };
                    }
                    
                    return {
                        results: data,
                        pagination: {
                            more: false
                        }
                };
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                showAlert('error', 'Error loading courses. Please try again.');
            },
            cache: true
        },
        minimumInputLength: 0,
        templateResult: formatCourse,
        templateSelection: formatCourseSelection
    });

    // Format how courses are displayed in the dropdown
    function formatCourse(course) {
        if (!course.id) return course.text;
        
        var $container = $(
            '<div class="d-flex justify-content-between align-items-center">' +
            '   <span class="course-name">' + course.text + '</span>' +
            '   <span class="badge bg-secondary ms-2">' + (course.shortname || '') + '</span>' +
            '</div>'
        );
        
        return $container;
    }
    
    // Format how the selected course is displayed
    function formatCourseSelection(course) {
        if (!course.id) return course.text;
        return course.text;
    }

    // Show alert message
    function showAlert(type, message) {
        // Remove any existing alerts
        $('.alert-dismissible').remove();
        
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert the alert after the form
        $('#studentSearchForm').after(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert-dismissible').alert('close');
        }, 5000);
    }

    // Enable/disable course dropdown based on category selection
    $('#category_id').change(function() {
        const categoryId = $(this).val();
        const $courseSelect = $('#course_id');
        
        if (categoryId) {
            $courseSelect.prop('disabled', false).val(null).trigger('change');
            // Clear any previous error messages
            $('.alert-dismissible').remove();
            // Open the course dropdown
            setTimeout(() => {
                $courseSelect.select2('open');
            }, 100);
        } else {
            $courseSelect.prop('disabled', true).val(null).trigger('change');
        }
        updateSearchButton();
    });

        // Handle form submission - prevent default form submission
        $('#courseSearchForm').on('submit', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Handle category change
        $('#category_id').on('change', function() {
            const categoryId = $(this).val();
            
            // Reset course select
            const $courseSelect = $('#course_id');
            $courseSelect.val(null).trigger('change');
            $courseSelect.empty().append('<option value="">Select a course</option>');
            $('#searchResults').html('');
            
            if (categoryId) {
                // Enable course select
                $courseSelect.prop('disabled', false);
                
                // Update URL with category ID
                const url = new URL(window.location);
                url.searchParams.set('category_id', categoryId);
                url.searchParams.delete('course_id');
                window.history.pushState({}, '', url);
                
                // Focus on course select after a short delay
                setTimeout(() => {
                    $courseSelect.select2('open');
                }, 100);
            } else {
                // Disable course select if no category selected
                $courseSelect.prop('disabled', true);
                $('#searchBtn').prop('disabled', true);
                
                // Clear URL parameters
                const url = new URL(window.location);
                url.searchParams.delete('category_id');
                url.searchParams.delete('course_id');
                window.history.pushState({}, '', url);
            }
        });
        
        // Handle course selection
        $('#course_id').on('change', function() {
            const courseId = $(this).val();
            const categoryId = $('#category_id').val();
            
            if (courseId) {
                // Update URL with both category and course IDs
                const url = new URL(window.location);
                if (categoryId) {
                    url.searchParams.set('category_id', categoryId);
                }
                url.searchParams.set('course_id', courseId);
                window.history.pushState({}, '', url);
                
                // Load the results
                loadSearchResults(courseId);
            } else {
                $('#searchResults').html('');
                
                // Update URL to remove course_id
                const url = new URL(window.location);
                url.searchParams.delete('course_id');
                window.history.pushState({}, '', url);
            }
        });
        
        // Restore search from URL parameters if they exist
        if (categoryId) {
            $('#category_id').val(categoryId).trigger('change');
            
            if (courseId) {
                // Small delay to ensure Select2 is initialized
                setTimeout(() => {
                    // Create a new option for the course
                    const newOption = new Option('Loading...', courseId, true, true);
                    $('#course_id').append(newOption).trigger('change');
                    
                    // Load the course details
                    $.ajax({
                        url: '{{ route("student-search.courses") }}',
                        type: 'GET',
                        data: {
                            category_id: categoryId,
                            course_id: courseId
                        },
                        success: function(data) {
                            if (data && data.length > 0) {
                                const course = data[0];
                                // Update the option with the correct text
                                const option = $('#course_id').find('option[value="' + courseId + '"]');
                                option.text(course.text).prop('selected', true);
                                $('#course_id').trigger('change');
                                
                                // Load the results
                                loadSearchResults(courseId);
                            }
                        }
                    });
                }, 100);
            }
        }
});
</script>
@endpush

<style>
    /* Form Controls */
    .form-control, .form-select {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    /* Select2 Customization */
    .select2-container--default .select2-selection--single {
        height: 48px;
        padding: 0.5rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        width: 30px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px;
        color: #495057;
        padding-left: 0;
    }
    
    .select2-container--default .select2-selection--single:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .select2-dropdown {
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .select2-results__option {
        padding: 0.5rem 1rem;
    }
    
    .select2-results__option--highlighted {
        background-color: #f0f7ff;
        color: #0d6efd;
    }
    
    /* Card Styling */
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: #fff;
    }
    
    /* Loading Spinner */
    .spinner-border {
        width: 2rem;
        height: 2rem;
        border-width: 0.2em;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .form-select, .select2-container--default .select2-selection--single {
            height: 44px;
        }
    }
</style>
@endsection
