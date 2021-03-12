<?php

namespace Citadelle\Icar\app;


use App\Models\Vehicule\Couleur;
use Citadelle\ReferentielApi\app\Correspondance;

class Stock extends Icar
{

    const FICHIER_CSV = 'STOCK_VO';


    protected $dates = [
        'date_entree',
        'date_mec',
        'derniere_modif',
    ];

    protected $csvs = [
        'options_const_conc',
        'options_generiques',
        'garanties'
    ];

    protected $nullables = [
        'prix',
        'prix_promo',
        'immat',
    ];


    /**
     * @return Correspondance|null
     */
    public function modele()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->modele_id)
            ->where('source_id', $this->source_id)
            ->where('referentiel_type', 'modele')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function categorie()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->categoriecg)
            ->where('source_id', $this->source_id)
            ->where('referentiel_type', 'category')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function transmission()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->tranqmission)
            ->where('source_id', $this->source_id)
            ->where('referentiel_type', 'transmission')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function energie()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->energie)
            ->where('source_id', $this->source_id)
            ->where('referentiel_type', 'energy')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function couleur()
    {
        return Couleur::select(['id'])
            ->where('source_reference', $this->couleur_id)
            ->where('source_id', $this->source_id)
            ->first();
    }


    /**
     * @param string $value
     */
    protected function setModele_idAttribute(string $value)
    {
        $this->attributes['modele_id'] = $this->marque_id . ' - ' . $value;
    }
    /**
     * @param string|null $value
     */
    protected function setCouleur_idAttribute($value)
    {
        $this->attributes['couleur_id'] = $value ? $this->marque_id . ' - ' . $value : null;
    }


}
