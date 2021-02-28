<?php

namespace Citadelle\Icar\app\Adaptateur;


use Citadelle\Icar\app\Icar;
use Illuminate\Database\Eloquent\Model;

abstract class Adaptateur
{

    /**
     * @var Icar
     */
    protected $objet;

    /**
     * Adaptateur constructor.
     * @param Icar $objet
     */
    public function __construct(Icar $objet)
    {
        $this->objet = $objet;
    }

    /**
     * @param Model $model
     */
    abstract public function adapte(Model $model);

}
