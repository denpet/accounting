<html>

<head>
    <title>{{ $title ?? 'Statement of Account' }}</title>
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
</head>

<body>
    <image src="{{ asset('/images/eden.png')}}" style="width: 200px" />
    <h1>Cash Discrepancy Report</h1>
    @foreach ($errors as $error)
    {{ $error }}<br />
    @endforeach
</body>