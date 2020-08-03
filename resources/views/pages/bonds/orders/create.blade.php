@extends('layouts.app')

@section('content')
    <h2>Register Order</h2>

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#bonds" data-toggle="tab">Private Bond</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#treasury_bonds" data-toggle="tab">Treasury Bond</a>
        </li>
    </ul>

    <div class="tab-content clearfix">
        <div class="tab-pane active" id="bonds">
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
        </div>

        <div class="tab-pane" id="treasury_bonds">
            {!! Form::open(['action' => 'BondOrdersController@storeTreasury']) !!}
            <div class="container">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            {{Form::label('treasury_bond_id', 'Treasury Bond')}}
                            {{Form::select('treasury_bond_id', $treasury_bonds, 0, ['class' => 'form-control'])}}
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
        </div>
    </div>
@endsection
