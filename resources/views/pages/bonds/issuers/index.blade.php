@extends('layouts.app')

@section('content')
    <h2>Bond Issuers</h2>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bond_issuers as $bond_issuer)
                <tr>
                    <td>{{$bond_issuer->name}}</td>
                </tr>
            @endforeach

            @if(count($bond_issuers) == 0)
                <tr>
                    <td>No issuers registered.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <a href="/bonds/issuers/create" class="btn btn-primary">Create Issuer</a>
@endsection
