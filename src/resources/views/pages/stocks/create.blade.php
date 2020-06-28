@extends('layouts.app')

@section('content')
    <h2>Create Stock</h2>
    {!! Form::open(['action' => 'StocksPagesController@apiRouteStore']) !!}
        <div class="form-group">
            {{Form::label('symbol', 'Symbol')}}
            {{Form::text('symbol', '', ['class' => 'form-control', 'placeholder' => 'Symbol'])}}
        </div>
        {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
    {!! Form::close() !!}
@endsection
