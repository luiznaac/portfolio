@extends('layouts.app')

@section('content')
    <h2>Bond Orders</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Bond</th>
                <th>Type</th>
                <th>Amount</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($bond_orders as $bond_order)
                <tr>
                    <td>{{$bond_order->date}}</td>
                    <td>{{$bond_order->bond_name}}</td>
                    <td>{{$bond_order->type}}</td>
                    <td>{{$bond_order->amount}}</td>
                    <td>
                    {!! Form::open(['action' => 'BondOrdersController@delete']) !!}
                        {{Form::hidden('id', $bond_order->id)}}
                        {{Form::submit('X', ['class' => 'btn btn-danger btn-sm'])}}
                    {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach

            @if(count($bond_orders) == 0)
                <td>No info.</td>
            @endif
            </tbody>
        </table>
    </div>

    <a href="/bonds/orders/create" class="btn btn-primary">Register Order</a>
@endsection
