<?php

declare(strict_types=1);

namespace ReliqArts\Docweaver\Exceptions\Product;

use ReliqArts\Docweaver\Contracts\Exception;
use ReliqArts\Docweaver\Models\Product;

final class AssetPublicationFailed extends PublicationFailed
{
    private const CODE = 8002;

    /**
     * @var null|string
     */
    protected $assetType;

    /**
     * AssetPublicationFailed constructor.
     *
     * @param Product        $product
     * @param string         $assetType
     * @param null|Exception $previous
     *
     * @return Exception
     */
    public static function forProductAssetsOfType(
        Product $product,
        string $assetType,
        Exception $previous = null
    ): Exception {
        $message = sprintf('Failed to publish %s assets for product `%s`.', $assetType, $product->getName());
        $self = new self($message, self::CODE, $previous);
        $self->product = $product;
        $self->assetType = $assetType;

        return $self;
    }
}
