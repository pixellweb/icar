<?php

namespace Citadelle\Icar\app\Adaptateur;

use App\Models\Contact;
use App\Models\Vehicule\Couleur;
use App\Models\Vehicule\Vehicule;
use Citadelle\Icar\app\Couleur as CouleurIcar;
use Citadelle\Icar\app\IcarException;
use Citadelle\Icar\app\Stock;
use Illuminate\Database\Eloquent\Model;


class Oovango extends Adaptateur
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

    public function stock(Stock $stock, \App\Models\Vehicule\Stock $site_stock)
    {
        $modele_id = $stock->modele()->referentiel_id ?? null;
        $transmission_id = $stock->transmission()->referentiel_id ?? null;
        $energie_id = $stock->energie()->referentiel_id ?? null;


        $vehicules = Vehicule::where('site_id', $stock->site_id)
            ->where('modele_id', $modele_id)
            ->where('energie_id', $energie_id)
            ->where('transmission_id', $transmission_id)
            ->where('version', $stock->version)
            ->where('type', $stock->type)
            ->where('is_demonstration', $stock->demo)
            ->where('place', $stock->places)
            ->where('porte', $stock->portes)
            ->get();

        $vehicule_correspondant = null;
        foreach ($vehicules as $vehicule) {

            // TODO $stock->caracteristiques()
            if ($this->array_compare($vehicule->options, $stock->options_const_conc)) {
                $vehicule_correspondant = $vehicule;
                break;
            }
        }

        $vehicule = !$vehicule_correspondant ? new Vehicule() : $vehicule_correspondant;

        $vehicule->site_id = $stock->site_id;
        $vehicule->modele_id = $modele_id;
        $vehicule->transmission_id = $transmission_id;
        $vehicule->energie_id = $energie_id;
        $vehicule->lifestyle_id = $stock->lifestyle()->referentiel_id ?? null;
        $vehicule->categorie_id = $stock->categorie()->referentiel_id ?? null;
        $vehicule->type = $stock->type;
        $vehicule->version = $stock->version;
        $vehicule->prix = $stock->prix;
        $vehicule->prix_promo = $stock->prix_promo;
        $vehicule->cv = $stock->cv;
        $vehicule->is_premiere_main = $stock->premiere_main;
        $vehicule->is_demonstration = $stock->demo;
        $vehicule->interieur = $stock->interieur;
        $vehicule->place = $stock->places;
        $vehicule->porte = $stock->portes;
        $vehicule->taux_co2 = $stock->co2;
        $vehicule->options = $stock->options_const_conc;
        $vehicule->save();

        $site_stock->vehicule_id = $vehicule->id;
        $site_stock->source_id = $stock->source_id;
        $site_stock->couleur_id = $stock->couleur()->id ?? null;
        $vehicule->contact_id = $this->contact($stock->source_id, $stock->marque()->referentiel_id ?? null, $stock->type);
        $site_stock->no_stock = $stock->num_stock;
        $site_stock->vin = $stock->chassis;
        $site_stock->immatriculation = $stock->immat;
        $site_stock->date_circulation = $stock->date_mec;
        $site_stock->date_entree = $stock->date_entree;
        $site_stock->km = $stock->km;

        $site_stock->save();

        if (!$vehicule_correspondant) {
            $vehicule->caracteristiques()->syncWithoutDetaching($stock->caracteristiques());
        }

        return $site_stock;
    }

    public function couleur(CouleurIcar $couleur_icar, Couleur $couleur)
    {
        $couleur->source_reference = $couleur_icar->id;
        $couleur->nom = $couleur_icar->nom;
        $couleur->source_id = $couleur_icar->source_id;
        $couleur->couleurs_principale_id = $couleur_icar->couleurPrincipale()->referentiel_id ?? null;

        $couleur->save();

        return $couleur;
    }

    protected function array_compare($array1, $array2)
    {
        $array1 = array_map('serialize', $array1->toArray());
        $array2 = array_map('serialize',$array2->toArray());
        $diff = array_map('unserialize', array_diff($array1, $array2));

        return !count($diff);
    }

    protected function contact($source_id, $marque_id, $type)
    {
        $contact = Contact::where('source_id', $source_id)->where('marque_id', $marque_id)->where('type', $type)->first();

        if (!$contact) {
            $contact = Contact::where('source_id', $source_id)->whereNull('marque_id')->whereNull('type')->first();

            if (!$contact) {
                throw new IcarException('Impossible de trouver un contact');
            }
        }

        return $contact;
    }

}
