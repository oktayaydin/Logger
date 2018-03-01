<?php
/**
 * Created by PhpStorm.
 * User: oktay
 * Date: 01/03/18
 * Time: 16:14
 */

namespace Oktay\OktayLogger;

use DateTime;
use RuntimeException;

/**
 * Class Logger
 *
 * @package OktayLogger
 * @author  Oktay AYDIN<oktayaydin6464@gmail.com>
 */
class Logger
{
    /**
     * @var string Log dosyasının yolu
     */
    private $logFilePath = '';
    /**
     * @var resource|null Log dosyasının işlemleri için kullanılacak nesne
     */
    private $fileHandle = null;
    /**
     * @var string Log dosyasının son satırını tutar
     */
    private $lastLine = '';
    /**
     * Logger constructor.
     *
     * @param string $logDirectory Log dosyasının tutulacağı dizin
     */
    public function __construct($logDirectory)
    {
        $logDirectory = rtrim($logDirectory, '\\/');
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0777, true);
        }
        $this->logFilePath = $logDirectory . DIRECTORY_SEPARATOR . "log_" . date("d-m-Y") . ".txt";
        if (file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
            throw new RuntimeException("Log dosyası yazılabilir değil. İzinleri kontrol edin.");
        }
        $this->fileHandle = fopen($this->logFilePath, 'a');
        if (!$this->fileHandle) {
            throw new RuntimeException("Dosya açılamadı. İzinleri kontrol edin.");
        }
    }
    /**
     * Sınıfın LogFilePath değerini geri döndürür.
     *
     * @return string LogFilePath değeri.
     */
    public function getLogFilePath()
    {
        return $this->logFilePath;
    }
    /**
     * Sınıfın LastLine değerini geri döndürür.
     *
     * @return string LastLine değeri.
     */
    public function getLastLine()
    {
        return $this->lastLine;
    }
    /**
     * Log işlemini gerçekleştirir.
     *
     * @param string $level   Log girdisinin seviyesi. Örn: error, warning, info
     * @param string $message Log girdisinin mesajı.
     */
    public function log($level, $message)
    {
        $message = $this->formatMessage($level, $message);
        $this->write($message);
    }
    /**
     * Gelen değerleri log dosyasında tutulacağı formata dönüştürür.
     *
     * @param string $level   Log'un seviyesi. Örn: error, warning, info
     * @param string $message Log mesajı.
     *
     * @return string Formatlanmış log girdisi.
     */
    private function formatMessage($level, $message)
    {
        $level = strtoupper($level);
        return "[{$this->getTimestamp()}] [{$level}] {$message}" . PHP_EOL;
    }
    /**
     * Çalıştırıldığı andaki tarih-saat bilgisinin milisaniye verisi ile birlikte geri gönderir.
     *
     * @return string Tarih-Saat bilgisi.
     */
    private function getTimestamp()
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $originalTime));
        return $date->format('Y-m-d G:i:s.u');
    }
    /**
     * Log giridisini dosyaya yazar
     *
     * @param string $message Log girdisi.
     */
    private function write($message)
    {
        if (!is_null($this->fileHandle)) {
            if (fwrite($this->fileHandle, $message) === false) {
                throw new RuntimeException('Log dosyası yazılabilir durumda değil. İzinleri kontrol edin.');
            } else {
                $this->lastLine = trim($message);
            }
        }
    }
    /**
     * Tüm işlemler bittiğinde log dosyasını kapatır.
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}