{{-- resources/views/layouts/casse.blade.php --}}
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Casse Automobile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<!-- Sidebar -->
<div class="flex h-screen">
    <div class="w-64 bg-blue-800 text-white">
        <div class="p-4">
            <h1 class="text-2xl font-bold">Casse Pro</h1>
            <p class="text-blue-200 text-sm">{{ auth()->user()->name }}</p>
        </div>

        <nav class="mt-6">
            <a href="{{ route('casse.dashboard') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.dashboard') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
            <a href="{{ route('casse.vehicules') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.vehicules*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-car mr-2"></i>Véhicules
            </a>
            <a href="{{ route('casse.stock') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.stock*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-boxes mr-2"></i>Stock Pièces
            </a>
            <a href="{{ route('casse.commandes') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.commandes*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-shopping-cart mr-2"></i>Commandes
                @if(($commandesEnAttente ?? 0) > 0)
                    <span class="bg-red-500 text-white rounded-full px-2 py-1 text-xs ml-2">{{ $commandesEnAttente }}</span>
                @endif
            </a>
            <a href="{{ route('casse.ventes-epaves') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.ventes-epaves*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-car-crash mr-2"></i>Ventes d'Épaves
                @if(($demandesEpaves ?? 0) > 0)
                    <span class="bg-yellow-500 text-white rounded-full px-2 py-1 text-xs ml-2">{{ $demandesEpaves }}</span>
                @endif
            </a>
            <a href="{{ route('casse.notifications') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.notifications*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-bell mr-2"></i>Notifications
                @if(($notificationsNonLues ?? 0) > 0)
                    <span class="bg-red-500 text-white rounded-full px-2 py-1 text-xs ml-2">{{ $notificationsNonLues }}</span>
                @endif
            </a>
            <a href="{{ route('casse.profile') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('casse.profile*') ? 'bg-blue-700' : '' }}">
                <i class="fas fa-user mr-2"></i>Profil
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="flex justify-between items-center p-4">
                <h2 class="text-xl font-semibold">@yield('title')</h2>
                <div class="flex items-center space-x-4">
                    <!-- Notifications rapides -->
                    <div class="relative">
                        <a href="{{ route('casse.notifications') }}" class="text-gray-600 hover:text-blue-600">
                            <i class="fas fa-bell text-xl"></i>
                            @if(($notificationsNonLues ?? 0) > 0)
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center">
                                    {{ $notificationsNonLues }}
                                </span>
                            @endif
                        </a>
                    </div>
                    <span class="text-gray-600">Bienvenue, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@yield('scripts')
</body>
</html>
