<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QC Checklist</title>

    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: #fff; }
        .center { text-align: center; }
        .tick { color: green; font-weight: bold; }
        .cross { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h2>Plumbing QC Checklist</h2>

<table>
    <thead>
        <tr>
            <th>Applicable</th>
            <th>Incorporated</th>
            <th>Confirmed</th>
            <th>Item</th>
        </tr>
    </thead>

    <tbody>
    @foreach($items as $item)
        <tr>
            <td class="center">{!! $item->applicable ? '✔' : '✖' !!}</td>
            <td class="center">{!! $item->incorporated ? '✔' : '✖' !!}</td>
            <td class="center">{!! $item->confirmed ? '✔' : '✖' !!}</td>
            <td>{{ $item->item_text }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
