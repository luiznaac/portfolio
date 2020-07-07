@extends('layouts.app')

@section('content')
    <h2>{{$stock->symbol}}</h2>
    <small>{{$stock->name}}</small>

    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stock_prices as $info)
                <tr>
                    <td>{{$info->date}}</td>
                    <td>{{$info->price}}</td>
                </tr>
                @endforeach

                @if(count($stock_prices) == 0)
                    <td>No info.</td>
                @endif
            </tbody>
        </table>
    </div>

    {!! Form::open(['action' => 'StocksController@loadInfoForDate']) !!}
        <div class="container">
            <div class="form-group">
                <div class="row">
                    <div class="col-auto">
                        {{Form::hidden('stock_id', $stock->id)}}
                        {{Form::date('date', '', ['class' => 'form-control', 'placeholder' => 'Date'])}}
                    </div>
                    <div class="col">
                        {{Form::submit('Get Info', ['class' => 'btn btn-primary'])}}
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@endsection
