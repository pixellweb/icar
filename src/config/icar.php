<?php

return [

    'path' => env('ICAR_PATH', 'import'),
    'projet'  => env('ICAR_PROJET', 'OOVANGO'),

    'adaptateur' => \Citadelle\Icar\app\Adaptateur\MondeOccasion::class,

    'models-alias' => [
        'Citadelle\ReferentielApi\app\SourceModelAlias' => App\Models\Source\Source::class,
        'Citadelle\ReferentielApi\app\CouleurModelAlias' => App\Models\Vehicule\Couleur::class,
        'Citadelle\ReferentielApi\app\StockModelAlias' => App\Models\Vehicule\Vehicule::class,
        'Citadelle\ReferentielApi\app\VehiculeModelAlias' => null
    ]

];
