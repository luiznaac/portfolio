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
                <th>Last Price</th>
                <th>Amount Contributed</th>
                <th>Amount Now</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stock_positions as $position)
                <tr>
                    <td><a href="/positions/stocks/{{$position->stock_id}}">{{$stocks[$position->stock_id]->symbol}}</a></td>
                    <td>{{$position->date}}</td>
                    <td>{{$position->quantity}}</td>
                    <td>{{'R$' . $position->average_price}}</td>
                    <td>
                        @if($stocks[$position->stock_id]->last_price < $position->average_price)
                            <p class="text-danger">{{'R$' . $stocks[$position->stock_id]->last_price}}</p>
                        @else
                            <p class="text-success">{{'R$' . $stocks[$position->stock_id]->last_price}}</p>
                        @endif
                    </td>
                    <td>{{'R$' . $position->contributed_amount}}</td>
                    <td>{{'R$' . $position->amount}}</td>
                </tr>
            @endforeach

            @if(count($stocks) == 0)
                <td>No info. Try consolidating positions.</td>
            @endif
            </tbody>
        </table>
    </div>
@endsection
