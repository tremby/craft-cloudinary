<?php
namespace craft\cloudinary;

use Craft;
use craft\flysystem\base\FlysystemFs;
use CarlosOCarvalho\Flysystem\Cloudinary\CloudinaryAdapter;

/**
 * Class Volume
 *
 * @property null|string $settingsHtml
 * @property string      $rootUrl
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class Fs extends FlysystemFs
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Cloudinary';
    }

    // Properties
    // =========================================================================

    /**
     * @var bool Whether this is a local source or not. Defaults to false.
     */
    protected $isSourceLocal = false;

    /**
     * @var string Path to the root of this sources local folder.
     */
    public $subfolder = '';

    /**
     * @var string Cloudinary API key
     */
    public $apiKey = '';

    /**
     * @var string Cloudinary API secret
     */
    public $apiSecret = '';

    /**
     * @var string Cloudinary cloud name to use
     */
    public $cloudName = '';

    /**
     * @var bool Overwrite existing files on Cloudinary
     */
    public $overwrite = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->foldersHaveTrailingSlashes = false;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['cloudName', 'apiKey', 'apiSecret'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     *
     * @return string|null
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('cloudinary/volumeSettings', [
            'volume' => $this
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getRootUrl(): string
    {
        return rtrim(rtrim($this->url, '/').'/'.$this->subfolder, '/').'/';
    }

    /**
     * @inheritdoc
     */
    public function write(string $path, string $contents, array $config = []): void
    {
        error_log("running write on [$path] but we're removing extension first");
        parent::write($this->_removeExtension($path), contents, $config);
    }

    /**
     * @inheritdoc
     */
    public function read(string $path): string
    {
        return parent::read($this->_removeExtension($path));
    }

    /**
     * @inheritdoc
     */
    public function writeFileFromStream(string $path, $stream, array $config = []): void
    {
        error_log("running writeFileFromStream on [$path] but we're removing extension first");
        parent::writeFileFromStream($this->_removeExtension($path), $stream, $config);
    }

    /**
     * @inheritdoc
     */
    public function fileExists(string $path): bool
    {
        return parent::fileExists($this->_removeExtension($path));
    }

    /**
     * @inheritdoc
     */
    public function deleteFile(string $path): void
    {
        parent::deleteFile($this->_removeExtension($path));
    }

    /**
     * @inheritdoc
     */
    public function renameFile(string $path, string $newPath): void
    {
        parent::renameFile($this->_removeExtension($path), $this->_removeExtension($newPath));
    }

    /**
     * @inheritdoc
     */
    public function copyFile(string $path, string $newPath): void
    {
        parent::copyFile($this->_removeExtension($path), $this->_removeExtension($newPath));
    }

    /**
     * @inheritdoc
     */
    public function getFileStream(string $uriPath)
    {
        return parent::getFileStream($this->_removeExtension($path));
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return CloudinaryAdapter
     */
    protected function createAdapter(): CloudinaryAdapter
    {
        return new CloudinaryAdapter([
            'cloud_name' => $this->cloudName,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'overwrite' => $this->overwrite,
        ]);
    }

    /**
     * @inheritdoc
     *
     * @return bool Whether the operation was successful.
     */
    protected function invalidateCdnPath(string $path): bool
    {
        return false; // TODO -- does this need to do anything?
    }

    // Private Methods
    // =========================================================================

    private function _removeExtension(string $path)
    {
        $pathInfo = pathinfo($path);

        return implode('/', [
            $pathInfo['dirname'],
            $pathInfo['filename'],
        ]);
    }
}
