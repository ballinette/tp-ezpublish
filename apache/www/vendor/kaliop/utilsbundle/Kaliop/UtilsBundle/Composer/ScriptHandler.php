<?php

namespace Kaliop\UtilsBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as DistributionBundleScriptHandler;
use Composer\Script\CommandEvent;

class ScriptHandler extends DistributionBundleScriptHandler
{
    /**
     * Execute fos:js-routing:dump command from composer
     */
    public static function fosJsRoutingDump(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'dump fos js routing');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'fos:js-routing:dump');
    }
}
