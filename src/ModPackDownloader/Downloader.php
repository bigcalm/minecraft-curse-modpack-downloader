<?php

namespace ModPackDownloader;

use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Downloader
{
    const CurseProjectBaseUrl = "https://minecraft.curseforge.com";
    const LOG_DISPLAY_LEVEL = Logger::INFO;

    /**
     * @var string
     */
    protected $manifestFile;

    /**
     * @var string
     */
    protected $modsDirectory;

    /**
     * @var array
     */
    protected $manifest = [];

    /**
     * @var array
     */
    protected $errors = [];

    /** @var Logger */
    protected $log;

    public function __construct($manifestFile = null, $modsDirectory = null)
    {
        $this->log = new Logger('downloader');
        $this->log->pushHandler(new StreamHandler('downloader.log', Logger::INFO));

        $this->manifestFile = $manifestFile;
        $this->modsDirectory = $modsDirectory;
    }

    /**
     * @return string
     */
    public function getManifestFile(): string
    {
        return $this->manifestFile;
    }

    /**
     * @param string $manifestFile
     * @return $this
     */
    public function setManifestFile($manifestFile)
    {
        $this->manifestFile = $manifestFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getModsDirectory(): string
    {
        return $this->modsDirectory;
    }

    /**
     * @param string $modsDirectory
     * @return $this
     */
    public function setModsDirectory($modsDirectory)
    {
        $this->modsDirectory = $modsDirectory;

        return $this;
    }

    public function download()
    {
        if (empty($this->manifestFile)) {
            $this->out('Missing manifest file', Logger::CRITICAL);
            throw new \InvalidArgumentException('Missing manifest file');
        }

        if (empty($this->modsDirectory)) {
            $this->out('Missing mods directory', Logger::CRITICAL);
            throw new \InvalidArgumentException('Missing mods directory');
        }

        $this->out('Loading manifest file');

        /** @var array $manifest */
        $this->manifest = json_decode(file_get_contents($this->manifestFile), true);

        $this->out('ModPack: ' . $this->manifest['name']);
        $this->out('Version: ' . $this->manifest['version']);

        $modCount = count($this->manifest['files']);

        $this->out('Mods to download: ' . $modCount);

        $index = 0;

        foreach ($this->manifest['files'] as $file) {
            $index++;

            $counter = '[' . $index . '/' . $modCount . ']';

            $projectUrl = self::CurseProjectBaseUrl . '/projects/' . $file['projectID'];

            $downloadUri = $projectUrl . '/files/' . $file['fileID'] . '/download';

            $this->out($counter . ' Downloading ' . $downloadUri, Logger::DEBUG);

            try {
                $modFilename = $this->stream($downloadUri);

                $this->out($counter . ' ' . $modFilename);
            } catch (\Exception $e) {
                $this->errors[$counter] = $projectUrl;

                $this->out($counter . ' Unable to download', Logger::ERROR);
                $this->out("\tError: " . $e->getMessage(), Logger::ERROR);
            }
        }

        $this->out('');
        $this->out('Finished.');

        if (!empty($this->errors)) {
            $this->out('Error count: ' . count($this->errors));

            $this->out('Unable to download some mods. Please manually fetch the ' . $this->manifest['minecraft']['version'] . ' version from: ');

            $this->out('');
            $this->out($this->errors);
        }
    }

    public function out($var, $level = Logger::INFO)
    {
        if ($level >= self::LOG_DISPLAY_LEVEL) {
            if (is_array($var)) {
                echo var_export($var, true) . "\n";
            } else {
                echo $var . "\n";
            }
        }

        $var = !is_array($var) ? $var : json_encode($var);
        $this->log->log($level, $var);
    }


    /**
     * @param string $uri
     * @return string
     */
    protected function stream($uri)
    {
        $tmpFile  = tempnam(sys_get_temp_dir(), 'modpaddownload_' . uniqid(strftime('%G-%m-%d')));
        $resource = fopen($tmpFile, 'w');
        $stream   = \GuzzleHttp\Psr7\stream_for($resource);

        $client   = new Client();
        $options  = [
            RequestOptions::ALLOW_REDIRECTS => ['track_redirects' => true],
            RequestOptions::SINK            => $stream, // the body of a response
            RequestOptions::CONNECT_TIMEOUT => 10.0,    // request
            RequestOptions::TIMEOUT         => 60.0,    // response
        ];

        $response = $client->request('GET', $uri, $options);

        $stream->close();

        $redirectHistory = $response->getHeader(\GuzzleHttp\RedirectMiddleware::HISTORY_HEADER);

        $finalLocation = array_pop($redirectHistory);

        $locationParts = explode('/', $finalLocation);

        $filename = array_pop($locationParts);

        $decodedFilename = urldecode($filename);

        rename($tmpFile, $this->modsDirectory . '/' . $decodedFilename);

        return $decodedFilename;
    }
}
