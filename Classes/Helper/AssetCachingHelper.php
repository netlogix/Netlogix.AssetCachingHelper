<?php
declare(strict_types=1);

namespace Netlogix\AssetCachingHelper\Helper;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetVariantInterface;
use Neos\Neos\Fusion\Helper\CachingHelper as NeosCachingHelper;

class AssetCachingHelper implements ProtectedContextAwareInterface
{

    /**
     * This prefix is not flushed at all
     * TODO: Replace with ASSET_DYNAMIC_TAG and remove?
     */
    protected const ASSET_TAG = 'Asset_';

    /**
     * Prefix used for dynamically tagging Asset usages
     * @see https://github.com/neos/neos-development-collection/blob/5.3/Neos.Neos/Classes/Fusion/Cache/ContentCacheFlusher.php#L294
     */
    protected const ASSET_DYNAMIC_TAG = 'AssetDynamicTag_';

    /**
     * @var NeosCachingHelper
     */
    protected $neosCachingHelper;

    public function __construct(NeosCachingHelper $neosCachingHelper)
    {
        $this->neosCachingHelper = $neosCachingHelper;
    }

    /**
     * Generate Asset Tags:
     *  - Asset_<identifier>
     *  - AssetDynamicTag_<identifier>
     *  - AssetDynamicTag_<workspace>_<identifier>
     *
     * Currently only the "AssetDynamicTag_<workspace>_<identifier>" is flushed by Neos,
     * but that one is never generated. To circumvent this, we create all possible
     * tags, even the "Asset_<identifier>" which is completely custom at this point.
     *
     * @see https://github.com/neos/neos-development-collection/issues/2905
     *
     * @param AssetInterface|array|null $asset
     * @param NodeInterface|null $node
     * @return string[]
     */
    public function assetTag($asset, ?NodeInterface $node = null): array
    {
        if (is_array($asset)) {
            $tags = [];
            foreach ($asset as $singleAsset) {
                $tags = array_merge($tags, $this->generateTagsForSingleAsset($singleAsset));
            }

            return array_values(array_unique($tags));
        } elseif ($asset instanceof AssetInterface) {
            return $this->generateTagsForSingleAsset($asset, $node);
        } else {
            return [];
        }
    }

    private function generateTagsForSingleAsset(AssetInterface $asset = null, ?NodeInterface $node = null): array
    {
        if ($asset instanceof AssetVariantInterface) {
            $asset = $asset->getOriginalAsset();
        }

        $assetIdentifier = $asset->getIdentifier();

        $tags = [
            static::ASSET_TAG . $assetIdentifier,
            static::ASSET_DYNAMIC_TAG . $assetIdentifier
        ];

        if ($node) {
            $workspaceHash = $this->neosCachingHelper->renderWorkspaceTagForContextNode(
                $node->getWorkspace()->getName()
            );

            $tags[] = static::ASSET_DYNAMIC_TAG . $workspaceHash . '_' . $assetIdentifier;
        }

        return $tags;
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
