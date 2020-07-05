@extends('layouts.app')

@section('content')
    <h2>Stock Positions</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Stock</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td><a href="/positions/stocks/{{$stock->id}}">{{$stock->symbol}}</a></td>
                </tr>
            @endforeach

            @if(count($stocks) == 0)
                <td>No info.</td>
            @endif
            </tbody>
        </table>
    </div>
@endsection
