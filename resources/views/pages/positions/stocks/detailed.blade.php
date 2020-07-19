@extends('layouts.app')

@section('content')
    <h2>{{$stock->symbol}} Positions</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Quantity</th>
                <th>Average Price</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stock_positions as $position)
                <tr>
                    <td>{{$position->date}}</td>
                    <td>{{$position->quantity}}</td>
                    <td>{{$position->average_price}}</td>
                    <td>{{$position->amount}}</td>
                </tr>
            @endforeach

            @if(count($stock_positions) == 0)
                <td>No info. Try consolidating it.</td>
            @endif
            </tbody>
        </table>
    </div>
@endsection
