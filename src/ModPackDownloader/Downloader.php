<?php

namespace ModPackDownloader;

use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Downloader
{
    const CurseProjectBaseUrl = "https://minecraft.curseforge.com";

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
            $this->out('Missing manifest file', 'critical');
            throw new \InvalidArgumentException('Missing manifest file');
        }

        if (empty($this->modsDirectory)) {
            $this->out('Missing mods directory', 'critical');
            throw new \InvalidArgumentException('Missing mods directory');
        }

        $this->out('Loading manifest file');

        /** @var array $manifest */
        $this->manifest = json_decode(file_get_contents($this->manifestFile), true);

        $this->out('ModPack: ' . $this->manifest['name']);
        $this->out('Version: ' . $this->manifest['version']);


        foreach ($this->manifest['files'] as $file) {
            $projectUrl = self::CurseProjectBaseUrl . '/projects/' . $file['projectID'];
            $downloadUri = $projectUrl . '/files/' . $file['fileID'] . '/download';

            $this->out('Downloading ' . $downloadUri);

            try {
                $modFilename = $this->stream($downloadUri);

                $this->out('Saved to: ' . $modFilename);
            } catch (\Exception $e) {
                $this->out('Unable to download mod. Please manually fetch ' . $downloadUri, 'error');
                $this->out('Error: ' . $e->getMessage());
            }
        }
    }

    public function out($string, $level = 'info', $logThisMessage = true)
    {
        echo $string . "\n";

        if ($logThisMessage) {
            $this->log->log($level, $string);
        }
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
