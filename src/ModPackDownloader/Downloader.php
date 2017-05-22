<?php

namespace ModPackDownloader;

class Downloader
{
    /**
     * @var string
     */
    protected $manifestFile = null;

    /**
     * @var string
     */
    protected $modsDirectory = null;

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
            throw new \InvalidArgumentException('Missing manifest file');
        }

        if (empty($this->modsDirectory)) {
            throw new \InvalidArgumentException('Missing mods directory');
        }
    }
}
