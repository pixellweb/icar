<?php

namespace Citadelle\Icar\app\Console\Commands;

use App\Models\Source\Source;
use Carbon\Carbon;
use Citadelle\ReferentielApi\app\ReferentielApiException;
use Citadelle\Icar\app\Fichier;
use Citadelle\Icar\app\Couleur as CouleurIcar;
use Citadelle\Icar\app\IcarException;
use Citadelle\Icar\app\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Helper\ProgressBar;

// Alias
use Citadelle\ReferentielApi\app\SourceModelAlias;
use Citadelle\ReferentielApi\app\CouleurModelAlias;
use Citadelle\ReferentielApi\app\StockModelAlias;
use Citadelle\ReferentielApi\app\VehiculeModelAlias;

class Icar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:icar
                                {--O|option=all}
                                {--no-cache : Suppression du système de cache.}
                                {--date= : Récupère uniquement les véhicules modifiés à partir de cette date. Format d/m/Y.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import de Icar
    {--option(O) option = all (import référentiels + correspondances) default}
    {--option(O) option = stock (importe les stock)}
    {--option(O) option = couleur (importe les couleurs)}
    {--option(O) option = referentiel (ajoute des nouvelles correspondance au déférentiel)}  ';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% -- %message%');


        foreach (config('citadelle.icar.models-alias') as $alias => $model) {
            if ($model) {
                class_alias($model, $alias);
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $option = $this->option('option');
        $option = !empty($option) ? $option : 'all';

        $cache_date = $this->option('date') ? Carbon::createFromFormat('d/m/Y', $this->option('date')) : Cache::get('icar_stock_cache_date');

        if ($this->option('no-cache') and !$this->option('date')) {
            $cache_date = null;
        }

        $sources = SourceModelAlias::all();

        if (in_array($option, ['all', 'stock'])) {
            $stocks = StockModelAlias::select(['id', 'vin'])->get();
            $vehicules = VehiculeModelAlias::get();
        }
        if (in_array($option, ['all', 'couleur'])) {
            $couleurs = CouleurModelAlias::select(['id', 'source_reference', 'source_id'])->get();
        }

        foreach ($sources as $source) {
            $this->info(PHP_EOL."##### Import de la source ".$source->nom.' #####');

            switch ($option) {
                case 'test' :
                    $this->test();
                    break;
                case 'referentiel' :
                    $this->referentiel($source);
                    break;
                case 'stock' :
                    $this->stock($source, $stocks, $vehicules, $cache_date);
                    break;
                case 'couleur' :
                    $this->couleur($source, $couleurs);
                    break;

                case 'all' :
                default :
                    $this->couleur($source, $couleurs);
                    $this->stock($source, $stocks, $vehicules, $cache_date);
                    $this->referentiel($source);
                break;
            }

        }

        $this->info(PHP_EOL."Fin de l'import");
    }

    protected function test()
    {
        $stocks = Stock::import(Source::find(1));
        dd($stocks->first());

        $collection = new Fichier(SourceModelAlias::first(), CouleurIcar::class);
        $couleurs = $collection->get();
        dd($couleurs->get(1));
    }

    protected function stock($source, $stocks, $vehicules, $cache_date)
    {
        $this->info(PHP_EOL.'**** Import du stock ****');
        $progress_bar = $this->startProgressBar(0);

        try {

            $stocks_icar = Stock::import($source);

            $progress_bar->setMaxSteps($stocks->count());

            foreach ($stocks_icar as $stock) {
                $progress_bar->setMessage($stock->chassis);
                $progress_bar->advance();

                try {

                    if ($cache_date and $stock->derniere_modif < $cache_date) {
                        continue;
                    }

                    $vehicule = $stocks->first(function($item) use ($stock) {
                        return $item->vin == $stock->chassis;
                    });

                    $stock->saveBdd($vehicule ? $vehicule : new StockModelAlias());

                } catch (IcarException $exception) {
                    $this->error(PHP_EOL.'Erreur enregistrement stock : '.$exception->getMessage());
                }
            }

            // Suppression stock
            StockModelAlias::where('source_id', $source->id)->whereNotIn('vin', $stocks_icar->pluck('chassis'))->delete();

            if (!$this->option('no-cache')) {
                Cache::put('icar_stock_cache_date', Carbon::now());
            }

        } catch (IcarException $exception) {
            $this->error(PHP_EOL.'Erreur import stock : '.$exception->getMessage());
        }

        $this->finishProgressBar($progress_bar);
    }

    protected function couleur($source, $couleurs)
    {
        $this->info(PHP_EOL.'**** Import des couleurs ****');
        $progress_bar = $this->startProgressBar($couleurs->count());

        try {

            $couleurs_icar = CouleurIcar::import($source);

            foreach ($couleurs_icar as $couleur_icar) {
                $progress_bar->setMessage($couleur_icar->nom);
                $progress_bar->advance();

                $couleur = $couleurs->first(function($item) use ($couleur_icar) {
                    return $item->source_reference == $couleur_icar->id and $item->source_id == $couleur_icar->source_id;
                });

                $couleur_icar->saveBdd($couleur ?? new CouleurModelAlias());
            }

        } catch (IcarException $exception) {
            $this->error(PHP_EOL.'Erreur import couleur : '.$exception->getMessage());
        }

        $this->finishProgressBar($progress_bar);
    }

    protected function referentiel($source)
    {
        $this->info(PHP_EOL.'**** Import du référentiel ****');
        $progress_bar = $this->startProgressBar();

        foreach (Fichier::REFERENTIEL_TYPES as $type => $class) {

            try {

                $referentiels = $class::import($source);

                // TODO prendre dans l'api ou en bdd ?
                $correspondances = $source->correspondances()->where('referentiel_type', $type)->get();

                foreach ($referentiels as $icar) {

                    if (!$correspondances->contains(function ($value, $key) use ($icar) {
                        return $value->source_reference == $icar->id;
                    })) {
                        $progress_bar->setMessage('Ajout référence '.$type.' : '.$icar->nom);
                        $progress_bar->advance();

                        try {

                            $icar->addReferentiel();

                        } catch (ReferentielApiException $e) {
                            $this->error($e->getMessage());
                        }
                    }
                }


            } catch (IcarException $exception) {
                $this->error(PHP_EOL.'Erreur import référentiel : '.$exception->getMessage());
            }

        }

        $this->finishProgressBar($progress_bar);

    }


    protected function startProgressBar($max_steps = 0)
    {
        $progress_bar = $this->output->createProgressBar($max_steps);
        $progress_bar->setFormat('custom');
        $progress_bar->setMessage('Start');
        $progress_bar->start();

        return $progress_bar;
    }

    protected function finishProgressBar($progress_bar)
    {
        $progress_bar->setMessage('Finish');
        $progress_bar->finish();
    }

}
