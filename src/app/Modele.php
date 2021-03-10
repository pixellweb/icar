<?php

namespace Citadelle\Icar\app;


class Modele extends Icar
{

    const FICHIER_CSV = 'MODELES';


    /**
     * @return string
     */
    protected function getIdAttribute()
    {
        return $this->attributes['id'] = $this->marque_id . ' - ' . $this->modele_id;
    }

    /**
     * @return string
     */
    protected function getNomAttribute()
    {
        return $this->attributes['nom'] = $this->marque_id . ' - ' . $this->libelle_modele;
    }
}
