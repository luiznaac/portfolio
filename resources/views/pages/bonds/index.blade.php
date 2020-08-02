@extends('layouts.app')

@section('content')
    <h2>Bonds</h2>
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
                    <td>{{$bond->getReturnRateString()}}</td>
                    <td>{{$bond->days . ' days'}}</td>
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
