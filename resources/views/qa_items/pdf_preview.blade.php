<table class="table table-bordered">
    <thead>
        <tr>
            <th>Section</th>
            <th>Item</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($previewData as $row)
            <tr>
                <td>{{ $row['section'] }}</td>
                <td>{{ $row['item'] }}</td>
                <td>{{ $row['status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<form action="{{ route('qa_items.importPdfConfirm', $sheet->id) }}" method="POST">

    @csrf

    @foreach ($previewData as $i => $item)
        <input type="hidden" name="items[{{ $i }}][section]" value="{{ $item['section'] }}">
        <input type="hidden" name="items[{{ $i }}][item]" value="{{ $item['item'] }}">
        <input type="hidden" name="items[{{ $i }}][status]" value="{{ $item['status'] }}">
    @endforeach

    <button class="btn btn-primary">Confirm Import</button>
</form>
