@extends('layouts.app')

@section('content')
    <h2>Stocks</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Symbol</th>
                <th>Type</th>
                <th>Name</th>
                <th>Last Price</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td><a href="/stocks/show/{{$stock->id}}">{{$stock->symbol}}</a></td>
                    <td>
                        @if(isset($stock->stock_type_id))
                            {{$stock_types[$stock->stock_type_id]['type']}}
                        @endif
                    </td>
                    <td>{{$stock->name}}</td>
                    <td>{{$stock->last_price}}</td>
                </tr>
            @endforeach

            @if(count($stocks) == 0)
                No stocks registered.
            @endif
            </tbody>
        </table>
    </div>

    {!! Form::open(['action' => 'StocksController@updateInfos']) !!}
    <div class="form-group">
        <div class="row">
            <div class="col">
                {{Form::submit('Update infos', ['class' => 'btn btn-primary'])}}
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection
