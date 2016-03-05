<?php

namespace Novosga\AttendanceBundle;

use Novosga\Module\BaseModule;

class NovosgaAttendanceBundle extends BaseModule
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
