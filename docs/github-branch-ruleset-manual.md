# GitHub Branch Ruleset Manual Setup

This is the only operational safety item that cannot be fully enforced from repository code. Configure it once in GitHub so the automated workflows can protect `main`.

Path in GitHub:

`Settings` -> `Rules` -> `Rulesets` -> `New branch ruleset`

Use these values:

| Field | Required value |
| --- | --- |
| Ruleset name | `MainProtector` |
| Enforcement status | `Active` |
| Bypass list | Empty unless an emergency-only admin bypass is approved |
| Target branches | Include default branch, or add pattern `main` |
| Restrict deletions | Enabled |
| Require a pull request before merging | Enabled |
| Required approvals | `1` minimum |
| Dismiss stale pull request approvals | Recommended |
| Require approval of the most recent reviewable push | Recommended |
| Require status checks to pass | Enabled |
| Require branches to be up to date before merging | Enabled |
| Block force pushes | Enabled |

Add these required status checks after the workflows have run at least once on a pull request:

- `Mayush Quality Gates / App health`
- `Mayush Quality Gates / Composer audit`
- `Mayush Quality Gates / NPM production audit`
- `Mayush Restoration Guardrails / Static and Laravel guardrails`

Do not leave the ruleset in an incomplete state:

- `Enforcement status` cannot remain `Disabled`.
- Target branches cannot remain empty.
- Required status checks cannot remain empty.
- If GitHub says rulesets are not enforced for this private repository without GitHub Team, either upgrade the organization plan or configure the same requirements under classic `Settings` -> `Branches` -> `Branch protection rules`.
