<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */


namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Class Kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
