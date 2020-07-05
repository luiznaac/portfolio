<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/">Portfolio</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbars" aria-controls="navbars" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbars">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="stocks-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Stocks</a>
                <div class="dropdown-menu" aria-labelledby="dropdown">
                    <a class="dropdown-item" href="/stocks">Listed</a>
                    <a class="dropdown-item" href="/stocks/create">Create</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="orders-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Orders</a>
                <div class="dropdown-menu" aria-labelledby="dropdown">
                    <a class="dropdown-item" href="/orders">Statement</a>
                    <a class="dropdown-item" href="/orders/create">Register</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="positions-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Positions</a>
                <div class="dropdown-menu" aria-labelledby="dropdown">
                    <a class="dropdown-item" href="/positions/stocks">Stocks</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
