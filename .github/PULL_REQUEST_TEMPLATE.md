# Summary
<!-- What does this PR change and why? Keep it concise but informative. -->

- What:
- Why:

# Related
<!-- Link issues, discussions, and context -->
- Closes #
- Relates to #
- Discussion #

# Type of change
<!-- Select one or more that apply -->
- [ ] Bug fix (non-breaking change that fixes an issue)
- [ ] Feature (non-breaking change that adds functionality)
- [ ] Documentation
- [ ] Refactor (no functional change, improves code quality)
- [ ] Performance
- [ ] Security
- [ ] CI / Tooling / Chore

# Details
## Implementation notes
<!-- Key design decisions, notable patterns, and rationale -->

## Trade-offs and alternatives considered
<!-- Summarize options you considered and why this approach was chosen -->

# Public API changes
<!-- Describe any new/changed public APIs with short examples. Note BC impact and SemVer. -->

```php
// before:

// after:
```

# Testing
## How was this tested?
- [ ] Unit tests
- [ ] Integration tests
- [ ] Manual verification

Steps to verify locally:
1. …
2. …

## Coverage
<!-- Mention areas covered by tests and any gaps -->

# Docs
- [ ] README updated if needed
- [ ] Added/updated docs (e.g., docs/hello-module.md, docs/lifecycle.md)

# Breaking changes
- [ ] No
- [ ] Yes (describe impact and migration below)

Migration steps:
1. …
2. …

# Performance impact
<!-- Any measurable impact or reasoning for hot paths (allocations/complexity). Include benchmarks if available. -->

# Security considerations
<!-- Input validation, deserialization, secrets handling, dependency changes, etc. -->

# Release notes
<!-- One-liner or short paragraph for CHANGELOG / release notes -->

- …

---

## Checklist
- [ ] SemVer: Public API changes are justified and documented (BC respected or clearly marked).
- [ ] Type-safety: Strict types, precise return types, and helpful phpdoc on public interfaces.
- [ ] Error semantics: Exceptions are specific, messages actionable, and documented.
- [ ] Transport-agnostic: No coupling to HTTP/router/PSR-15 in the core (remain PSR-only: container/simple-cache).
- [ ] Dependency hygiene: No new hard deps unless essential; rationale provided.
- [ ] Performance: No unnecessary work on the critical path (module discovery/build/resolve); notes provided.
- [ ] Tests: Added/updated tests cover new behavior and edge cases.
- [ ] Quality gates pass locally:
  - [ ] `composer validate --strict`
  - [ ] `make codestyle`
  - [ ] `make test`
  - [ ] `make phpstan`
- [ ] Documentation updated where it improves developer understanding.
- [ ] I agree to abide by the project’s [Code of Conduct](./.github/CODE_OF_CONDUCT.md).

<!-- Thank you for contributing to Power Modules! -->