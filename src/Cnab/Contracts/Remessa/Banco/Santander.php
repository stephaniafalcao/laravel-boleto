<?php
namespace Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa\Banco;

use Eduardokum\LaravelBoleto\Cnab\Contracts\Remessa;

interface Santander extends Remessa
{
    public function gerar();
}