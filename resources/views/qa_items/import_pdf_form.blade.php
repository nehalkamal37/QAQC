@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 mt-4">

    <h2 class="mb-4">Upload Checklist PDF</h2>

   
<form action="{{ route('checklist.preview') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <div class="mb-3">
            <label class="form-label">Project Name:</label>
            <input type="text" name="project_name" class="form-control"
                   value="{{ $project_name ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Project Number:</label>
            <input type="text" name="project_number" class="form-control"
                   value="{{ $project_number ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Checklist PDF:</label>
            <input type="file" name="checklist_pdf" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload & Preview</button>
    </form>


    <!-- ===================== PREVIEW TABLE ===================== -->
    @if(isset($previewData))

        <h4>Preview Extracted Checklist</h4>

        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Applicable</th>
                    <th>Incorporated</th>
                    <th>Confirmed</th>
                    <th>Item Text</th>
                </tr>
            </thead>

            <tbody>
            @foreach($previewData as $item)
                <tr>
                    <td class="text-center">{!! $item['applicable'] ? '✔' : '✖' !!}</td>
                    <td class="text-center">{!! $item['incorporated'] ? '✔' : '✖' !!}</td>
                    <td class="text-center">{!! $item['confirmed'] ? '✔' : '✖' !!}</td>
                    <td>{{ $item['item_text'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    @endif

</div>
@endsection
