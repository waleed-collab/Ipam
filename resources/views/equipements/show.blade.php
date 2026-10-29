{{-- Vue : fiche détaillée d'un équipement.
     [BACKBONE] Pour l'équipement backbone (cadre "Local" du schéma), la fiche expose ses interfaces :
      - côté international : nom, @IP, bande passante, neighbor (opérateur), AS local ;
      - côté accès         : nom, @IP, bande passante, équipement d'accès, technologie derrière. --}}
@extends('layouts.app')
@section('titre', 'Détail équipement')
@section('contenu')
    <header class="page">
        <div>
            <h1>{{ $equipement->nom }}</h1>
            <div class="sous-titre">
                {{ $equipement->type }} ·
                <span class="badge {{ $equipement->estBackbone() ? 'accent' : 'gris' }}">
                    {{ $equipement->estBackbone() ? 'Backbone (équipement local central)' : "Équipement d'accès" }}
                </span>
                @if($equipement->adresse_ip) · Management : <span class="mono">{{ $equipement->adresse_ip }}</span> @endif
                @if($equipement->technologie) · {{ $equipement->technologie->nom }} @endif
                {{-- [PÉRIMÈTRE] Le lien équipement→client n'est plus affiché dans l'interface
                     équipements (colonne et champ retirés) : on ne le réintroduit pas ici. --}}
            </div>
        </div>
    </header>

    @if($equipement->estBackbone())
        {{-- ================= CÔTÉ INTERNATIONAL : relations externes (sessions BGP) ================= --}}
        <div class="carte" style="margin-bottom:18px">
            <h2 style="font-size:1rem; margin-bottom:12px">Côté international — relations externes</h2>
            <table>
                <thead><tr><th>Interface</th><th>Adresse IP</th><th>Bande passante</th><th>Neighbor</th><th>AS local</th><th></th></tr></thead>
                <tbody>
                    @forelse($equipement->interfacesInternationales as $interface)
                        <tr>
                            <td class="mono">{{ $interface->nom }}</td>
                            <td class="mono">{{ $interface->adresse_ip ?? '—' }}</td>
                            <td>{{ $interface->bande_passante ? number_format($interface->bande_passante, 0, ',', ' ') . ' Mbps' : '—' }}</td>
                            <td>{{ $interface->operateur->nom ?? '—' }}
                                @if($interface->operateur) <span class="mono">(AS{{ $interface->operateur->asn }})</span> @endif
                            </td>
                            {{-- L'AS local vient des paramètres de l'AS (valeur unique du réseau) --}}
                            <td class="mono">AS{{ $configurationAs->asn_local }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('equipements.interfaces.destroy', [$equipement, $interface]) }}"
                                      onsubmit="return confirm('Supprimer cette interface ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn petit danger" type="submit">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucune interface internationale déclarée.</td></tr>
                    @endforelse
                </tbody>
                {{-- [ALIGNEMENT] Ligne d'ajout intégrée au tableau : chaque champ est DANS la cellule
                     de sa colonne, donc toujours parfaitement aligné avec l'en-tête correspondant.
                     Les champs sont rattachés au formulaire déclaré plus bas via l'attribut form="...". --}}
                <tfoot>
                    <tr>
                        <td><input class="mono champ-tableau" form="form-int" name="nom" placeholder="Gi0/0/1" required></td>
                        <td><input class="mono champ-tableau" form="form-int" name="adresse_ip" placeholder="Adresse IP"></td>
                        <td><input class="champ-tableau" form="form-int" name="bande_passante" type="number" min="1" placeholder="Mbps"></td>
                        <td>
                            <select class="champ-tableau" form="form-int" name="operateur_id" required>
                                <option value="">— Neighbor —</option>
                                @foreach($operateurs as $operateur)
                                    <option value="{{ $operateur->id }}">{{ $operateur->nom }} (AS{{ $operateur->asn }})</option>
                                @endforeach
                            </select>
                        </td>
                        {{-- AS local : valeur unique du réseau, affichée automatiquement (non saisie) --}}
                        <td class="mono">AS{{ $configurationAs->asn_local }}</td>
                        <td style="text-align:right"><button class="btn petit" form="form-int" type="submit">Ajouter</button></td>
                    </tr>
                </tfoot>
            </table>

            {{-- Formulaire d'ajout CÔTÉ INTERNATIONAL (les champs visibles sont dans le tfoot ci-dessus) --}}
            <form id="form-int" method="POST" action="{{ route('equipements.interfaces.store', $equipement) }}">
                @csrf
                <input type="hidden" name="type" value="internationale">    {{-- Côté fixé par le volet --}}
            </form>
        </div>

        {{-- ================= CÔTÉ ACCÈS : réseau d'accès et technologies derrière ================= --}}
        <div class="carte" style="margin-bottom:18px">
            <h2 style="font-size:1rem; margin-bottom:12px">Côté accès — réseau d'accès</h2>
            <table>
                {{-- Côté accès : l'« Adresse IP » affichée est celle de l'ÉQUIPEMENT d'accès de la ligne
                     (même source que la liste des équipements), pas une IP propre à l'interface. Elles
                     restent donc toujours identiques entre cette fiche et la liste. --}}
                <thead><tr><th>Interface</th><th>Adresse IP</th><th>Bande passante</th><th>Équipement d'accès</th><th>Technologie</th><th></th></tr></thead>
                <tbody>
                    @forelse($equipement->interfacesAcces as $interface)
                        <tr>
                            <td class="mono">{{ $interface->nom }}</td>
                            {{-- IP = celle de l'équipement d'accès (identique à la liste des équipements) --}}
                            <td class="mono">{{ $interface->equipementDistant->adresse_ip ?? '—' }}</td>
                            <td>{{ $interface->bande_passante ? number_format($interface->bande_passante, 0, ',', ' ') . ' Mbps' : '—' }}</td>
                            <td>{{ $interface->equipementDistant->nom ?? '—' }}</td>
                            <td>{{ $interface->technologie->nom ?? '—' }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('equipements.interfaces.destroy', [$equipement, $interface]) }}"
                                      onsubmit="return confirm('Supprimer cette interface ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn petit danger" type="submit">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Aucune interface d'accès déclarée.</td></tr>
                    @endforelse
                </tbody>
                {{-- [ALIGNEMENT] Même principe que côté international : chaque champ dans sa colonne. --}}
                <tfoot>
                    <tr>
                        <td><input class="mono champ-tableau" form="form-acces" name="nom" placeholder="Gi0/1/0" required></td>
                        {{-- Pas de saisie : l'IP affichée sera automatiquement celle de l'équipement choisi. --}}
                        <td class="mono" style="color:var(--encre-2)">auto</td>
                        <td><input class="champ-tableau" form="form-acces" name="bande_passante" type="number" min="1" placeholder="Mbps"></td>
                        <td>
                            <select class="champ-tableau" form="form-acces" name="equipement_distant_id" required>
                                <option value="">— Équipement —</option>
                                @foreach($equipementsAcces as $distant)
                                    <option value="{{ $distant->id }}">{{ $distant->nom }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="champ-tableau" form="form-acces" name="technologie_id" required>
                                <option value="">— Technologie —</option>
                                @foreach($technologies as $technologie)
                                    <option value="{{ $technologie->id }}">{{ $technologie->nom }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="text-align:right"><button class="btn petit" form="form-acces" type="submit">Ajouter</button></td>
                    </tr>
                </tfoot>
            </table>

            {{-- Formulaire d'ajout CÔTÉ ACCÈS (les champs visibles sont dans le tfoot ci-dessus) --}}
            <form id="form-acces" method="POST" action="{{ route('equipements.interfaces.store', $equipement) }}">
                @csrf
                <input type="hidden" name="type" value="acces">             {{-- Côté fixé par le volet --}}
            </form>
        </div>

        {{-- Erreurs de validation des formulaires d'interface --}}
        @if($errors->any())
            <div class="erreur" style="margin-bottom:18px">{{ $errors->first() }}</div>
        @endif
    @else
        {{-- Équipement d'accès : simple rappel, les interfaces se gèrent sur le backbone --}}
        <div class="carte">
            <div class="sous-titre">
                Cet équipement est un équipement d'accès : ses raccordements sont visibles sur la fiche
                de l'équipement backbone (interfaces côté accès).
            </div>
        </div>
    @endif

    {{-- [DESIGN] Bouton retour en bas de page, aligné à droite (pied d'action standard). --}}
    <div class="barre-actions">
        <a class="btn secondaire" href="{{ route('equipements.index') }}">Retour à la liste</a>
    </div>
@endsection
