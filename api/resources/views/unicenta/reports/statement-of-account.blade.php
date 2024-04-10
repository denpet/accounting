<html>

<head>
    <title>{{ $title ?? 'Statement of Account' }}</title>
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
</head>

<body>
    <span>
        <image src="{{ asset('/images/eden.png')}}" style="width: 200px" />
        <h1>Statement of Account</h1>
        <p>{{ $customer->name }}</p>
        <hr>
        <table>
            <tr>
                <th align='left'>Name</th>
                <th align='right'>Units</th>
                <th align='right'>Price</th>
                <th align='right'>Amount</th>
            </tr>
            @foreach ($tickets as $ticket)
            <tr>
                <td><b>Ticket Id: {{ $ticket['id'] }}</b></td>
                <td align='left'><b>Date: {{ $ticket['date'] }}</b></td>
            </tr>
            @foreach ($ticket['rows'] as $ticketRow)
            <tr>
                <td>{{ $ticketRow['name'] }}</td>
                <td align='right'>{{ $ticketRow['units'] }}</td>
                <td align='right'>{{ number_format($ticketRow['price']) }}</td>
                <td align='right'>{{ number_format($ticketRow['price'] * $ticketRow['units']) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan=2 />
                <td align='right'><b>Total:</b></td>
                <td align='right'><b>{{ number_format($ticket['total']) }}</b></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            @endforeach
            <tr>
                <td colspan=2 />
                <td align='right'><b>Sub total:</b></td>
                <td align='right'><b>{{ number_format($ticket_total) }}</b></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td><b>Payments</b></td>
            </tr>
            @foreach ($payments as $payment)
            <tr>
                <td>{{ $payment->date }}</td>
                <td>{{ $payment->payment }}</td>
                <td />
                <td align='right'>{{ number_format($payment->total) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan=2 />
                <td align='right'><b>Total:</b></td>
                <td align='right'><b>{{ number_format($payment_total) }}</b></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan=2 />
                <td align='right'><b>Grand total:</b></td>
                <td align='right'><b>{{ number_format($total) }}</b></td>
            </tr>
        </table>
</body>

</html>