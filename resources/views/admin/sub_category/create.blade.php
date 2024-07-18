@extends('admin.layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Create Sub Category</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{route('sub-categories.index')}}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        <form action="" name="subCategoryForm" id="subCategoryForm">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name">Category</label>
                                <select name="category" id="category" class="form-control">
                                    <option value="">Select a category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <p></p>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Name">
                                <p></p>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input type="text" readonly name="slug" id="slug" class="form-control" placeholder="Slug">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 ">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 ">
                                <label for="showHome">Show on Home</label>
                                <select name="showHome" id="showHome" class="form-control">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="subcategory.html" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
        </form>

    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection


@section('customJs')
<script>
    $(document).ready(function() {
        $("#subCategoryForm").submit(function(event) {
            event.preventDefault();
            var element = $("#subCategoryForm");
            $("button[type=submit]").prop('disabled', true);

            $.ajax({
                url: '{{ route("sub-categories.store") }}',
                type: 'post',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);

                    if (response["status"] == true) {
                        window.location.href = "{{ route('sub-categories.index') }}";
                    } else {
                        var errors = response['errors'];
                        handleErrors(errors, ['name', 'slug', 'category']);
                    }
                },
                error: function(jqXHR, exception) {
                    console.log("Something went wrong!");
                }
            });
        });
    });

    function handleErrors(errors, fields) {
        fields.forEach(function(field) {
            if (errors[field]) {
                $("#" + field).addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback').html(errors[field][0]);
            } else {
                $("#" + field).removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback').html("");
            }
        });
    }

    function clearErrors(fields) {
        fields.forEach(function(field) {
            $("#" + field).removeClass('is-invalid')
                .siblings('p')
                .removeClass('invalid-feedback').html("");
        });
    }

    $("#name").on('input', function() {
        let element = $(this);
        $("button[type=submit]").prop('disabled', true);

        $.ajax({
            url: '{{ route("getSlug") }}',
            type: 'GET',
            data: {
                title: element.val()
            },
            dataType: 'json',
            success: function(response) {
                $("button[type=submit]").prop('disabled', false);
                if (response.status === true) {
                    $("#slug").val(response.slug);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                $("button[type=submit]").prop('disabled', false);
            }
        });
    });
</script>

@endsection
