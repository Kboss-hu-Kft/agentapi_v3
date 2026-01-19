<?php

namespace Kboss\SzamlaAgent;

/**
 * A Számla Agent naplózását végző osztály
 */
class Log {

    /**
     * Alapértelmezett naplófájl elnevezés
     */
    const string LOG_FILENAME = 'szamlaagent';

    /**
     * Naplózási szint: nincs naplózás
     */
    const int LOG_LEVEL_OFF   = 0;

    /**
     * Naplózási szint: hibák
     */
    const int LOG_LEVEL_ERROR = 1;

    /**
     * Naplózási szint: figyelmeztetések
     */
    const int LOG_LEVEL_WARN  = 2;

    /**
     * Naplózási szint: fejlesztői (debug)
     */
    const int LOG_LEVEL_DEBUG = 3;

    /**
     * Elérhető naplózási szintek
     */
    private static array $logLevels = array(
        self::LOG_LEVEL_OFF,
        self::LOG_LEVEL_ERROR,
        self::LOG_LEVEL_WARN,
        self::LOG_LEVEL_DEBUG
    );

    /**
     * Naplózási fájl elnevezés
     *
     * @var string
     */
    private string $logFileName;

    /**
     * @var int
     */
    public int $loglevel {
        get {
            return $this->loglevel;
        }
        set {
            $this->loglevel = $value;
        }
    }

    /**
     * Log constructor.
     *
     * @param int $loglevel
     * @param string $fileName
     */
    public function __construct(int $loglevel, string $fileName = self::LOG_FILENAME)
    {
        $this->loglevel = $loglevel;
        $this->logFileName = $fileName . '_' . date('Y-m-d') . '_' . time() . '.log';
    }

    /**
     * Üzenetek naplózása logfájlba
     * Igény szerint e-mail küldése a megadott címre.
     *
     * @param string $pMessage
     * @param int $type
     * @param string $logPath
     * @param string $logEmail
     * @throws SzamlaAgentException
     */
    public function writeLog(string $pMessage, int $type, string $logPath, string $logEmail): void
    {
        if ($this->loglevel !== self::LOG_LEVEL_OFF) {
            $filename   = SzamlaAgentUtil::getAbsPath($logPath, $this->logFileName);
            $remoteAddr = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
            $logType = SzamlaAgentUtil::isNotBlank(self::getLogTypeStr($type)) ? ' [' . self::getLogTypeStr($type) . '] ' : '';
            $message    = '['.date('Y-m-d H:i:s').'] ['.$remoteAddr.']'. $logType . $pMessage.PHP_EOL;

            if (!file_exists($filename)) {
                $file = fopen($filename, "w");
                if ($file) {
                    fclose($file);
                } else {
                    throw new SzamlaAgentException(SzamlaAgentException::LOG_ERROR);
                }
            }
            error_log($message, 3, $filename);

            if (!empty($logEmail) && $type == self::LOG_LEVEL_ERROR) {
                $headers = "Content-Type: text/html; charset=UTF-8";
                error_log($message, 1, $logEmail, $headers);
            }
        }
    }

    /**
     * Visszaadja a naplózás típusának elnevezését
     *
     * @param int $type
     * @return string
     */
    public static function getLogTypeStr(int $type): string
    {
        return match ($type) {
            self::LOG_LEVEL_ERROR => 'error',
            self::LOG_LEVEL_WARN => 'warn',
            self::LOG_LEVEL_DEBUG => 'debug',
            default => '',
        };
    }

    /**
     * @return string
     */
    public function getLogFileName(): string
    {
        return $this->logFileName;
    }

    /**
     * @param int $logLevel
     *
     * @return bool
     */
    public static function isValidLogLevel(int $logLevel): bool
    {
        return (in_array($logLevel, self::$logLevels));
    }

    /**
     * @param int $logLevel
     *
     * @return bool
     */
    public static function isNotValidLogLevel(int $logLevel): bool
    {
        return !self::isValidLogLevel($logLevel);
    }

}