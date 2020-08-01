@extends('layouts.app')

@section('content')
    <h2>Stock Positions</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Stock</th>
                <th>Reference Date</th>
                <th>Quantity</th>
                <th>Avg. Price Paid</th>
                <th>Amount Contributed</th>
                <th>Amount Now</th>
                <th>Last Price</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stock_positions as $position)
                <tr>
                    <td><a href="/positions/stocks/{{$position->stock_id}}">{{$stocks[$position->stock_id]->symbol}}</a></td>
                    <td>{{$position->date}}</td>
                    <td>{{$position->quantity}}</td>
                    <td>{{$position->average_price}}</td>
                    <td>{{$position->contributed_amount}}</td>
                    <td>{{$position->amount}}</td>
                    <td>{{$stocks[$position->stock_id]->last_price}}</td>
                </tr>
            @endforeach

            @if(count($stocks) == 0)
                <td>No info. Try consolidating positions.</td>
            @endif
            </tbody>
        </table>
    </div>
@endsection
