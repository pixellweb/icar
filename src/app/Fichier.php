<?php


namespace Citadelle\Icar\app;


use App\Models\Source\Source;
use Illuminate\Support\Collection as CollectionLaravel;

class Fichier
{
    /**
     * @var ReaderCsv
     */
    protected $reader_csv;
    /**
     * @var string
     */
    protected $base_path;
    /**
     * @var string
     */
    protected $projet;
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string
     */
    protected $class;


    const WORDS_TO_REMOVE = [
        'NC',
        '#N/A',
        'Non codifie',
        'Non codifié',
        'Non Codifié',
        'Non codifiée',
        'NCO',
        'NON',
    ];

    const REFERENTIEL_TYPES = [
        'energy' => Carburant::class,
        'transmission' => Transmission::class,
        'category' => Categorie::class,
        'couleur' => CouleurPrincipale::class,
        'marque' => Marque::class,
        'modele' => Modele::class,
        //'caracteristiques' => Caracteristique:class, // TODO
    ];


    /**
     * Fichier constructor.
     * @param Source $source
     * @param $class
     */
    public function __construct(Source $source, string $class)
    {
        $this->reader_csv = new ReaderCsv();
        $this->base_path = base_path(config('citadelle.icar.path'));
        $this->projet = config('citadelle.icar.projet');

        $this->source = $source;

        $this->class = $class;
    }


    /**
     * @return CollectionLaravel
     * @throws IcarException
     */
    public function get(): CollectionLaravel
    {
        $this->reader_csv->setPath($this->getFichier());
        $this->reader_csv->read();
        $rows = $this->reader_csv->getRows();

        $collection = collect();
        foreach ($rows as $row) {

            // Suppression des NC
            if (isset($row['id']) and (in_array($row['id'], self::WORDS_TO_REMOVE) or empty($row['id']))) {
                continue;
            }

            $row['source_id'] = $this->source->id;
            $row['site_id'] = $this->source->site_id;

            $collection->push(new $this->class($row));
        }

        return $collection;
    }

    /**
     * @return string
     */
    protected function getFichier()
    {
        return $this->base_path . '/' . $this->source->repertoire_ftp . '/' . $this->projet . '_' . $this->class::FICHIER_CSV . '_' . $this->source->repertoire_ftp . '.csv';
    }

}

