<?php
namespace novosga\composer;

use \Composer\Script\Event;

/**
 * EventHandler
 *
 * @author rogeriolino
 */
class EventHandler {
    
    private static $prevVersion;
    
    public static function preUpdate(Event $e) {
        $composer = $e->getComposer();
        self::$prevVersion = $composer->getPackage()->getVersion();
    }
    
    public static function postUpdate(Event $e) {
        $io = $e->getIO();
        $composer = $e->getComposer();
        $io->write('Checking database changes...');
        $io->write('Updating from ' . self::$prevVersion . ' to ' . $composer->getPackage()->getVersion());
        $io->write('Novo SGA updated');
    }
    
}
