<?php


namespace Citadelle\Icar\app;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as CollectionLaravel;
use phpDocumentor\Reflection\Types\Integer;

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
     * @var int
     */
    protected $api_source_id;

    /**
     * @var string
     */
    protected $repertoire_ftp;

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
        'lifestyle' => Segment::class,
        'couleur' => CouleurPrincipale::class,
        'marque' => Marque::class,
        'modele' => Modele::class,
    ];


    /**
     * Fichier constructor.
     * @param Int $api_source_id
     * @param string $repertoire_ftp
     * @param $class
     */
    public function __construct(int $api_source_id, string $repertoire_ftp, string $class)
    {
        $this->reader_csv = new ReaderCsv();
        $this->base_path = base_path(config('citadelle.icar.path'));
        $this->projet = config('citadelle.icar.projet');

        $this->api_source_id = $api_source_id;
        $this->repertoire_ftp = $repertoire_ftp;

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

            $row['api_source_id'] = $this->api_source_id;

            $collection->push(new $this->class($row));
        }

        return $collection;
    }

    /**
     * @return string
     */
    protected function getFichier()
    {
        return $this->base_path . '/' . $this->repertoire_ftp . '/' . $this->projet . '_' . $this->class::FICHIER_CSV . '_' . $this->repertoire_ftp . '.csv';
    }

}

