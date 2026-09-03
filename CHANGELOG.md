# Changelog

All notable changes to this package are documented here.

## [1.1.0] - Unreleased

### Security

- `CheckRemoteToken` now sends the token to the Identity Provider via the `Authorization: Bearer <token>` header instead of a `token` query parameter, so it no longer ends up in access/proxy logs or intermediary caches.
- Added a request timeout (5s, 3s to connect) on the IdP validation call. A slow or unreachable IdP now fails closed with a 401 instead of hanging the request indefinitely or surfacing an uncaught 500.

### Upgrading

Consuming apps that only *use* the `remote.token` / `sentinel.ability` middleware need no code change — just bump the dependency.

Apps that also *implement* the Identity Provider's validation endpoint (i.e. read `token` on the receiving end) must accept the token from the `Authorization` header before upgrading, or validation will start failing with 401s. See the README's "Upgrading to 1.1.0" section.

## [1.0.2] - previous release

No changelog kept before this version — see git history.
