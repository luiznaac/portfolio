@extends('layouts.app')

@section('content')
    <h2>Stocks</h2>
    @foreach($stocks as $stock)
        <a href="/stocks/{{$stock->id}}">{{$stock->symbol}}</a>{{" - $stock->name"}}<br>
    @endforeach

    @if(count($stocks) == 0)
        No stocks registered.
    @endif

    <a href="/stocks/create" class="btn btn-primary">Register Stock</a>
@endsection
