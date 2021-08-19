# Netlogix.AssetCachingHelper

This package provides a Fusion Helper that can be used to create an AssetTag.
For every given asset, the following two types of tags will be generated:
* `Asset_<Asset Identifier>` This tag is currently not flushed by Neos at all
* `AssetDynamicTag_<Asset Identifier>` This tag is flushed whenever an Asset is changed

## Usage

To create an asset tag, simply call the helper in your @cache block:
```
    @cache {
        mode = 'cached'

        entryIdentifier {
            node = ${node}
        }

        entryTags {
            node = ${Neos.Caching.nodeTag(node)}
            image = ${Netlogix.AssetCaching.assetTag(q(node).property('image'), node)}
        }
    }
```
The Helper takes either a single Asset or an array of Assets. Any null values will be ignored.
