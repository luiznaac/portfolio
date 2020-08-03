@extends('layouts.app')

@section('content')
    <h2>Create Bond</h2>

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#bond" data-toggle="tab">Private Bond</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#treasury_bond" data-toggle="tab">Treasury Bond</a>
        </li>
    </ul>

    <div class="tab-content clearfix">
        <div class="tab-pane active" id="bond">
            {!! Form::open(['action' => 'BondsController@store']) !!}
            <div class="container">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('bond_issuer', 'Bond Issuer')}}
                            {{Form::select('bond_issuer_id', $bond_issuers, 0, ['class' => 'form-control'])}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('bond_type', 'Bond Type')}}
                            {{Form::select('bond_type_id', $bond_types, 0, ['class' => 'form-control'])}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-6">
                                    {{Form::label('index', 'Index (blank if NA)')}}
                                    {{Form::select('index_id', $indices, 0, ['class' => 'form-control'])}}
                                </div>
                                <div class="col-md-6">
                                    {{Form::label('index_rate', 'Rate (%)')}}
                                    {{Form::number('index_rate', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Rate (%)'])}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('interest_rate', 'Extra Interest Rate (%) (blank if NA)')}}
                            {{Form::number('interest_rate', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Interest Rate (%)'])}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('days', 'Days')}}
                            {{Form::number('days', '', ['class' => 'form-control', 'placeholder' => 'Days'])}}
                        </div>
                    </div>
                </div>
            </div>
            {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
            {!! Form::close() !!}
        </div>

        <div class="tab-pane" id="treasury_bond">
            {!! Form::open(['action' => 'BondsController@storeTreasury']) !!}
            <div class="container">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('index', 'Index (blank if NA)')}}
                            {{Form::select('index_id', $indices, 0, ['class' => 'form-control'])}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('interest_rate', 'Extra Interest Rate (%) (blank if NA)')}}
                            {{Form::number('interest_rate', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Interest Rate (%)'])}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::label('maturity_date', 'Maturity Date')}}
                            {{Form::date('maturity_date', '', ['class' => 'form-control', 'placeholder' => 'Matutiry Date'])}}
                        </div>
                    </div>
                </div>
            </div>
            {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
            {!! Form::close() !!}
        </div>
    </div>
@endsection
