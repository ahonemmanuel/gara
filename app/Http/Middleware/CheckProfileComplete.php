<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Vérifier les champs obligatoires
        $champsManquants = $this->getChampsManquants($user);

        if (!empty($champsManquants)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Veuillez compléter votre profil avant de créer une annonce.')
                ->with('required_fields', $champsManquants)
                ->with('intended_url', $request->fullUrl());
        }

        return $next($request);
    }

    /**
     * Récupérer les champs manquants du profil
     */
    private function getChampsManquants($user): array
    {
        $champsManquants = [];

        $champsRequis = [
            'name' => 'Nom complet',
            'email' => 'Email',
            'telephone' => 'Téléphone',
            'ville' => 'Ville',
        ];

        // Champs supplémentaires pour les casses
        if ($user->isCasse()) {
            $champsRequis['adresse'] = 'Adresse complète';
        }

        foreach ($champsRequis as $champ => $label) {
            if (empty($user->$champ)) {
                $champsManquants[$champ] = $label;
            }
        }

        return $champsManquants;
    }
}
