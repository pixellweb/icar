<?php

namespace Citadelle\Icar\app;


use App\Models\Agence;
use App\Models\Vehicule\Couleur;
use Citadelle\ReferentielApi\app\Correspondance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Stock extends Icar
{

    const FICHIER_CSV = 'STOCK';


    protected $dates = [
        'date_entree',
        'date_mec',
        'derniere_modif',
    ];

    protected $csvs = [
        'options_const_conc',
        'options_generiques',
        'garanties',
        'pack_pneus',
        'equipements'
    ];

    protected $nullables = [
        'prix',
        'prix_promo',
        'immat',
        'hauteur',
        'longueur2',
        'longueur1',
        'prix_bonus',
        'prix_malus',
        'anne_modele',
    ];


    /**
     * @return Correspondance|null
     */
    public function marque()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->marque_id)
            ->where('source_id', $this->api_source_id)
            ->where('referentiel_type', 'marque')
            ->first();
    }


    /**
     * @return Correspondance|null
     */
    public function modele()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->modele_id)
            ->where('source_id', $this->api_source_id)
            ->where('referentiel_type', 'modele')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function categorie()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->carrosserie)
            ->where('source_id', $this->api_source_id)
            ->where('referentiel_type', 'category')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function lifestyle()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->segment_de_marche)
            ->where('source_id', $this->api_source_id)
            ->where('referentiel_type', 'lifestyle')
            ->first();
    }

    /**
     * @return Correspondance|null
     */
    public function transmission()
    {
        return Correspondance::select(['referentiel_id'])
            ->where('source_reference', $this->transmission)
            ->where('source_id', $this->api_source_id)
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
            ->where('source_id', $this->api_source_id)
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
            ->where('source_id', $this->api_source_id)
            ->first();
    }

    /**
     * @return Collection
     */
    public function caracteristiques(): Collection
    {
        $options = collect();
        foreach ($this->options_generiques as $option) {
            $correspondance = Correspondance::select(['referentiel_id'])
                ->where('source_reference', $option[1])
                ->where('source_id', $this->api_source_id)
                ->where('referentiel_type', 'caracteristique')
                ->first();
            if ($correspondance) {
                $options->push($correspondance->referentiel_id);
            }
        }
        foreach ($this->equipements as $option) {
            $correspondance = Correspondance::select(['referentiel_id'])
                ->where('source_reference', $option[1])
                ->where('source_id', $this->api_source_id)
                ->where('referentiel_type', 'caracteristique')
                ->first();
            if ($correspondance) {
                $options->push($correspondance->referentiel_id);
            }
        }
        return $options;
    }

    /**
     * @return Agence|null
     */
    public function agence()
    {
        return Agence::select(['id'])
            ->where('source_reference', $this->localisation)
            ->first();
    }


    /**
     * @return  string $value
     */
    protected function getTypeAttribute()
    {
        return $this->attributes['vnvo'] == 1 ? 'VO' : 'VN';
    }

    /**
     * @return  Collection equipements
     */
    protected function getEquipementsAttribute()
    {
        $equipements = isset($this->attributes['equipements']) ? $this->attributes['equipements'] : collect();
        return $equipements->map(function ($value) {
            return [0 => null, 1 => $value[0]];
        });
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

    protected function setChassisAttribute(string $value)
    {
        if (empty($value)) {
            throw new IcarException('Numéro de chassis manquant');
        }
        $this->attributes['chassis'] = $value;
    }

    protected function setPack_pneusAttribute(Collection $value)
    {
        if (isset($value[0][2])) {
            $this->attributes['monte'] = $value[0][2];
        }
    }


}
