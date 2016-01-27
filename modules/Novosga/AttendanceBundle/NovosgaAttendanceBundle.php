<?php

namespace Novosga\AttendanceBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaAttendanceBundle extends Bundle implements ModuleInterface
{
    public function getIconName()
    {
        return 'pencil-square-o';
    }

    public function getDisplayName()
    {
        return 'Atendimento';
    }

    public function getHomeRoute()
    {
        return 'novosga_attendance_index';
    }
}
