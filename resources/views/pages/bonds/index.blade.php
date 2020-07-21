@extends('layouts.app')

@section('content')
    <h2>Bonds</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Issuer</th>
                <th>Type</th>
                <th>Rate of Return</th>
                <th>Maturity Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bonds as $bond)
                <tr>
                    <td>{{$bond->bond_issuer_id}}</td>
                    <td>{{$bond->bond_type_id}}</td>
                    <td>{{$bond->index_id}}</td>
                    <td>{{$bond->maturity_date}}</td>
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

    <a href="/bonds/create" class="btn btn-primary">Create Bond</a>
@endsection
