<?php


namespace Citadelle\Icar\app;


use Str;

class ReaderCsv
{

    protected $separator = ";";
    protected $enclosure = '';

    protected $path;

    protected $header;

    protected $datas = [];


    /**
     * @param string $path
     * @return static
     * @throws IcarException
     */
    static function createFromPath(string $path): self
    {
        $reader = new self($path);
        $reader->read();
        return $reader;
    }


    /**
     * ReaderCsv constructor.
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        $this->setPath($path);
    }


    /**
     * @param string|null $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }


    /**
     * @throws IcarException
     */
    public function read()
    {
        if (!file_exists($this->path)) {
            throw new IcarException('Impossible de lire le fichier ' . $this->path);
        }

        $strgetcsv = function ($str) {

            $array = str_getcsv(utf8_encode($str), $this->separator, $this->enclosure);

            return array_map('trim', $array);
        };

        // Récupération des données sous forme de tableau
        $csv = array_map($strgetcsv, file($this->path));


        $this->header = isset($csv[0]) ? $csv[0] : null;
        // remove column header
        array_shift($csv);


        // Utilisation de l'entete pour les clés
        foreach ($csv as $data) {
            $this->datas[] = array_combine($this->getHeaderSlug(), $data);
        }
    }


    /**
     * @return Fichier
     */
    public function getRows(): array
    {
        return $this->datas;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }


    /**
     * @return array
     */
    public function getHeaderSlug(): array
    {
        $sluglify = function ($str) {
            return Str::slug($str, '_');
        };

        return array_map($sluglify, $this->header);
    }
}
