<?php

declare(strict_types=1);

namespace ReliQArts\Docweaver\Services\Documentation;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Psr\SimpleCache\InvalidArgumentException;
use ReliQArts\Docweaver\Contracts\ConfigProvider;
use ReliQArts\Docweaver\Contracts\Documentation\Provider as ProviderContract;
use ReliQArts\Docweaver\Contracts\Filesystem;
use ReliQArts\Docweaver\Contracts\MarkdownParser;
use ReliQArts\Docweaver\Exceptions\BadImplementation;
use ReliQArts\Docweaver\Models\Product;

final class Provider implements ProviderContract
{
    private const CACHE_TIMEOUT_SECONDS = 60 * 5;
    private const PAGE_INDEX = 'index';
    private const FILE_EXTENSION = 'md';

    /**
     * The cache implementation.
     *
     * @var Cache
     */
    private $cache;

    /**
     * The cache key.
     *
     * @var string
     */
    private $cacheKey;

    /**
     * Documentation resource directory.
     *
     * @var string
     */
    private $documentationDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MarkdownParser
     */
    private $markdownParser;

    /**
     * Create a new documentation instance.
     *
     * @param Filesystem     $filesystem
     * @param Cache          $cache
     * @param ConfigProvider $configProvider
     * @param MarkdownParser $markdownParser
     *
     * @throws BadImplementation
     */
    public function __construct(
        Filesystem $filesystem,
        Cache $cache,
        ConfigProvider $configProvider,
        MarkdownParser $markdownParser
    ) {
        $this->filesystem = $filesystem;
        $this->cache = $cache;
        $this->configProvider = $configProvider;
        $this->documentationDirectory = $configProvider->getDocumentationDirectory();
        $this->cacheKey = $this->configProvider->getCacheKey();
        $this->markdownParser = $markdownParser;
        $documentationDirectoryAbsolutePath = base_path($this->documentationDirectory);

        if (!$this->filesystem->isDirectory($documentationDirectoryAbsolutePath)) {
            throw new BadImplementation(
                sprintf(
                    'Documentation resource directory `%s` does not exist.',
                    $this->documentationDirectory
                )
            );
        }
    }

    /**
     * @param Product     $product
     * @param string      $version
     * @param null|string $page
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws FileNotFoundException
     */
    public function getPage(Product $product, string $version, string $page = null): string
    {
        $page = $page ?? self::PAGE_INDEX;
        $cacheKey = sprintf('%s.%s.%s.%s', $this->cacheKey, $product->getKey(), $version, $page);

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $pageContent = $this->getPageContent($product, $version, $page);

        $this->cache->put($cacheKey, $pageContent, self::CACHE_TIMEOUT_SECONDS);

        return $pageContent;
    }

    /**
     * @param Product $product
     * @param string  $version
     * @param string  $content
     *
     * @return string
     */
    public function replaceLinks(Product $product, string $version, string $content): string
    {
        $routePrefix = $this->configProvider->getRoutePrefix();
        $versionPlaceholder = '{{version}}';

        // ensure product name exists in url
        if (!empty($product)) {
            $content = str_replace(
                sprintf('docs/%s', $versionPlaceholder),
                sprintf('%s/%s/%s', $routePrefix, $product->getKey(), $version),
                $content
            );
        }

        return str_replace($versionPlaceholder, $version, $content);
    }

    /**
     * @param Product $product
     * @param string  $version
     * @param string  $page
     *
     * @return bool
     */
    public function sectionExists(Product $product, string $version, string $page): bool
    {
        $filePath = $this->getFilePathForProductPage($product, $version, $page);

        return $this->filesystem->exists($filePath);
    }

    /**
     * @param Product $product
     * @param string  $version
     * @param string  $page
     *
     * @return string
     */
    private function getFilePathForProductPage(Product $product, string $version, string $page): string
    {
        $directory = $product->getDirectory();
        $filename = ($page === self::PAGE_INDEX) ? $this->configProvider->getContentIndexPageName() : $page;

        return sprintf('%s/%s/%s.%s', $directory, $version, $filename, self::FILE_EXTENSION);
    }

    /**
     * @param Product $product
     * @param string  $version
     * @param string  $page
     *
     * @return string
     * @throws FileNotFoundException
     */
    private function getPageContent(Product $product, string $version, string $page): string
    {
        $filePath = $this->getFilePathForProductPage($product, $version, $page);
        $pageContent = '';

        if ($this->filesystem->exists($filePath)) {
            $fileContents = $this->filesystem->get($filePath);
            $pageContent = $this->replaceLinks(
                $product,
                $version,
                $this->markdownParser->parse($fileContents)
            );
        }

        return $pageContent;
    }
}
