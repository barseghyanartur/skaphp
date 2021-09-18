# Release history and notes
[Sequence based identifiers](http://en.wikipedia.org/wiki/Software_versioning#Sequence-based_identifiers)
are used for versioning (schema follows below):

```
major.minor[.revision]
```

- It's always safe to upgrade within the same minor version (for example, from
  0.3 to 0.3.4).
- Minor version changes might be backwards incompatible. Read the
  release notes carefully before upgrading (for example, when upgrading from
  0.3.4 to 0.4).
- All backwards incompatible changes are mentioned in this document.

## 0.1.3

2021-09-19

- Add `SignatureValidationResult` class for when you need to know why 
  signature validation failed.

## 0.1.2

2021-09-18

- Add license and author information to the sources.

## 0.1.1

2021-09-17

- Update keywords in the compose.json.

## 0.1.0

2021-09-17

- Initial public release.
