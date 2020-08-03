@extends('layouts.app')

@section('content')
    <h2>Bonds</h2>
    <h3>Private</h3>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Rate of Return</th>
                <th>Maturity</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bonds as $bond)
                <tr>
                    <td>{{$bond->getBondName()}}</td>
                    <td>{{$bond['return']}}</td>
                    <td>{{$bond['days'] . ' days'}}</td>
                </tr>
            @endforeach

            @if(count($bonds) == 0)
                <tr>
                    <td>No bonds registered.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <h3>Treasury</h3>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Rate of Return</th>
                <th>Maturity</th>
            </tr>
            </thead>
            <tbody>
            @foreach($treasury_bonds as $treasury_bond)
                <tr>
                    <td>{{$treasury_bond->getTreasuryBondName()}}</td>
                    <td>{{$treasury_bond['return']}}</td>
                    <td>{{$treasury_bond['maturity_date']}}</td>
                </tr>
            @endforeach

            @if(count($treasury_bonds) == 0)
                <tr>
                    <td>No treasury bonds registered.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <a href="/bonds/create" class="btn btn-primary">Create Bond</a>
@endsection
