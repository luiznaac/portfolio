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
                <th>Result</th>
                <th>Variation</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stock_positions_by_type as $stock_type_id => $stock_positions_and_percentage)
                <tr>
                    <td colspan="5"><b>{{$stock_positions_and_percentage['percentage'] . '% - ' . $stock_types[$stock_type_id]['description']}}</b></td>
                </tr>
                @foreach($stock_positions_and_percentage['positions'] as $stock_position)
                <tr>
                    <td>{{$stock_position['percentage']. '%'}}</td>
                    <td>{{$stocks[$stock_position['position']->stock_id]['symbol']}}</td>
                    <td>{{'R$' . $stock_position['position']->amount}}</td>
                    <td>
                    @if($stock_position['gross_result'] < 0)
                        <p class="text-danger">{{'R$' . $stock_position['gross_result']}}</p>
                    @else
                        <p class="text-success">{{'R$' . $stock_position['gross_result']}}</p>
                    @endif
                    </td>
                    <td>
                        @if($stock_position['gross_result'] < 0)
                            <p class="text-danger">{{$stock_position['gross_result_percentage'] . '%'}}</p>
                        @else
                            <p class="text-success">{{$stock_position['gross_result_percentage'] . '%'}}</p>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
