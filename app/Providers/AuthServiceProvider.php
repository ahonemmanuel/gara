<?php

namespace App\Providers;

use App\Models\PanierItem;
use App\Policies\PanierItemPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Commande;
use App\Policies\CommandePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Les mappings de modèles vers leurs policies correspondantes.
     *
     * @var array<class-string, class-string>
     */

    protected $policies = [
        Commande::class => CommandePolicy::class,
        PanierItem::class => PanierItemPolicy::class,
    ];

    /**
     * Enregistrer les policies d'authentification.
     */
    public function boot()
    {
        $this->registerPolicies();

        // Tu peux définir ici d'autres Gates si nécessaire
    }
}
