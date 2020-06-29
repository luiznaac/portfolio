@extends('layouts.app')

@section('content')
    <h2>Register Order</h2>
    {!! Form::open(['action' => 'OrdersPagesController@apiRouteStore']) !!}
        <div class="container">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2">
                        {{Form::label('symbol', 'Stock Symbol')}}
                        {{Form::text('symbol', '', ['class' => 'form-control', 'placeholder' => 'Stock Symbol'])}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        {{Form::label('date', 'Order Date')}}
                        {{Form::date('date', '', ['class' => 'form-control', 'placeholder' => 'Order Date'])}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        {{Form::label('type', 'Order Type')}}
                        {{Form::select('type', ['buy' => 'Buy', 'sell' => 'Sell'], 'buy', ['class' => 'form-control'])}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        {{Form::label('quantity', 'Quantity')}}
                        {{Form::number('quantity', '', ['class' => 'form-control', 'placeholder' => 'Quantity'])}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        {{Form::label('price', 'Price')}}
                        {{Form::number('price', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Price'])}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        {{Form::label('cost', 'Cost')}}
                        {{Form::number('cost', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Cost'])}}
                    </div>
                </div>
            </div>
        </div>
        {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
    {!! Form::close() !!}
@endsection
