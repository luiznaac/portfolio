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
                            <p style="font-size: 1.5rem">{{'R$' . $contributed_amount}}</p>
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
                        @if($updated_amount < 0)
                            <p class="text-danger" style="font-size: 1.5rem">{{'R$' . $updated_amount}}</p>
                        @else
                            <p class="text-success" style="font-size: 1.5rem">{{'R$' . $updated_amount}}</p>
                        @endif
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="text-center">
                            @if($updated_amount < 0)
                                <p class="text-danger" style="font-size: 0.8rem">{{'R$' . ($updated_amount - $contributed_amount)}}</p>
                            @else
                                <p class="text-success" style="font-size: 0.8rem">{{'R$' . ($updated_amount - $contributed_amount)}}</p>
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
            <h2>{{$stock_allocation . '%'}} - Stocks</h2>
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
                @foreach($stock_positions_list as $stock_type_id => $stock_positions)
                    <tr>
                        <td colspan="5"><b>{{$stock_type_allocations[$stock_type_id] . '% - ' . $stock_types[$stock_type_id]['description']}}</b></td>
                    </tr>
                    @foreach($stock_positions as $stock_position)
                    <tr>
                        <td>{{$stock_allocations[$stock_position['stock_id']]. '%'}}</td>
                        <td>{{$stock_position['symbol']}}</td>
                        <td>{{'R$' . $stock_position['amount']}}</td>
                        <td>
                        @if($stock_position['amount'] - $stock_position['contributed_amount'] < 0)
                            <p class="text-danger">{{'R$' . ($stock_position['amount'] - $stock_position['contributed_amount'])}}</p>
                        @else
                            <p class="text-success">{{'R$' . ($stock_position['amount'] - $stock_position['contributed_amount'])}}</p>
                        @endif
                        </td>
                        <td>
                            @if($stock_position['amount'] - $stock_position['contributed_amount'] < 0)
                                <p class="text-danger">{{round((($stock_position['amount'] - $stock_position['contributed_amount'])/$stock_position['contributed_amount'])*100, 2) . '%'}}</p>
                            @else
                                <p class="text-success">{{round((($stock_position['amount'] - $stock_position['contributed_amount'])/$stock_position['contributed_amount'])*100, 2) . '%'}}</p>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="row">
            <h2>{{$bond_allocation . '%'}} - Bonds</h2>
        </div>
        <div class="row">
            <table class="table">
                <thead>
                <tr>
                    <th>Percentage</th>
                    <th>Bond</th>
                    <th>Total Updated</th>
                    <th>Result</th>
                    <th>Variation</th>
                </tr>
                </thead>
                <tbody>
                @foreach($bond_positions_list as $bond_type_id => $bond_positions)
                    @foreach($bond_positions as $bond_position)
                        <tr>
                            <td>{{$bond_allocations[$bond_position['bond_id']]. '%'}}</td>
                            <td>{{$bond_position['bond_name']}}</td>
                            <td>{{'R$' . $bond_position['amount']}}</td>
                            <td>
                                @if($bond_position['result'] < 0)
                                    <p class="text-danger">{{'R$' . $bond_position['result']}}</p>
                                @else
                                    <p class="text-success">{{'R$' . $bond_position['result']}}</p>
                                @endif
                            </td>
                            <td>
                                @if($bond_position['variation'] < 0)
                                    <p class="text-danger">{{$bond_position['variation'] . '%'}}</p>
                                @else
                                    <p class="text-success">{{$bond_position['variation'] . '%'}}</p>
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
