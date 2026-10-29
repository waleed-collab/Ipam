{{-- Vue : Journal d'audit (personnel uniquement).
     Trace les connexions/déconnexions de tous les utilisateurs, ainsi que les modifications
     (création/modification/suppression/téléchargement) effectuées par l'admin et le manager. --}}
@extends('layouts.app')
@section('titre', "Journal d'audit")
@section('contenu')
    <header class="page">
        <div>
            <h1>Journal d'audit</h1>
            <div class="sous-titre">Connexions de tous les utilisateurs et modifications effectuées par le personnel.</div>
            {{-- [DESIGN] Filtres en pastilles par type d'action --}}
            <div class="filtres">
                <a href="{{ route('journal-audit.index') }}" class="{{ request('action') ? '' : 'actif' }}">Toutes</a>
                <a href="{{ route('journal-audit.index', ['action' => 'connexion']) }}" class="{{ request('action') === 'connexion' ? 'actif' : '' }}">Connexions</a>
                <a href="{{ route('journal-audit.index', ['action' => 'deconnexion']) }}" class="{{ request('action') === 'deconnexion' ? 'actif' : '' }}">Déconnexions</a>
                <a href="{{ route('journal-audit.index', ['action' => 'creation']) }}" class="{{ request('action') === 'creation' ? 'actif' : '' }}">Créations</a>
                <a href="{{ route('journal-audit.index', ['action' => 'modification']) }}" class="{{ request('action') === 'modification' ? 'actif' : '' }}">Modifications</a>
                <a href="{{ route('journal-audit.index', ['action' => 'suppression']) }}" class="{{ request('action') === 'suppression' ? 'actif' : '' }}">Suppressions</a>
                <a href="{{ route('journal-audit.index', ['action' => 'telechargement']) }}" class="{{ request('action') === 'telechargement' ? 'actif' : '' }}">Téléchargements</a>
            </div>
        </div>
    </header>

    {{-- [PURGE] Réservé à l'administrateur : un journal que chacun pourrait effacer ne vaudrait
         plus rien comme preuve. Le manager consulte, mais ne supprime pas.
         La purge respecte le filtre d'action actif : on peut par exemple ne retirer que les
         vieilles connexions, sans toucher aux créations ou suppressions. --}}
    @if(auth()->user()->estAdmin())
        <div class="carte" style="margin-bottom:16px">
            <form method="POST" action="{{ route('journal-audit.purger') }}"
                  style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap"
                  onsubmit="return confirm('Supprimer définitivement ces entrées du journal ? Cette action est irréversible.')">
                @csrf @method('DELETE')
                <input type="hidden" name="action" value="{{ request('action') }}">

                <div class="champ" style="margin:0">
                    <label for="jours" style="font-size:.8rem">Faire le ménage dans le journal</label>
                    <select id="jours" name="jours" style="min-width:260px">
                        <option value="365">Entrées de plus d'un an</option>
                        <option value="180">Entrées de plus de 6 mois</option>
                        <option value="90" selected>Entrées de plus de 3 mois</option>
                        <option value="30">Entrées de plus d'un mois</option>
                        <option value="7">Entrées de plus d'une semaine</option>
                        {{-- [SÉCURITÉ] Pas d'option « tout l'historique » : un journal d'audit
                             entièrement effaçable ne vaudrait plus rien comme preuve. La purge
                             porte toujours sur des entrées ANCIENNES, jamais sur les récentes. --}}
                    </select>
                </div>

                <button class="btn danger" type="submit">Supprimer</button>

                <div class="sous-titre" style="flex:1; min-width:220px">
                    @if(request('action'))
                        Ne concerne que les entrées « <b>{{ ucfirst(request('action')) }}</b> » (filtre actif).
                    @else
                        Concerne tous les types d'actions. Utilisez les filtres ci-dessus pour restreindre.
                    @endif
                    La purge est elle-même consignée dans le journal.
                </div>
            </form>
        </div>
    @endif

    <table>
        <thead>
            <tr><th>Date</th><th>Utilisateur</th><th>Rôle</th><th>Action</th><th>Ressource</th><th>Détail</th><th>Adresse IP</th>
                @if(auth()->user()->estAdmin())<th></th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($entrees as $entree)
                <tr>
                    <td class="mono">{{ $entree->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $entree->nom_utilisateur }}</td>
                    <td>
                        <span class="badge {{ $entree->role === 'admin' ? 'accent' : ($entree->role === 'manager' ? 'orange' : 'gris') }}">
                            {{ ucfirst($entree->role) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $couleurs = [
                                'connexion'      => 'vert',
                                'deconnexion'    => 'gris',
                                'creation'       => 'vert',
                                'modification'   => 'orange',
                                'suppression'    => 'rouge',
                                'telechargement' => 'accent',
                            ];
                        @endphp
                        <span class="badge {{ $couleurs[$entree->action] ?? 'gris' }}">{{ ucfirst($entree->action) }}</span>
                    </td>
                    {{-- [LISIBILITÉ] Nom métier de la ressource au lieu du nom technique du modèle
                         (« Sous-bloc d'adresses » et non « PlageIp »), avec son identifiant en second. --}}
                    <td>
                        @if($entree->libelleEntite())
                            <span class="badge gris">{{ $entree->libelleEntite() }}</span>
                            @if($entree->entite_id)
                                <span class="sous-titre mono" style="font-size:.76rem">#{{ $entree->entite_id }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    {{-- Le sujet de l'action ressort visuellement du reste de la phrase --}}
                    <td class="detail-audit">{{ $entree->descriptionFormatee() ?: '—' }}</td>
                    <td class="mono">{{ $entree->adresse_ip ?? '—' }}</td>
                    {{-- Retrait d'une entrée isolée jugée sans intérêt (administrateur uniquement) --}}
                    @if(auth()->user()->estAdmin())
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('journal-audit.destroy', $entree) }}"
                                  onsubmit="return confirm('Supprimer cette entrée du journal ?')">
                                @csrf @method('DELETE')
                                <button class="btn petit danger" type="submit">Supprimer</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->estAdmin() ? 8 : 7 }}">Aucune entrée pour ce filtre.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $entrees->links() }}</div>
@endsection
