# Contributing to Power Modules

Thanks for your interest! This project values explicit design, strong typing, and respectful collaboration.

How to contribute
- Ask questions and propose ideas in GitHub Discussions (or open a “Design feedback” issue).
- For bugs: provide repro steps, expected vs actual behavior, and environment details.
- For features: describe the use case, proposed API, trade-offs, and minimal example.
- For docs: improvements and examples are very welcome.

Development
- PHP: 8.4+
- Tools: PHPUnit 12, PHPStan level 8, PHP-CS-Fixer
- Quick checks:
  - make test
  - make phpstan
  - make codestyle

Coding guidelines
- Prefer explicit APIs and strong typing over “magic.”
- Keep module boundaries crystal clear (imports/exports).
- Favor small, composable units. Add tests with meaningful names.

Pull requests
- Keep PRs focused and incremental.
- Include tests and docs for new behavior.
- Describe trade-offs and alternatives considered.

Issue labels
- good first issue: small, self-contained tasks.
- help wanted: larger tasks suitable for contributors.

Code of Conduct
- By participating, you agree to abide by the Code of Conduct.