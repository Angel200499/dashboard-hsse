<aside class="sidebar">

    <div class="sidebar-logo">

        <img src="{{ asset('assets/images/logo/logo.png') }}"
             alt="Pertamina">

    </div>

    <ul class="sidebar-menu">

        <li>Dashboard</li>

        <li>Monitoring Temuan</li>

        <li>Detail Temuan</li>

        <li>Manajemen User</li>

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