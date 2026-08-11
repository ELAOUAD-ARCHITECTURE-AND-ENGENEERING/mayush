import { supportState } from '../src/commerce/supportState';
import { systemState } from '../src/commerce/systemState';
import { accountPreferencesState } from '../src/commerce/accountPreferencesState';
import { TicketResolvedRatingScreen } from '../src/screens/support/TicketResolvedRatingScreen';
import { SupportConnectionErrorScreen } from '../src/screens/support/SupportConnectionErrorScreen';
import { SupportTemporarilyUnavailableScreen } from '../src/screens/support/SupportTemporarilyUnavailableScreen';
import { MaintenanceModeServicesImpactedScreen } from '../src/screens/support/MaintenanceModeServicesImpactedScreen';
import { AppUpdateAvailableScreen } from '../src/screens/support/AppUpdateAvailableScreen';
import appConfig from '../app.json';

export function runStep7CTicketResolutionSystemStatesUpdateTests() {
  console.log('--- Section 27: Step 7C — Ticket Resolution, Support/System States & App Update ---');

  let passed = 0;
  let failed = 0;

  function assert(condition: boolean, message: string) {
    if (condition) {
      console.log(`[PASS] ${message}`);
      passed++;
    } else {
      console.error(`[FAIL] ${message}`);
      failed++;
    }
  }

  // 1. Component existence & export tests
  assert(typeof TicketResolvedRatingScreen === 'function', 'TicketResolvedRatingScreen exists (309:820)');
  assert(typeof SupportConnectionErrorScreen === 'function', 'SupportConnectionErrorScreen exists (309:821)');
  assert(typeof SupportTemporarilyUnavailableScreen === 'function', 'SupportTemporarilyUnavailableScreen exists (309:822)');
  assert(typeof MaintenanceModeServicesImpactedScreen === 'function', 'MaintenanceModeServicesImpactedScreen exists (309:823)');
  assert(typeof AppUpdateAvailableScreen === 'function', 'AppUpdateAvailableScreen exists (309:824)');

  // 2. Support State Rating extension tests
  const resolvedTicket = supportState.getSupportRequests().find((r) => r.status === 'resolved');
  assert(!!resolvedTicket, 'supportState includes a resolved ticket fixture for rating (req-3)');

  if (resolvedTicket) {
    const rateSuccess = supportState.rateTicket(resolvedTicket.id, 5, 'Excellent service rapide');
    assert(rateSuccess === true, 'rateTicket attached 5-star rating to resolved ticket');
    const updated = supportState.getSupportRequestById(resolvedTicket.id);
    assert(updated?.rating?.stars === 5, 'supportState stores rating stars correctly');
    assert(updated?.rating?.comment === 'Excellent service rapide', 'supportState stores rating comment correctly');
  }

  // 3. System State tests
  assert(systemState.getSupportAvailabilityStatus() === 'online', 'systemState initializes support availability status');
  const maintenance = systemState.getMaintenanceInfo();
  assert(maintenance.isMaintenanceMode === true, 'systemState provides maintenance mode info');
  assert(maintenance.impactedServices.length === 3, 'systemState lists 3 impacted services');
  assert(maintenance.lastCheckedTimestamp === '28 mai 2026 à 10:24', 'systemState provides dynamic last checked timestamp without invented ETA');

  const appUpdate = systemState.getAppUpdateInfo();
  assert(appUpdate.updateAvailable === true, 'systemState provides app update availability info');
  assert(appUpdate.currentVersion === appConfig.expo.version, 'App update screen uses verified app version from app.json (1.0.0)');
  assert(appUpdate.latestVersion === '1.3.0', 'systemState specifies target update version 1.3.0');
  assert(appUpdate.isMandatory === false, 'Step 7C app update 309:824 is optional (isMandatory is false)');

  // 4. Connection Error vs Temporarily Unavailable distinction
  assert(
    typeof SupportConnectionErrorScreen === 'function' && typeof SupportTemporarilyUnavailableScreen === 'function',
    'Connection Error (309:821) and Temporarily Unavailable (309:822) exist as distinct components'
  );

  // 5. Verification of non-implementation of 309:825
  let forcedUpdateImplemented = false;
  try {
    require('../src/screens/support/ForcedUpdateRequiredScreen');
    forcedUpdateImplemented = true;
  } catch {
    forcedUpdateImplemented = false;
  }
  assert(!forcedUpdateImplemented, 'Node 309:825 (09-forced-update-required-fr) is NOT implemented in Step 7C batch');

  // 6. Preservation of durable state
  const requestsBefore = supportState.getSupportRequests().length;
  supportState.rateTicket('req-3', 4, 'Bon support');
  assert(supportState.getSupportRequests().length === requestsBefore, 'Rating submission preserves existing support tickets array size');

  // 7. Language / RTL preservation
  const currentLang = accountPreferencesState.getSelectedLanguage();
  assert(currentLang === 'fr' || currentLang === 'ar', 'Language preference remains intact across system state transitions');

  console.log(`Step 7C Tests Complete: ${passed} PASSED, ${failed} FAILED\n`);
  return { passed, failed };
}
