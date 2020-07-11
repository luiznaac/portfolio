@extends('layouts.app')

@section('content')
    <div>
        <h2>Dashboard</h2>
        <table class="table">
            <thead>
            <tr>
                <th>Percentage</th>
                <th>Symbol</th>
                <th>Total Updated</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stock_positions_by_type as $stock_type_id => $stock_positions_and_percentage)
                <tr>
                    <td colspan="3">{{$stock_positions_and_percentage['percentage'] . '% - ' . $stock_types[$stock_type_id]['description']}}</td>
                </tr>
                <tr>
                @foreach($stock_positions_and_percentage['positions'] as $stock_position)
                    <td>{{$stock_position['percentage']. '%'}}</td>
                    <td>{{$stocks[$stock_position['position']->stock_id]['symbol']}}</td>
                    <td>{{$stock_position['position']->amount}}</td>
                @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
