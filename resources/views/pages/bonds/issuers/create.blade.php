@extends('layouts.app')

@section('content')
    <h2>Create Issuer</h2>
    {!! Form::open(['action' => 'BondIssuersController@store']) !!}
        <div class="form-group">
            <div class="row">
                <div class="col-md-3">
                    {{Form::label('name', 'Name')}}
                    {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
                </div>
            </div>
        </div>
        {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
    {!! Form::close() !!}
@endsection
