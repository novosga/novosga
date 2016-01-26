<?php

namespace Novosga\AdminBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaAttendanceBundle extends Bundle implements ModuleInterface
{
    public function getDisplayName() 
    {
        return 'Atendimento';
    }

    public function getHomeRoute() 
    {
        return 'novosga_attendance_index';
    }
}
