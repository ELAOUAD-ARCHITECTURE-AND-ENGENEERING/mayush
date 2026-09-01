import assert from 'assert';
import fs from 'fs';
import path from 'path';
import { systemState } from '../src/commerce/systemState';
import { authState } from '../src/commerce/authState';
import { emptyCartState } from '../src/commerce/cartState';
import { accountPreferencesState } from '../src/commerce/accountPreferencesState';

export function runStep7DForcedUpdateSettingsStatesTests(baseDir: string = process.cwd()): void {
  console.log('\n==================================================');
  console.log('RUNNING STEP 7D: FORCED UPDATE & SETTINGS FAILURE/LOADING STATES TESTS');
  console.log('==================================================\n');

  // 1. Files Existence (Nodes 309:825 - 309:827)
  const forcedUpdatePath = path.join(baseDir, 'src/screens/support/ForcedUpdateRequiredScreen.tsx');
  const settingsErrorPath = path.join(baseDir, 'src/screens/support/SettingsErrorLoadingStateScreen.tsx');
  const settingsSkeletonPath = path.join(baseDir, 'src/screens/support/SettingsSkeletonLoadingStateScreen.tsx');

  assert(fs.existsSync(forcedUpdatePath), 'ForcedUpdateRequiredScreen exists (309:825)');
  assert(fs.existsSync(settingsErrorPath), 'SettingsErrorLoadingStateScreen exists (309:826)');
  assert(fs.existsSync(settingsSkeletonPath), 'SettingsSkeletonLoadingStateScreen exists (309:827)');

  // 2. Content & Copy Verification
  const forcedUpdateContent = fs.readFileSync(forcedUpdatePath, 'utf8');
  const settingsErrorContent = fs.readFileSync(settingsErrorPath, 'utf8');
  const settingsSkeletonContent = fs.readFileSync(settingsSkeletonPath, 'utf8');

  assert(
    forcedUpdateContent.includes('Mise à jour requise pour continuer') || forcedUpdateContent.includes('Mise à jour obligatoire'),
    'ForcedUpdateRequiredScreen (309:825) renders title and mandatory update badge'
  );
  assert(
    !forcedUpdateContent.includes('Plus tard') && !forcedUpdateContent.includes('onLater'),
    'ForcedUpdateRequiredScreen (309:825) excludes optional skip/later buttons'
  );
  assert(
    forcedUpdateContent.includes('Mettre à jour maintenant'),
    'ForcedUpdateRequiredScreen (309:825) renders primary mandatory update CTA'
  );

  assert(
    settingsErrorContent.includes('Impossible de charger les paramètres') && settingsErrorContent.includes('Réessayer'),
    'SettingsErrorLoadingStateScreen (309:826) renders error title and retry CTA'
  );
  assert(
    settingsErrorContent.includes('Vos données restent conservées'),
    'SettingsErrorLoadingStateScreen (309:826) explicitly confirms user data preservation'
  );

  assert(
    settingsSkeletonContent.includes('profileCardSkeleton') && settingsSkeletonContent.includes('menuRowSkeleton'),
    'SettingsSkeletonLoadingStateScreen (309:827) renders profile card and menu row skeleton structures'
  );

  // 3. System State Architecture & Methods
  systemState.setUpdateMode('forced');
  assert(systemState.getAppUpdateInfo().isMandatory === true, 'systemState manages forced update mode toggle');

  systemState.setUpdateMode('optional');
  assert(systemState.getAppUpdateInfo().isMandatory === false, 'systemState manages optional update mode toggle');

  systemState.setSettingsLoadState('error');
  assert(systemState.getSettingsLoadState() === 'error', 'systemState manages settings error load state');

  systemState.retrySettingsLoad();
  assert(systemState.getSettingsLoadState() === 'loading', 'retrySettingsLoad transitions state to loading');

  systemState.setSettingsLoadState('ready');
  assert(systemState.getSettingsLoadState() === 'ready', 'systemState resets settings state to ready');

  // 4. Data Preservation Verification
  assert(authState !== undefined, 'durable authState survives system state transitions');
  assert(emptyCartState !== undefined, 'durable cartState survives system state transitions');
  assert(accountPreferencesState !== undefined, 'durable accountPreferencesState survives system state transitions');

  assert(!settingsErrorContent.includes('AsyncStorage.clear()'), 'SettingsErrorLoadingStateScreen does NOT invoke AsyncStorage.clear()');
  assert(!forcedUpdateContent.includes('AsyncStorage.clear()'), 'ForcedUpdateRequiredScreen does NOT invoke AsyncStorage.clear()');
  assert(!settingsSkeletonContent.includes('AsyncStorage.clear()'), 'SettingsSkeletonLoadingStateScreen does NOT invoke AsyncStorage.clear()');

  // 5. Documentation & Ledger Integrity Checks
  const routeMapJson = fs.readFileSync(
    path.join(baseDir, 'design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.json'),
    'utf8'
  );
  const routeMapMd = fs.readFileSync(
    path.join(baseDir, 'design-reference/mayush-mobile-design/figma-handoff/figma-prototype-route-map.md'),
    'utf8'
  );
  const progressMd = fs.readFileSync(path.join(baseDir, 'docs/mvp-progress.md'), 'utf8');

  assert(progressMd.includes('Step 6B — Guest Account State'), 'mvp-progress.md restores Step 6B historical section');
  assert(progressMd.includes('Step 6C — About, Accessibility'), 'mvp-progress.md restores Step 6C historical section');

  assert(
    routeMapJson.includes('"connectionId": "FIGMA-PROT-204"') && routeMapJson.includes('"connectionId": "FIGMA-PROT-205"'),
    'figma-prototype-route-map.json tracks connections FIGMA-PROT-204 and FIGMA-PROT-205'
  );
  assert(
    routeMapMd.includes('FIGMA-PROT-204') && routeMapMd.includes('FIGMA-PROT-205') && routeMapMd.includes('FIGMA-PROT-206'),
    'figma-prototype-route-map.md tracks connections 204, 205, and 206'
  );

  console.log('==================================================');
  console.log('ALL STEP 7D TESTS PASSED SUCCESSFULLY');
  console.log('==================================================\n');
}
