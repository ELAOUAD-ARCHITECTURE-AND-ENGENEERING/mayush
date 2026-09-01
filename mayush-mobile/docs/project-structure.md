# Mayush Mobile - Project Structure

## Architectural Overview

`mayush-mobile` is the buyer-only mobile client application for the Mayush eCommerce marketplace. It resides inside the existing `mayush` Laravel repository as a dedicated React Native + Expo TypeScript project folder, maintaining absolute separation from Laravel backend source files.

```
mayush/
├── app/                          # Laravel Backend Controllers, Models, Middleware
├── config/                       # Laravel Configuration
├── database/                     # Migrations & Seeders
├── public/                       # Laravel Public Web Assets
├── resources/                    # Laravel Web Views (Blade)
├── routes/                       # Laravel Web & API Routes
└── mayush-mobile/                # Buyer-Only React Native / Expo Application
    ├── assets/                   # App Icons, Splash Screens, Static Images
    ├── design-reference/         # Design & Screenshot Reference Files
    │   └── mayush-mobile-design/ # 441 design screenshots, handoff docs & assets
    ├── docs/                     # MVP Architecture & State Tracking
    │   ├── mvp-state.json        # Machine-readable phase state machine
    │   ├── mvp-progress.md       # Audit & progress log
    │   └── project-structure.md  # Project layout documentation
    ├── src/                      # Mobile Application Source Code
    ├── tests/                    # Mobile Automated Unit & Integration Tests
    ├── app.json                  # Expo Application Manifest
    ├── App.tsx                   # Mobile Application Entry Point
    ├── index.ts                  # Expo Entrypoint Register
    ├── package.json              # Dependencies & Scripts
    └── tsconfig.json             # TypeScript Configuration
```

## Folder Roles & Responsibilities

- `design-reference/mayush-mobile-design/`: Holds all 441 original design screenshots, Figma handoffs, validation docs, and state references. Read-only design reference.
- `docs/`: Holds project metadata, state files (`mvp-state.json`), progress tracking (`mvp-progress.md`), and structural documentation.
- `src/`: Future application code (components, screens, navigation, state, services, hooks, utils).
- `assets/`: Image assets, custom fonts, splash screens, and icons used by the app.
- `tests/`: Automated unit and integration tests.

## Integrity Checklist

- **Laravel Application**: 100% untouched.
- **Design Reference Integrity**: 441 files, 18 directories verified.
- **Mobile Stack**: React Native + Expo TypeScript (`blank-typescript` template).
- **Existing Mobile Code**: None previously existed; clean baseline initialized.
