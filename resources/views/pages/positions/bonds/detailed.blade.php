@extends('layouts.app')

@section('content')
    <h2>{{$bond->getBondName()}} Positions</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bond_positions as $position)
                <tr>
                    <td>{{$position->date}}</td>
                    <td>{{$position->amount}}</td>
                </tr>
            @endforeach

            @if(count($bond_positions) == 0)
                <td>No info. Try consolidating it.</td>
            @endif
            </tbody>
        </table>
    </div>
@endsection
