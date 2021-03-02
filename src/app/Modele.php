<?php

namespace Citadelle\Icar\app;


class Modele extends Icar
{

    const FICHIER_CSV = 'MODELES';


    /**
     * @param string $value
     */
    protected function setMarque_idAttribute(string $value)
    {
        $this->attributes['marque_id'] = $value;

        $this->attributes['id'] = $value . ' - ' . $this->id;
    }

    /**
     * @param string $value
     */
    protected function setLibelle_modeleAttribute(string $value)
    {
        $this->attributes['nom'] = $this->marque_id . ' - ' . $value;
    }
}
