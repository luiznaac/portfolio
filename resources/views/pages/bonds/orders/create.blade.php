@extends('layouts.app')

@section('content')
    <h2>Register Order</h2>
    {!! Form::open(['action' => 'BondOrdersController@store']) !!}
    <div class="container">
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {{Form::label('bond_id', 'Bond')}}
                    {{Form::select('bond_id', $bonds, 0, ['class' => 'form-control'])}}
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('date', 'Order Date')}}
                            {{Form::date('date', '', ['class' => 'form-control', 'placeholder' => 'Order Date'])}}
                        </div>
                        <div class="col-md-3">
                            {{Form::label('type', 'Order Type')}}
                            {{Form::select('type', ['buy' => 'Buy', 'sell' => 'Sell'], 'buy', ['class' => 'form-control'])}}
                        </div>
                        <div class="col-md-3">
                            {{Form::label('amount', 'Amount')}}
                            {{Form::number('amount', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Amount'])}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
    {!! Form::close() !!}
@endsection
