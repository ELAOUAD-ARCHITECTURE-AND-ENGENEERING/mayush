+# Phase 5B iOS native-validation checklist

Status: `NATIVE_EVIDENCE_BLOCKED_BY_ENVIRONMENT`

This validation host is Windows. No iOS Simulator run can be claimed here. Use a macOS machine with Xcode Simulator or an approved physical iOS-device build workflow.

Before marking iOS evidence complete, record the device/simulator model, iOS version, Expo build/runtime version, date, and evidence paths for each item below.

- [ ] Application startup: splash, live loader, and language selection.
- [ ] Safe areas: header, footer, home, cart, checkout, and Order Details.
- [ ] Five-tab navigation and selected-tab preservation.
- [ ] Product gallery and variant bottom sheet: presentation and dismissal.
- [ ] Cart persistence, quantities, totals, and MAD formatting.
- [ ] Address form: keyboard avoidance, focus order, +212 validation, and saved-address return.
- [ ] Authentication gate: guest/authenticated continuation and return destination.
- [ ] Payment: processing lock, cancellation, failure recovery, and success routing.
- [ ] Orders List refresh and Order Details back navigation.
- [ ] French LTR and Arabic RTL text, font rendering, and icon alignment.

## Evidence template

| Check | Device / iOS | Result | Screenshot / video | Notes |
|---|---|---|---|---|
| Startup / entry |  |  |  |  |
| Checkout geometry |  |  |  |  |
| Keyboard form |  |  |  |  |
| Payment flow |  |  |  |  |
| Orders / back navigation |  |  |  |  |
| RTL |  |  |  |  |

