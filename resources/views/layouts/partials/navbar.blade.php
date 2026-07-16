<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">

    <div class="container-fluid">

        <a
            class="navbar-brand fw-semibold"
            href="{{ route('dashboard.index') }}"
        >
            DNS Analytics
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMenu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarMenu"
        >

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <li class="nav-item">

                    <a
                        class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}"
                        href="{{ route('dashboard.index') }}"
                    >
                        Dashboard
                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link {{ request()->routeIs('dns-queries.*') ? 'active' : '' }}"
                        href="{{ route('dns-queries.index') }}"
                    >
                        Query Log
                    </a>

                </li>

            </ul>

            @auth

                <ul class="navbar-nav">

                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                        >
                            {{ auth()->user()->name }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>

                                <form
                                    action="{{ route('logout') }}"
                                    method="POST"
                                >

                                    @csrf

                                    <button
                                        class="dropdown-item"
                                        type="submit"
                                    >
                                        Logout
                                    </button>

                                </form>

                            </li>

                        </ul>

                    </li>

                </ul>

            @endauth

        </div>

    </div>

</nav>