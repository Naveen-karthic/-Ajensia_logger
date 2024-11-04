<?php
namespace Carifer\Ajensia;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

class AjensiaLogger
{
    protected static $loggers = [];
    private static $methods = ['message', 'data', 'security', 'error'];

    public static function __callStatic($method, $arguments)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $controller = $backtrace[1]['class'] ?? 'N/A';
        $methodFunction = $backtrace[1]['function'] ?? 'N/A';

        if (!in_array($method, self::$methods)) {
            throw new \BadMethodCallException("Method {$method} does not exist");
        }

        // Extract the message and context
        $message = $arguments[0] ?? '';
        $context = $arguments[1] ?? [];

        // Add controller and method to context
        $context['controller'] = $controller;
        $context['method'] = $methodFunction;

        // Log the message using the appropriate category
        return self::logMessageCategory($method, $message, $context);
    }

    public static function configureLogger($category)
    {
        $userId = Auth::user()->id;
    
        if (!isset(self::$loggers[$category])) {
            $date = now()->format('Y-m-d');

            $logDirectory = config('ajensia_logger.log_directory');

            if ($userId) {
                $userLogDirectory = "{$logDirectory}/{$userId}";
                if (!File::exists($userLogDirectory)) {
                    File::makeDirectory($userLogDirectory, 0755, true);
                }
                $path = "{$userLogDirectory}/{$category}_{$date}.log";
            } else {
                $path = "{$logDirectory}/{$category}_{$date}.log";
            }

            $logger = new Logger($category);
            $handler = new StreamHandler($path, Logger::toMonologLevel(self::getLevelByCategory($category)));
            $formatter = new LineFormatter(null, null, false, true);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            self::$loggers[$category] = $logger;
        }

        return self::$loggers[$category];
    }

    public static function logMessageCategory($category, $message, array $context = [])
    {
        $logger = self::configureLogger($category);
        $logger->log(Logger::toMonologLevel(self::getLevelByCategory($category)), $message, $context);
    }

    private static function getLevelByCategory($category)
    {
        switch ($category) {
            case 'message':
                return Level::Info;
            case 'data':
            case 'security':
                return Level::Notice;
            case 'error':
                return Level::Error;
            default:
                return Level::Info;
        }
    }
}
