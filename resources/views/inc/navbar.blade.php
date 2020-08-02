@inject('helper', 'App\Http\Controllers\PagesHelperController')

<nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Portfolio') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="stocks-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Stocks</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown">
                        <a class="dropdown-item" href="/stocks">Listed</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="positions-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Bonds</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown">
                        <a class="dropdown-item" href="/bonds">Listed</a>
                        <a class="dropdown-item" href="/bonds/issuers">Issuers</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="orders-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Orders</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown">
                        <a class="dropdown-item" href="/stocks/orders">Stock Statement</a>
                        <a class="dropdown-item" href="/stocks/orders/create">Stock</a>
                        <a class="dropdown-item" href="/bonds/orders">Bond Statement</a>
                        <a class="dropdown-item" href="/bonds/orders/create">Bond</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="positions-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Positions</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown">
                        <a class="dropdown-item" href="/positions/stocks">Stocks</a>
                        <a class="dropdown-item" href="/positions/bonds">Bonds</a>
                    </div>
                </li>
                @if($helper::getConsolidationState() == 0)
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-success btn-sm" disabled>Consolidated</button>
                </li>
                @elseif($helper::getConsolidationState() == 1)
                {!! Form::open(['action' => 'ConsolidatorController@consolidate']) !!}
                <li class="nav-item">
                    {{Form::submit('Consolidate', ['class' => 'nav-link btn btn-danger btn-sm'])}}
                </li>
                {!! Form::close() !!}
                @else
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-info btn-sm" disabled>Consolidating</button>
                </li>
                @endif
                @endauth
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
