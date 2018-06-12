# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 4.0.0 - 2018-06-12

Initial release highlights include:

- \Psr\Cache is now used for caching
- Min version of PHP is now >= 7.1
- strict_types throughout with scalar type hints and return types
- Much improved test coverage ~ 95%
- Introduced document hydration so you can implement concrete content models in code
- Re-Introduced typed content fragments such as `RichText`, `Date`, `Embed`, `GeoPoint` etc.
- Transparent support for both V1 and V2 Apis
- Default document implementation can retrieve it's own relationships such as alternative translations
- Guaranteed to throw predictable exceptions all implementing the same interface in the same namespace

## Future changes

The `LinkResolver` as a concept is now pretty much a hard dependency for `\Prismic\Api` but relies on setter injection. The good thing is that this enables other elements, such as `RichText` to be serialised to HTML without needing to keep a link resolver handy all the time, i.e. `$textFragment->asHtml()` instead of `$textFragment->asHtml($linkResolver)` which can be annoying in views, but the bad thing is that it's a hidden dependency. At some point this needs to be made more friendly…

The same could be said of the `Hydrator` - The defaults are perfectly acceptable and they are straight-forward to override but it's still a hidden dependency.

In future, I'm considering providing ready to use factories and configuration suitable for `Psr\Container` - it's good to practice dependency injection and everyone's doing it right? 

### `\Prismic\Api` Notable Gotchas

- Methods `getByID`, `getByIDs`, `getByUID` have been camel-cased to `getById`, `getByIds` and `getByUid`
- Method `previewSession` signature has changed. A Link resolver is no longer required: `previewSession( string $token, string $defaultUrl) : string`
- Named constructor `get()` has changed to `Api::get( string $action, string $accessToken, Client $httpClient, \Psr\Cache\CacheItemPoolInterface $cache)
