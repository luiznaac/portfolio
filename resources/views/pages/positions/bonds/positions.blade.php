@extends('layouts.app')

@section('content')
    <h2>Bond Positions</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Bond</th>
                <th>Reference Date</th>
                <th>Amount Contributed</th>
                <th>Amount Now</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bond_positions as $position)
                <tr>
                    <td><a href="/positions/bonds/{{$position->bond_id}}">{{$bonds[$position->bond_id]->getBondName()}}</a></td>
                    <td>{{$position->date}}</td>
                    <td>{{$position->contributed_amount}}</td>
                    <td>{{$position->amount}}</td>
                </tr>
            @endforeach

            @if(count($bonds) == 0)
                <td>No info. Try consolidating positions.</td>
            @endif
            </tbody>
        </table>
    </div>
@endsection
