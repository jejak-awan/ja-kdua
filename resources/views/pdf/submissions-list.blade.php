<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .header p {
            color: #7f8c8d;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #dee2e6;
            font-size: 10px;
            text-transform: uppercase;
            color: #495057;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-new {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        .status-read {
            background-color: #f1f3f5;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $form->name }} - Submissions</h1>
        <p>Exported on {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 120px;">Date</th>
                <th style="width: 80px;">Status</th>
                <th style="width: 150px;">Submitted By</th>
                @foreach($headers as $header)
                    <th>{{ ucfirst($header) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
                <tr>
                    <td>{{ $submission->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="status-badge {{ $submission->status === 'new' ? 'status-new' : 'status-read' }}">
                            {{ ucfirst($submission->status) }}
                        </span>
                    </td>
                    <td>
                        {{ $submission->user ? ($submission->user->name ?? $submission->user->email) : 'Guest (' . $submission->ip_address . ')' }}
                    </td>
                    @foreach($headers as $key)
                        <td>
                            @php
                                $val = $submission->data[$key] ?? '-';
                            @endphp
                            @if(is_array($val))
                                {{ implode(', ', $val) }}
                            @else
                                {{ $val }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
