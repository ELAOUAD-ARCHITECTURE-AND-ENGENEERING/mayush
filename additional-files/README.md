# 📁 Additional Files

This directory contains all supplementary files created during development that are **not part of the original Mayush project codebase**. These files support the development workflow but should never be confused with core application code.

## Folder Structure

| Folder | Purpose |
|---|---|
| `documentation/` | Guides, notes, architecture diagrams, and explanatory files |
| `utilities/` | Helper scripts, automation tools, and CLI utilities |
| `experiments/` | Test implementations, prototypes, and proof-of-concept code |
| `backups/` | Version snapshots, temporary saves, and rollback references |

## Naming Conventions

- **Folders:** lowercase kebab-case (e.g., `my-subfolder`)
- **Files:** `YYYY-MM-DD_purpose-description.ext` (e.g., `2026-03-28_clamav-integration-notes.md`)

## Rules

1. All supplementary files **must** live inside this `additional-files/` directory
2. Files must be categorized into the appropriate subfolder
3. Each subfolder contains a `README.md` documenting its contents
4. **Never** move, modify, or relocate existing project files into this folder
5. The `index.json` manifest in this directory tracks all contents
