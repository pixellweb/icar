<?php

namespace Citadelle\Icar\app\Adaptateur;

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

        $vehicule->source_id = $stock->source_id;
        $vehicule->modele_id = $stock->modele()->referentiel_id ?? null;
        $vehicule->categorie_id = $stock->categorie()->referentiel_id ?? null;
        $vehicule->transmission_id = $stock->transmission()->referentiel_id ?? null;
        $vehicule->energie_id = $stock->energie()->referentiel_id ?? null;
        $vehicule->couleur_id = $stock->couleur()->id ?? null;
        $vehicule->date_circulation = $stock->date_mec;
        $vehicule->prix = $stock->prix;
        $vehicule->prix_promo = $stock->prix_promo;
        $vehicule->is_professionnel = 0; //TODO;
        $vehicule->km = $stock->km;
        $vehicule->cv = $stock->cv;
        $vehicule->premiere_main = 0; //TODO;
        $vehicule->interieur = 0; //TODO;
        $vehicule->place = $stock->places;
        $vehicule->porte = $stock->portes;
        $vehicule->taux_co2 = $stock->co2;
        $vehicule->taxe_co2 = 0; //TODO;
        $vehicule->no_stock = $stock->num_stock;
        $vehicule->vin = $stock->chassis;
        $vehicule->immatriculation = $stock->immat;
        $vehicule->localisation = 0; //TODO;
        $vehicule->coordonee_cache = 0; //TODO;
        $vehicule->civilite = 0; //TODO;
        $vehicule->prenom = 0; //TODO;
        $vehicule->nom = 0; //TODO;
        $vehicule->email = 0; //TODO;
        $vehicule->gsm = 0; //TODO;


        return $vehicule;
    }

    public function couleur(CouleurIcar $couleur_icar, Couleur $couleur)
    {
        $couleur->source_reference = $couleur_icar->id;
        $couleur->nom = $couleur_icar->nom;
        $couleur->source_id = $couleur_icar->source_id;
        $couleur->couleurs_principale_id = $couleur_icar->couleurPrincipale()->id ?? null;

        return $couleur;
    }

}
