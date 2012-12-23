<?
declare(encoding='UTF-8');

\set_error_handler(static function($errno, $errstr, $errfile, $errline){
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

\spl_autoload_extensions('.php');
\spl_autoload_register();

use \Yasca\Core\Iterators;
use \Yasca\Core\Operators;

//Wrap the following code to not leave variables in local scope
\call_user_func(static function(){

/**
 * Yasca Engine, Yasca Static Analysis Tool
 *
 * This package implements a simple engine for static analysis
 * of source code files.
 * @author Michael V. Scovetta <scovetta@sourceforge.net>
 * @author Cory Carson <cory.carson@boeing.com>
 * @license see doc/LICENSE
 * @package Yasca
 */

$scannerOptions = [];

$addDefaultLog = true;
$logs = new \SplQueue();

$addDefaultReport = true;
$reports = new \SplQueue();

$version =
    'Yasca version ' . \Yasca\Scanner::VERSION . "\n" +
    'Copyright (c) 2012 Michael V. Scovetta. See docs/LICENSE for license information.';

$help = <<<"EOT"
$version

Usage: [options] directory
Perform analysis of program source code.
  -h, --help                     Show this help
  -v, --version                  Show only the version info

  --extensionsOnly(,EXT)+        Only scan these file extensions
  --extensionsIgnore(,EXT)+      Ignore these file extensions

  --pluginsIgnore(,NAMEPART)+    Ignore plugins containing these
  --pluginsOnly(,NAMEPART)+      Only use plugins containing these
  --installedPlugins             Do not perform scan. Print names of installed plugins

  -l,TYPE(,OPTIONS)*
  --log,TYPE(,OPTIONS)*
          Uses the TYPE of log plugin, and provides OPTIONS to that type.
          Multiple log switches can be used.
          If no switch specified, behaves as --log:ConsoleLog,STDOUT
  --logOptions                   Do not perform scan. Print help for all installed log types
  --logOptions,TYPE              Do not perform scan. Print help for log TYPE
  --installedLogs                Do not perform scan. Print names of installed logs
  --logSilent, --silent          Do not add default console log.

  -r,TYPE(,OPTIONS)*
  --report,TYPE(,OPTIONS)*
          Use the TYPE of report, and provides OPTIONS to that type
          Multiple report switches can be used.
          If no switch specified, behaves as --report,HtmlFileReport
  --installedReports             Do not perform scan. Print names of installed reports
  --reportOptions                Do not perform scan. Print help for all installed report types
  --reportOptions,TYPE           Do not perform scan. Print help for report TYPE

  --batch(,DIR)+                 Create a report for each folder in DIR, for each DIR
  --debug                        Throw exceptions instead of logging them

Examples:
  yasca.bat c:\\source_code
  yasca.sh /opt/dev/source_code
  php.exe Main.php --pluginsIgnore,FindBugs,PMD,Antic,JLint /opt/dev/source_code
  php.exe Main.php --log,ConsoleLog,7 "c:/orange/"
  php.exe Main.php --onlyPlugins,BuiltIn c:/example/


EOT;

if ($_SERVER['argc'] < 2){
    print($help);
    exit(0);
}

foreach (
    (new \Yasca\Core\IteratorBuilder)
    ->from($_SERVER['argv'])
    //Skip the name of the script file
    ->skip(1)
    ->selectKeys(static function($arg){
        $options = \str_getcsv($arg);
        $switch = \array_shift($options);
        return [
            Operators::nullCoalesce($options,[]),
            $switch,
        ];
    })

    as $switch => $options
){
    //As of PHP 5.4, switch() uses loose comparision instead of strict.
    //Use if/elseif instead.
    if          ($switch === '-h'         ||
              $switch === '--help'  ||
              $switch === '/?'
      ){
        print($help);
        exit(0);

    } elseif ($switch === '-v' ||
              $switch === '--version'
      ){
        print($version);
        exit(0);

    } elseif ($switch === '--pluginInstalled' ||
              $switch === '--pluginsInstalled'||
              $switch === '--installedPlugins'||
              $switch === '--installedPlugin'
    ){
        (new \Yasca\Core\IteratorBuilder)
        ->from(\Yasca\Plugin::$installedPlugins)
        ->selectMany(static function($plugins){return $plugins;})
        ->forAll(static function($plugin){
            print("$plugin\n");
        });
        exit(0);

    } elseif ($switch === '--logInstalled'  ||
              $switch === '--logsInstalled' ||
              $switch === '--installedLogs' ||
              $switch === '--installedLog'
      ){
        foreach(\Yasca\Log::getInstalledLogs() as $log){
            print("$log\n");
        }
        exit(0);

    } elseif ($switch === '--reportInstalled' ||
              $switch === '--reportsInstalled' ||
              $switch === '--installedReports'
    ){
        foreach(\Yasca\Report::getInstalledReports() as $report){
            print("$report\n");
        }
        exit(0);


    } elseif ($switch === '--batch'){
        $scannerOptions['batch'] = $options;
        $addDefaultReport = false;

    } elseif ($switch === '--debug'){
        $scannerOptions['debug'] = true;

    } elseif ($switch === '--silent' ||
              $switch === '--logSilent'
      ){
        $addDefaultLog = false;

    } elseif ($switch === '-l'         ||
              $switch === '--log'
      ){
        $type = '\Yasca\Logs\\' . \array_shift($options);
        $logs->enqueue(new $type($options));
        $addDefaultLog = false;

    } elseif ($switch === '-r' ||
              $switch === '--report'
      ){
        $type = '\Yasca\Reports\\' . \array_shift($options);
        $reports->enqueue(new $type($options));
        $addDefaultReport = false;

    } elseif ($switch === '--logOption' ||
              $switch === '--logOptions'
      ){
          if (Iterators::any($options)){
            $type = '\Yasca\Logs\\' . \array_shift($options);
            print($type::OPTIONS);
          } else {
              foreach(\Yasca\Log::getInstalledLogs() as $log){
                print("$log\n");
                $type = '\Yasca\Logs\\' . $log;
                print("    " . $type::OPTIONS . "\n\n");
            }
          }
        exit(0);

    } elseif ($switch === '--reportOption' ||
              $switch === '--reportOptions'
      ){
          if (Iterators::any($options)){
            $type = '\Yasca\Reports\\' . \array_shift($options);
            print($type::OPTIONS);
          } else {
              foreach(\Yasca\Report::getInstalledReports() as $report){
                  print("$report\n");
                  $type = '\Yasca\Reports\\' . $report;
                print("    " . $type::OPTIONS . "\n\n");
              }
          }
        exit(0);

    } elseif ($switch === '--ignoredPlugin'  ||
              $switch === '--ignoredPlugins' ||
              $switch === '--ignorePlugin'   ||
              $switch === '--ignorePlugins'  ||
              $switch === '--pluginIgnored'  ||
              $switch === '--pluginsIgnored' ||
              $switch === '--pluginIgnore'   ||
              $switch === '--pluginsIgnore'
    ){
        $scannerOptions['pluginsIgnore'] = $options;

    } elseif ($switch === '--onlyPlugins' ||
              $switch === '--onlyPlugin'  ||
              $switch === '--pluginsOnly' ||
              $switch === '--pluginOnly'
    ){
        $scannerOptions['pluginsOnly'] = $options;

    } elseif ($switch === '--onlyExtension' ||
              $switch === '--onlyExtensions'||
              $switch === '--extensionOnly' ||
              $switch === '--extensionsOnly'
    ){
        $scannerOptions['extensionsOnly'] = $options;

    } elseif ($switch === '--ignoreExtension' ||
              $switch === '--ignoreExtensions'||
              $switch === '--extensionIgnore' ||
              $switch === '--extensionsIgnore'
    ){
        $scannerOptions['extensionsIgnore'] = $options;

    } else {
        $scannerOptions['targetDirectory'] = $switch;
    }
}

$debug =
    (new \Yasca\Core\FunctionPipe)
    ->wrap($scannerOptions)
    ->pipe([Iterators::_class,'elementAtOrNull'], 'debug')
    ->pipe([Operators::_class,'equals'], true)
    ->unwrap();

if ($addDefaultLog === true){
    $logs->enqueue(
        new \Yasca\Logs\ConsoleLog([
            \Yasca\Logs\Level::ERROR |
            \Yasca\Logs\Level::INFO  |
            (
             $debug
             ? \Yasca\Logs\Level::DEBUG
             : 0
            )
        ])
    );
}
if ($addDefaultReport === true){
    $reports->enqueue(new \Yasca\Reports\HtmlFileReport(['report.html']));
}

$batch = Iterators::elementAtOrNull($scannerOptions, 'batch');
if ($batch !== null) {
    $logsAdapter = new \Yasca\Core\SplSubjectAdapter();
    Iterators::forAll($logs, [$logsAdapter, 'attach']);
    $batchStart = new \DateTime();

    $iter =
        (new \Yasca\Core\IteratorBuilder)
        ->from($batch)
        ->selectMany(static function($scanDir){
            return (new \Yasca\Core\FunctionPipe)
            ->wrap($scanDir)
            ->pipe([Operators::_class,'_new'], '\DirectoryIterator')
            ->toIteratorBuilder()
            ->selectKeys(static function($dir) use ($scanDir){
                return [$dir, $scanDir];
            });
        })
        ->where(static function($fileinfo){
            return !$fileinfo->isDot() && $fileinfo->isDir();
        })
        ->selectKeys(static function($fileinfo, $scanDir) use ($scannerOptions, $debug, $logsAdapter){
            static $count = 0;
            $count += 1;
            $key = $count;
            $targetDirectory = $fileinfo->getRealpath();
            $scannerOptions['targetDirectory'] = $targetDirectory;
            $reportFileName = "{$scanDir}\\{$fileinfo->getBasename()}.html";
            $launchTime = new \DateTime();
            $logPrefix = '#' . \str_pad('' . $key, 3) . '  ';
            return [
                (new \Yasca\Scanner($scannerOptions))
                ->attachLogObserver(
                    new \Yasca\Core\SplObserverAdapter(
                        static function($value) use ($logsAdapter, $logPrefix){
                            list($message, $severity) = $value;
                            $logsAdapter->raise([$logPrefix . $message, $severity]);
                        }
                    )
                )
                ->attachResultObserver(
                    new \Yasca\Reports\HtmlFileReport([$reportFileName])
                )
                ->executeAsync()
                ->whenDone(static function($async) use ($key, $targetDirectory, $launchTime, $logPrefix, $logsAdapter){
                    if ($async->isErrored() === true){
                        $logsAdapter->raise(["{$logPrefix}Errored for $targetDirectory", \Yasca\Logs\Level::ERROR]);
                    } else {
                        $logsAdapter->raise(["{$logPrefix}Completed for $targetDirectory", \Yasca\Logs\Level::INFO]);
                    }
                    $logsAdapter->raise(["{$logPrefix}Took {$launchTime->diff(new \DateTime())->format('%h:%i:%s')}", \Yasca\Logs\Level::INFO]);
                }),
                "#$key $targetDirectory",
            ];
        })
        ->toFunctionPipe()
        ->pipe([Iterators::_class,'toArray'], true)
        ->toIteratorBuilder()
        ->where(static function($async) {
            return $async->isDone() === false;
        })
        ->toArray(true);

    if (Iterators::any($iter)){
        $logsAdapter->raise(["All scans launched", \Yasca\Logs\Level::INFO]);
        $logsAdapter->raise([Iterators::count($iter) . " scans waiting on external plugins:", \Yasca\Logs\Level::INFO]);
        foreach($iter as $dir => $async){
            $logsAdapter->raise(["$dir", \Yasca\Logs\Level::INFO]);
        }
        foreach($iter as $dir => $async){
            if ($async->isDone() === false){
                $logsAdapter->raise(["Waiting on $dir...", \Yasca\Logs\Level::INFO]);
            }
            $async->result();
        }
    }
    $logsAdapter->raise(["Batch took {$batchStart->diff(new \DateTime())->format('%h:%i:%s')}", \Yasca\Logs\Level::INFO]);
    exit(0);
}

$scanner = new \Yasca\Scanner($scannerOptions);
Iterators::forAll($logs, [$scanner, 'attachLogObserver']);
Iterators::forAll($reports, [$scanner, 'attachResultObserver']);
//Allow everything but the scanner (and things the scanner holds) to drop out of scope
return $scanner;
})->execute();