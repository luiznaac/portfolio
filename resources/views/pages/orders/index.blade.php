@extends('layouts.app')

@section('content')
    <h2>Orders</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Stock</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Cost</th>
                <th>Average Price</th>
                <th>Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{$order->date}}</td>
                    <td>{{$order->getStockSymbol()}}</td>
                    <td>{{$order->type}}</td>
                    <td>{{$order->quantity}}</td>
                    <td>{{$order->price}}</td>
                    <td>{{$order->cost}}</td>
                    <td>{{$order->average_price}}</td>
                    <td>{{$order->getTotal()}}</td>
                    <td>
                        {!! Form::open(['action' => 'OrdersController@delete']) !!}
                            {{Form::hidden('id', $order->id)}}
                            {{Form::submit('X', ['class' => 'btn btn-danger btn-sm'])}}
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach

            @if(count($orders) == 0)
                <td>No info.</td>
            @endif
            </tbody>
        </table>
    </div>

    <a href="/orders/create" class="btn btn-primary">Register Order</a>
@endsection
