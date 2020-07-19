@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <h1>Dashboard</h1>
        </div>
        <div class="row" style="padding-top: 15px; padding-bottom: 30px">
            <div class="col-sm">
                <div class="container">
                    <div class="row d-flex justify-content-center">
                        <h2>Contributed</h2>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="text-center">
                            <p style="font-size: 1.5rem">{{'R$' . $amount_contributed}}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="container ">
                    <div class="row d-flex justify-content-center">
                        <h2>Result</h2>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="text-center">
                        @if($amount_updated < 0)
                            <p class="text-danger" style="font-size: 1.5rem">{{'R$' . $amount_updated}}</p>
                        @else
                            <p class="text-success" style="font-size: 1.5rem">{{'R$' . $amount_updated}}</p>
                        @endif
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="text-center">
                            @if($amount_updated < 0)
                                <p class="text-danger" style="font-size: 0.8rem">{{'R$' . ($amount_updated - $amount_contributed)}}</p>
                            @else
                                <p class="text-success" style="font-size: 0.8rem">{{'R$' . ($amount_updated - $amount_contributed)}}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="container">
                    <div class="row d-flex justify-content-center">
                        <h2>Variation</h2>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="text-center">
                        @if($overall_variation < 0)
                            <p class="text-danger" style="font-size: 1.5rem">{{$overall_variation . '%'}}</p>
                        @else
                            <p class="text-success" style="font-size: 1.5rem">{{$overall_variation . '%'}}</p>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="container">
                    <div class="row d-flex justify-content-center">
                        <h2>Dividends</h2>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="text-center">
                            <p style="font-size: 1.5rem">{{'R$' . $dividends_amount}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
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
    </div>
@endsection
