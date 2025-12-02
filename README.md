# OREMIS Sentinel 🛡️

**Sentinel** is the centralized token validation and ability system
for OREMIS applications (CA, PIO, SUP, etc.), backed by the Identity
Provider at `data.oremis.fr`.

## Features

- Remote token validation against `data.oremis.fr`
- Token abilities injected into the request
- Convenience facade `TokenAbility`:
  - `TokenAbility::abilities()`
  - `TokenAbility::can('gdpr:write')`
  - `TokenAbility::require('gdpr:write')`
  - `TokenAbility::userId()`
- Route middlewares:
  - `remote.token` → validates the token remotely + cache
  - `ability` → checks required abilities per route

---
