<?php
declare(strict_types=1);

spl_autoload_register(function($sClassName)
{
    $sClassFile = __DIR__.'./../';
/*    var_dump($sClassFile);
    var_dump($sClassName);
    var_dump($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php');
    die;*/
    if ( file_exists($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php') )
    {
        require_once($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php');
    }
    $arClass = explode('\\', strtolower($sClassName));
    foreach($arClass as $sPath )
    {
        $sClassFile .= '/'.ucfirst($sPath);
    }
    $sClassFile .= '.php';

    if (file_exists($sClassFile))
    {
        require_once($sClassFile);
    }
});