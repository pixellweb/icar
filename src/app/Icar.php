<?php


namespace Citadelle\Icar\app;


use Citadelle\Icar\app\Adaptateur\Adaptateur;
use App\Models\Source\Source;
use Carbon\Carbon;
use Citadelle\ReferentielApi\app\Ressources\Correspondance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Icar
{
    /**
     * @var Adaptateur
     */
    protected $adaptateur;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $dates = [];
    /**
     * @var array
     */
    protected $csvs = [];
    /**
     * @var array
     */
    protected $nullables = [];


    /**
     * @param Source $source
     * @return Collection
     * @throws IcarException
     */
    public static function import(Source $source): Collection
    {
        $objet = new Fichier($source, static::class);
        return $objet->get();
    }

    /**
     * Icar constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $adaptateur = config('citadelle.icar.adaptateur');
        $this->adaptateur = new $adaptateur($this);

        $this->fill($attributes);
    }


    /**
     * @param array $attributes
     */
    public function fill(array $attributes)
    {
        // On affecte les $attributs en masse avant les setters
        // Cela permet de ne pas être tributaire de l'ordre des colonnes dans le csv
        $this->attributes = array_merge($this->attributes, $attributes);
        foreach ($attributes as $property => $value) {
            $this->__set($property, $value);
        }
    }


    /**
     * @param Model $model
     */
    public function saveBdd(Model $model)
    {
        $this->getAdaptateur()->adapte($model);
        $model->save();
    }


    /**
     *
     */
    public function addReferentiel()
    {
        $datas = [
            'source_reference' => $this->id,
            'intitule' => $this->nom,
        ];

        $correspondance = new Correspondance();
        $correspondance->post($this->source_id, $this->getReferentielType(), $datas);
    }


    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . lcfirst($name) . 'Attribute')) {
            $this->{'set' . lcfirst($name) . 'Attribute'}($value);
        } else {

            if (in_array($name, $this->dates)) {
                $value = $this->createDate($value);
            }

            if (in_array($name, $this->csvs)) {
                $value = $this->readCsv($value);
            }

            if (in_array($name, $this->nullables)) {
                $value = $this->setNullable($value);
            }

            $this->attributes[$name] = $value;
        }

    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {

        if (method_exists($this, 'get' . lcfirst($name) . 'Attribute')) {
            return $this->{'get' . lcfirst($name) . 'Attribute'}();
        } elseif (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * @param $value
     * @return Carbon|false|null
     */
    protected function createDate($value)
    {
        $date = null;
        try {
            $date = Carbon::createFromFormat('Y-m-d H:i:s.v', $value);
        } catch (\Exception $exception) {
            $date = null;
        }
        return $date;
    }

    /**
     * @param $value
     * @return array
     */
    protected function readCsv($value): array
    {
        $array = str_getcsv($value, '|');
        return array_map('str_getcsv', $array, array_fill(0, count($array), '-'));
    }

    /**
     * @param $value
     * @return mixed|null
     */
    protected function setNullable($value)
    {
        return (empty($value) or in_array($value, ['.00'])) ? null : $value;
    }

    /**
     * @return false|int|string|null
     */
    public function getReferentielType()
    {
        return in_array(static::class, Fichier::REFERENTIEL_TYPES) ? array_search(static::class, Fichier::REFERENTIEL_TYPES) : null;
    }

    /**
     * @return Adaptateur|null
     */
    public function getAdaptateur()
    {
        return $this->adaptateur;
    }
}

