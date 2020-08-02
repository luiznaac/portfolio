@extends('layouts.app')

@section('content')
    <h2>Create Bond</h2>
    {!! Form::open(['action' => 'BondsController@store']) !!}
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
        {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
    {!! Form::close() !!}
@endsection
