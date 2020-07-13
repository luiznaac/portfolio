@extends('layouts.app')

@section('content')
    <h2>{{$stock->symbol}}</h2>
    <small>{{$stock->name}}</small>
    <br><br>
    @if($stock_type->type != \App\Model\Stock\StockType::ETF_TYPE)
    <h3>Dividends</h3>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Type</th>
                <th>Reference Date</th>
                <th>Date Paid</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stock_dividends as $dividend)
                <tr>
                    <td>{{$dividend->type}}</td>
                    <td>{{$dividend->reference_date}}</td>
                    <td>{{$dividend->date_paid}}</td>
                    <td>R${{$dividend->value}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
    <br><br>
    <h3>Historical Prices</h3>
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stock_prices as $price)
                <tr>
                    <td>{{$price->date}}</td>
                    <td>{{$price->price}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

