<?php

namespace Citadelle\Icar\app;


use Citadelle\ReferentielApi\app\Correspondance;

class Couleur extends Icar
{

    const FICHIER_CSV = 'COULEURS';


    /**
     * @return Correspondance|null
     */
    public function couleurPrincipale()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->id_couleur_principale)
            ->where('source_id', $this->source_id)
            ->where('referentiel_type', 'couleur')
            ->first();
    }

    /**
     * @param string $value
     */
    protected function setIdAttribute(string $value)
    {
        $this->attributes['id'] = $this->marque . ' - ' . $value;
    }

}
