{{-- ====================================================================
     Vue de PAGINATION propre à l'application.

     [FIX] La vue livrée par défaut avec Laravel est écrite pour Tailwind CSS :
     ses icônes SVG n'ont de taille que via des classes Tailwind (w-5 h-5).
     Cette application n'utilise pas Tailwind, les flèches s'affichaient donc
     à leur taille naturelle — c'est-à-dire en gigantesques chevrons occupant
     tout l'écran. Cette vue les remplace par des libellés texte, stylés avec
     le CSS maison (classe .pagination).
     ==================================================================== --}}
@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">

        {{-- ---------- Page précédente ---------- --}}
        @if ($paginator->onFirstPage())
            <span class="page desactive" aria-disabled="true">‹ Précédent</span>
        @else
            <a class="page" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Précédent</a>
        @endif

        {{-- ---------- Numéros de page ---------- --}}
        @foreach ($elements as $element)
            {{-- Séparateur « … » pour les plages de pages masquées --}}
            @if (is_string($element))
                <span class="page ellipse" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page actif" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- ---------- Page suivante ---------- --}}
        @if ($paginator->hasMorePages())
            <a class="page" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant ›</a>
        @else
            <span class="page desactive" aria-disabled="true">Suivant ›</span>
        @endif

        {{-- ---------- Repère de position ---------- --}}
        <span class="pagination-total">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </span>
    </nav>
@endif
