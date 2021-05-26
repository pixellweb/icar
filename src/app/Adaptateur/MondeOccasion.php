<?php

namespace Citadelle\Icar\app\Adaptateur;

use App\Models\Source\Source;
use Citadelle\Icar\app\Couleur as CouleurIcar;
use Citadelle\Icar\app\Stock;
use App\Models\Vehicule\Vehicule;
use App\Models\Vehicule\Couleur;
use Illuminate\Database\Eloquent\Model;


class MondeOccasion extends Adaptateur
{


    public function adapte(Model $model)
    {
        if (get_class($this->objet) == Stock::class) {
            return $this->stock($this->objet, $model);
        }
        if (get_class($this->objet) == CouleurIcar::class) {
            return $this->couleur($this->objet, $model);
        }
    }

    public function stock(Stock $stock, Vehicule $vehicule)
    {

        $vehicule->site_id = $stock->site_id;
        $vehicule->source_id = $stock->source_id;
        $vehicule->modele_id = $stock->modele()->referentiel_id ?? null;
        $vehicule->categorie_id = $stock->categorie()->referentiel_id ?? null;
        $vehicule->transmission_id = $stock->transmission()->referentiel_id ?? null;
        $vehicule->energie_id = $stock->energie()->referentiel_id ?? null;
        $vehicule->couleur_id = $stock->couleur()->id ?? null;
        $vehicule->agence_id = $stock->agence()->id ?? null;
        $vehicule->date_circulation = $stock->date_mec;
        $vehicule->version = $stock->version;
        $vehicule->prix = $stock->prix;
        $vehicule->prix_promo = $stock->prix_promo;
        $vehicule->km = $stock->km;
        $vehicule->cv = $stock->cv;
        $vehicule->is_premiere_main = $stock->premiere_main;
        $vehicule->interieur = $stock->interieur;
        $vehicule->place = $stock->places;
        $vehicule->porte = $stock->portes;
        $vehicule->taux_co2 = $stock->co2;
        $vehicule->options = $stock->options_generiques->map(function ($item, $key) {
            return !empty($item[1]) ? $item[1] : null;
        });
        $vehicule->garanties = $stock->garanties->map(function ($item, $key) {
            return !empty($item[1]) ? $item[1] : null;
        });
        foreach ($vehicule->garanties as $garantie) {
            // '/Garantie (\d+) Mois( ou ([ \d]+) km)*/mi'
            if (preg_match('/Garantie (\d+) Mois.*/mi', $garantie, $matches)) {
                $vehicule->garantie_duree = $matches[1];
            }
            if (preg_match('/Garantie Constructeur.*/mi', $garantie, $matches)) {
                $vehicule->has_garantie_constructeur =  true;
            }
        }
        $vehicule->no_stock = $stock->num_stock;
        $vehicule->vin = $stock->chassis;
        $vehicule->immatriculation = $stock->immat;
        $vehicule->is_particulier = in_array($stock->source_id, Source::PARTICULIER_SOURCE_IDS);
        $vehicule->is_coordonee_cache = $stock->coordonee_cache;
        $vehicule->civilite = $stock->civilite;
        $vehicule->prenom = $stock->prenom;
        $vehicule->nom = $stock->nom;
        $vehicule->email = $stock->email;
        $vehicule->gsm = $stock->gsm;

        $vehicule->save();

        $vehicule->caracteristiques()->syncWithoutDetaching($stock->caracteristiques());

        return $vehicule;
    }

    public function couleur(CouleurIcar $couleur_icar, Couleur $couleur)
    {
        $couleur->source_reference = $couleur_icar->id;
        $couleur->nom = $couleur_icar->nom;
        $couleur->source_id = $couleur_icar->source_id;
        $couleur->couleurs_principale_id = $couleur_icar->couleurPrincipale()->id ?? null;

        $couleur->save();

        return $couleur;
    }

}
