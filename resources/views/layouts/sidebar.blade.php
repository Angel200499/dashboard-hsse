<aside class="sidebar">

    <div class="logo">

<img src="{{ asset('assets/logo/logo-pge.png') }}" alt="Logo PGE">    </div>

    <ul>

        <li>Dashboard</li>

        <li>Monitoring</li>

        <li>Import Data</li>

        <li>Users</li>

        <li>Reports</li>

        <li>Settings</li>

        <li>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; padding: 0; margin: 0; color: inherit; font: inherit; cursor: pointer; text-align: left; width: 100%;">
                    Logout
                </button>
            </form>
        </li>

    </ul>

</aside>