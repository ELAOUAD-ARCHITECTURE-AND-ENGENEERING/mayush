# GitHub Branch Protection Manual Setup

This is the only operational safety item that cannot be fully enforced from repository code. Configure the strongest branch protection available on the current GitHub plan so the automated workflows can protect `main`.

## Preferred Setup Without GitHub Team

Rulesets are not enforced for private repositories on the current plan. Use classic branch protection instead if GitHub exposes it:

`Settings` -> `Branches` -> `Branch protection rules` -> `Add branch protection rule`

Use these values:

| Field | Required value |
| --- | --- |
| Branch name pattern | `main` |
| Require a pull request before merging | Enabled |
| Required approvals | `1` minimum |
| Dismiss stale pull request approvals | Enabled |
| Require status checks to pass before merging | Enabled |
| Require branches to be up to date before merging | Enabled |
| Block force pushes | Enabled |
| Restrict deletions | Enabled, if available |

Add these required status checks after the workflows have run at least once on a pull request:

- `Mayush Quality Gates / App health`
- `Mayush Quality Gates / Composer audit`
- `Mayush Quality Gates / NPM production audit`
- `Mayush Restoration Guardrails / Static and Laravel guardrails`

If classic branch protection is also unavailable or limited, the repository cannot fully block direct changes from GitHub UI on this plan. In that case, the mandatory operational fallback is:

```bash
composer guardrails
php artisan app:preflight-restore --require-blog-navigation
```

Run both before merge/deploy, and never deploy when either command fails.

## Ruleset Setup For GitHub Team Or Enterprise

Only use this section after upgrading to a plan where private repository rulesets are enforced.

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

Add the same required status checks listed above.

Do not leave the ruleset in an incomplete state:

- `Enforcement status` cannot remain `Disabled`.
- Target branches cannot remain empty.
- Required status checks cannot remain empty.
- If GitHub says rulesets are not enforced for this private repository without GitHub Team, do not rely on the ruleset. Use classic branch protection if available, or use the mandatory operational fallback commands above.
